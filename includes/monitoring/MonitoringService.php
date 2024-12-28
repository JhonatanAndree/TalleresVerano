<?php
/**
 * Servicio de Monitoreo
 * Ruta: includes/monitoring/MonitoringService.php
 */

class MonitoringService {
    private $logger;
    private $config;
    private $mailer;
    private static $instance = null;

    private function __construct() {
        $this->logger = ActivityLogger::getInstance();
        $this->config = require __DIR__ . '/../../Config/monitoring.php';
        $this->mailer = EmailService::getInstance();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function checkSystem() {
        $checks = [
            'database' => $this->checkDatabase(),
            'storage' => $this->checkStorage(),
            'services' => $this->checkExternalServices(),
            'performance' => $this->checkPerformance(),
            'security' => $this->checkSecurity()
        ];

        $this->logResults($checks);
        
        if ($this->hasErrors($checks)) {
            $this->notifyAdmins($checks);
        }

        return $checks;
    }

    private function checkDatabase() {
        try {
            $db = Database::getInstance()->getConnection();
            
            return [
                'status' => 'ok',
                'metrics' => [
                    'connections' => $this->getDatabaseMetrics($db),
                    'slow_queries' => $this->getSlowQueries($db),
                    'deadlocks' => $this->getDeadlocks($db)
                ]
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }
    }

    private function checkStorage() {
        $paths = [
            'uploads' => storage_path('uploads'),
            'backups' => storage_path('backups'),
            'logs' => storage_path('logs')
        ];

        $results = [];
        foreach ($paths as $key => $path) {
            $disk = disk_free_space($path);
            $total = disk_total_space($path);
            $used = $total - $disk;
            $percentage = ($used / $total) * 100;

            $results[$key] = [
                'status' => $percentage > 90 ? 'warning' : 'ok',
                'free_space' => $disk,
                'used_space' => $used,
                'total_space' => $total,
                'percentage_used' => $percentage
            ];
        }

        return $results;
    }

    private function checkExternalServices() {
        $services = [
            'yape' => $this->checkYapeAPI(),
            'whatsapp' => $this->checkWhatsAppAPI(),
            'email' => $this->checkEmailService()
        ];

        return $services;
    }

    private function checkPerformance() {
        return [
            'response_time' => $this->measureResponseTime(),
            'memory_usage' => memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true),
            'cpu_load' => sys_getloadavg()
        ];
    }

    private function checkSecurity() {
        return [
            'ssl_cert' => $this->checkSSLCertificate(),
            'file_permissions' => $this->checkFilePermissions(),
            'failed_logins' => $this->getFailedLogins()
        ];
    }

    private function logResults($checks) {
        foreach ($checks as $component => $status) {
            if ($status['status'] === 'error') {
                $this->logger->error("Monitor error in $component", $status);
            } elseif ($status['status'] === 'warning') {
                $this->logger->warning("Monitor warning in $component", $status);
            }
        }
    }

    private function hasErrors($checks) {
        foreach ($checks as $check) {
            if ($check['status'] === 'error') {
                return true;
            }
        }
        return false;
    }

    private function notifyAdmins($checks) {
        $message = $this->formatErrorMessage($checks);
        $admins = $this->getAdminEmails();

        foreach ($admins as $email) {
            $this->mailer->send(
                $email,
                'Alerta de Monitoreo del Sistema',
                'monitoring_alert',
                ['checks' => $checks]
            );
        }
    }

    private function formatErrorMessage($checks) {
        $message = "Se han detectado los siguientes problemas:\n\n";
        foreach ($checks as $component => $check) {
            if ($check['status'] === 'error') {
                $message .= "- $component: {$check['message']}\n";
            }
        }
        return $message;
    }

    private function getAdminEmails() {
        return explode(',', $this->config['admin_emails']);
    }
}