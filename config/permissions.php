<?php
/**
 * Configuración de permisos por rol
 * Ruta: Config/permissions.php
 */

return [
    'SuperAdmin' => [
        'all' => ['create', 'read', 'update', 'delete'],
    ],
    
    'Administrador' => [
        'sedes' => ['create', 'read', 'update'],
        'aulas' => ['create', 'read', 'update'],
        'docentes' => ['create', 'read', 'update'],
        'turnos' => ['create', 'read', 'update'],
        'horarios' => ['create', 'read', 'update'],
        'talleres' => ['create', 'read', 'update'],
        'registradores' => ['read', 'update'],
        'padres' => ['read'],
        'estudiantes' => ['read', 'update'],
        'admision' => ['create', 'read', 'update'],
        'recursos_humanos' => ['read', 'update'],
        'pagos' => ['read'],
        'reportes' => ['read'],
        'configuracion' => ['read']
    ],
    
    'Docente' => [
        'dashboard' => ['read'],
        'perfil' => ['read', 'update'],
        'estudiantes' => ['read'],
        'notas' => ['create', 'read', 'update']
    ],
    
    'Registrador' => [
        'admision' => ['create', 'read'],
        'perfil' => ['read', 'update']
    ],
    
    'Padre' => [
        'estudiantes' => ['read'],
        'pagos' => ['read'],
        'notas' => ['read']
    ]
];