<?php
/**
 * Modelo de Pagos a Docentes
 * Ruta: Models/PagoDocenteModel.php
 */

class PagoDocenteModel implements CrudInterface {
    use CrudTrait;

    protected $table = 'pagos_docentes';
    protected $fillable = [
        'docente_id',
        'monto',
        'horas_trabajadas',
        'costo_hora',
        'mes',
        'ano',
        'estado',
        'observaciones'
    ];

    public function __construct(\PDO $db) {
        $this->db = $db;
    }

    public function calcularPagoMensual($docenteId, $mes, $ano) {
        // Obtener todas las horas trabajadas
        $sql = "SELECT 
                    COUNT(*) * 2 as total_horas,
                    (SELECT costo_hora FROM configuracion_pagos WHERE tipo = 'docente' AND deleted_at IS NULL LIMIT 1) as costo_hora
                FROM horarios h
                JOIN talleres t ON h.taller_id = t.id
                WHERE t.docente_id = ?
                AND MONTH(h.fecha) = ?
                AND YEAR(h.fecha) = ?
                AND h.deleted_at IS NULL";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$docenteId, $mes, $ano]);
        $resultado = $stmt->fetch(\PDO::FETCH_ASSOC);

        return [
            'horas_trabajadas' => $resultado['total_horas'],
            'costo_hora' => $resultado['costo_hora'],
            'monto_total' => $resultado['total_horas'] * $resultado['costo_hora']
        ];
    }

    public function procesarPagosMensuales($mes, $ano) {
        try {
            $this->db->beginTransaction();

            // Obtener todos los docentes activos
            $sql = "SELECT id FROM usuarios WHERE rol = 'Docente' AND deleted_at IS NULL";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $docentes = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($docentes as $docente) {
                $calculo = $this->calcularPagoMensual($docente['id'], $mes, $ano);
                
                if ($calculo['horas_trabajadas'] > 0) {
                    $this->create([
                        'docente_id' => $docente['id'],
                        'monto' => $calculo['monto_total'],
                        'horas_trabajadas' => $calculo['horas_trabajadas'],
                        'costo_hora' => $calculo['costo_hora'],
                        'mes' => $mes,
                        'ano' => $ano,
                        'estado' => 'pendiente'
                    ]);
                }
            }

            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function getResumenPagos($mes, $ano) {
        $sql = "SELECT 
                    pd.*,
                    u.nombre as docente_nombre,
                    u.apellido as docente_apellido,
                    u.dni as docente_dni
                FROM pagos_docentes pd
                JOIN usuarios u ON pd.docente_id = u.id
                WHERE pd.mes = ? 
                AND pd.ano = ?
                AND pd.deleted_at IS NULL";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$mes, $ano]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function aprobarPago($id) {
        return $this->update($id, ['estado' => 'aprobado']);
    }

    public function rechazarPago($id, $observaciones) {
        return $this->update($id, [
            'estado' => 'rechazado',
            'observaciones' => $observaciones
        ]);
    }
}