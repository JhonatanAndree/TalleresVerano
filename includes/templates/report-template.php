<?php
/**
 * Plantilla base para reportes
 * Ruta: includes/templates/report-template.php
 */
class ReportTemplate {
    protected $data;
    protected $config;

    public function __construct($data, $config = []) {
        $this->data = $data;
        $this->config = array_merge([
            'orientation' => 'portrait',
            'title' => 'Reporte',
            'subtitle' => '',
            'footer' => true,
            'logo' => true
        ], $config);
    }

    public function render() {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>' . $this->config['title'] . '</title>
            <style>
                ' . $this->getStyles() . '
            </style>
        </head>
        <body>
            ' . $this->getHeader() . '
            ' . $this->getContent() . '
            ' . ($this->config['footer'] ? $this->getFooter() : '') . '
        </body>
        </html>';
    }

    protected function getStyles() {
        return '
            body { 
                font-family: Arial, sans-serif; 
                line-height: 1.6; 
                padding: 20px; 
            }
            .header { 
                text-align: center; 
                margin-bottom: 30px; 
            }
            .logo { 
                max-width: 150px; 
                margin-bottom: 15px; 
            }
            .title { 
                font-size: 24px; 
                font-weight: bold; 
                margin-bottom: 10px; 
            }
            .subtitle { 
                font-size: 16px; 
                color: #666; 
                margin-bottom: 20px; 
            }
            .content { 
                margin-bottom: 40px; 
            }
            table { 
                width: 100%; 
                border-collapse: collapse; 
                margin-bottom: 20px; 
            }
            th, td { 
                padding: 8px; 
                border: 1px solid #ddd; 
                text-align: left; 
            }
            th { 
                background-color: #f5f5f5; 
            }
            .footer { 
                position: fixed; 
                bottom: 20px; 
                width: 100%; 
                text-align: center; 
                font-size: 12px; 
                color: #666; 
            }
            .page-number:before { 
                content: counter(page); 
            }';
    }

    protected function getHeader() {
        $header = '<div class="header">';
        if ($this->config['logo']) {
            $header .= '<img src="/public/img/logo.png" class="logo" alt="Logo">';
        }
        $header .= '<div class="title">' . $this->config['title'] . '</div>';
        if ($this->config['subtitle']) {
            $header .= '<div class="subtitle">' . $this->config['subtitle'] . '</div>';
        }
        $header .= '</div>';
        return $header;
    }

    protected function getContent() {
        return '<div class="content">
            <!-- El contenido específico del reporte se define en las clases hijas -->
        </div>';
    }

    protected function getFooter() {
        return '<div class="footer">
            <div>Generado el ' . date('d/m/Y H:i:s') . '</div>
            <div>Página <span class="page-number"></span></div>
        </div>';
    }
}