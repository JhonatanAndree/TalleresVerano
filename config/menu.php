<?php
return [
    'SuperAdmin' => [
        [
            'text' => 'Dashboard',
            'url' => '/admin/dashboard',
            'icon' => 'fas fa-tachometer-alt'
        ],
        [
            'text' => 'Control Académico',
            'icon' => 'fas fa-graduation-cap',
            'submenu' => [
                ['text' => 'Sedes', 'url' => '/admin/sedes'],
                ['text' => 'Aulas', 'url' => '/admin/aulas'],
                ['text' => 'Docentes', 'url' => '/admin/docentes'],
                ['text' => 'Turnos', 'url' => '/admin/turnos'],
                ['text' => 'Horarios', 'url' => '/admin/horarios'],
                ['text' => 'Talleres', 'url' => '/admin/talleres']
            ]
        ],
        [
            'text' => 'Control de Usuarios',
            'icon' => 'fas fa-users',
            'submenu' => [
                ['text' => 'Registradores', 'url' => '/admin/registradores'],
                ['text' => 'Padres', 'url' => '/admin/padres'],
                ['text' => 'Estudiantes', 'url' => '/admin/estudiantes'],
                ['text' => 'Personal de Apoyo', 'url' => '/admin/personal']
            ]
        ],
        [
            'text' => 'Admisión',
            'url' => '/admin/admision',
            'icon' => 'fas fa-user-plus'
        ],
        [
            'text' => 'Contabilidad',
            'icon' => 'fas fa-calculator',
            'submenu' => [
                ['text' => 'Pagos', 'url' => '/admin/pagos'],
                ['text' => 'Costos', 'url' => '/admin/costos'],
                ['text' => 'Pago Docentes', 'url' => '/admin/pago-docentes'],
                ['text' => 'Pago Personal', 'url' => '/admin/pago-personal']
            ]
        ],
        [
            'text' => 'Configuración',
            'icon' => 'fas fa-cog',
            'submenu' => [
                ['text' => 'Sistema', 'url' => '/admin/configuracion'],
                ['text' => 'Año Fiscal', 'url' => '/admin/ano-fiscal'],
                ['text' => 'Backups', 'url' => '/admin/backups'],
                ['text' => 'Historial', 'url' => '/admin/historial']
            ]
        ]
    ],
    'Administrador' => [
        [
            'text' => 'Dashboard',
            'url' => '/admin/dashboard',
            'icon' => 'fas fa-tachometer-alt'
        ],
        [
            'text' => 'Control Académico',
            'icon' => 'fas fa-graduation-cap',
            'submenu' => [
                ['text' => 'Sedes', 'url' => '/admin/sedes'],
                ['text' => 'Aulas', 'url' => '/admin/aulas'],
                ['text' => 'Docentes', 'url' => '/admin/docentes'],
                ['text' => 'Horarios', 'url' => '/admin/horarios'],
                ['text' => 'Talleres', 'url' => '/admin/talleres']
            ]
        ],
        [
            'text' => 'Admisión',
            'url' => '/admin/admision',
            'icon' => 'fas fa-user-plus'
        ]
    ],
    'Docente' => [
        [
            'text' => 'Dashboard',
            'url' => '/docente/dashboard',
            'icon' => 'fas fa-tachometer-alt'
        ],
        [
            'text' => 'Mis Talleres',
            'url' => '/docente/talleres',
            'icon' => 'fas fa-chalkboard-teacher'
        ]
    ],
    'Registrador' => [
        [
            'text' => 'Dashboard',
            'url' => '/registrador/dashboard',
            'icon' => 'fas fa-tachometer-alt'
        ],
        [
            'text' => 'Admisión',
            'url' => '/registrador/admision',
            'icon' => 'fas fa-user-plus'
        ]
    ]
];