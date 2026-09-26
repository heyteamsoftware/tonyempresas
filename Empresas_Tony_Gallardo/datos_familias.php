<?php
declare(strict_types=1);

/**
 * Particularidades de cada familia profesional para los cuestionarios
 * imprimibles (alumnado y empresa colaboradora): ciclos, competencias
 * técnicas, tipos de empresa, instalaciones y actividades formativas.
 *
 * Clave = nombre exacto de la familia tal como está en la tabla `familias`.
 * Si una familia no tiene entrada aquí, los cuestionarios usan textos
 * genéricos (ver funcion datos_familia()).
 */
const DATOS_FAMILIAS = [

    'Servicios Socioculturales y a la Comunidad' => [
        'acento' => '#7c3aed',
        'ciclos' => [
            'CFGM Atención a Personas en Situación de Dependencia',
            'CFGS Educación Infantil',
            'CFGS Integración Social',
            'CFGS Animación Sociocultural y Turística',
        ],
        'competencias' => [
            'Atención directa a personas',
            'Elaboración de programas socioeducativos',
            'Manejo de ayudas técnicas',
            'Dinamización de grupos',
            'Registro y documentación de intervenciones',
        ],
        'areas_interes' => [
            'Atención a la infancia', 'Atención a mayores / dependencia',
            'Integración social', 'Animación sociocultural',
        ],
        'tipos_empresa' => [
            'Centro de día', 'Residencia', 'Escuela infantil',
            'Asociación / ONG', 'Ayuntamiento o entidad pública',
        ],
        'instalaciones' => [
            'Sala de estimulación', 'Aula / ludoteca',
            'Sala de usos múltiples', 'Vehículo adaptado',
        ],
        'actividades' => [
            'Apoyo en actividades de la vida diaria',
            'Programación de actividades educativas',
            'Acompañamiento social', 'Animación de grupos',
        ],
    ],

    'Seguridad y Medio Ambiente' => [
        'acento' => '#15803d',
        'ciclos' => [
            'CFGM Emergencias y Protección Civil',
            'CFGS Educación y Control Ambiental',
            'CFGS Coordinación de Emergencias y Protección Civil',
        ],
        'competencias' => [
            'Actuación en emergencias', 'Control y gestión ambiental',
            'Manejo de equipos de protección',
            'Elaboración de planes de emergencia',
            'Toma de muestras y control de residuos',
        ],
        'areas_interes' => [
            'Emergencias y rescate', 'Gestión ambiental',
            'Protección civil', 'Control de residuos',
        ],
        'tipos_empresa' => [
            'Servicio de emergencias', 'Consultora ambiental',
            'Planta de gestión de residuos', 'Bomberos / Protección Civil',
            'Empresa de seguridad',
        ],
        'instalaciones' => [
            'Base operativa', 'Laboratorio ambiental',
            'Almacén de EPIs', 'Vehículos de intervención',
        ],
        'actividades' => [
            'Simulacros y dispositivos de emergencia',
            'Muestreo y análisis ambiental',
            'Elaboración de informes', 'Vigilancia y control',
        ],
    ],

    'Madera, Mueble y Corcho' => [
        'acento' => '#b45309',
        'ciclos' => [
            'CFGB Carpintería y Mueble',
            'CFGM Carpintería y Mueble',
            'CFGM Instalación y Amueblamiento',
            'CFGS Diseño y Amueblamiento',
        ],
        'competencias' => [
            'Interpretación de planos',
            'Manejo de herramientas manuales',
            'Uso de maquinaria fija y portátil',
            'Montaje de mobiliario / Instalación',
            'Diseño asistido (CAD) / CNC',
        ],
        'areas_interes' => [
            'Carpintería general', 'Fabricación de mueble',
            'Oficina técnica', 'Instalación en obra', 'Barnizado y acabados',
        ],
        'tipos_empresa' => [
            'Carpintería general', 'Fabricación de mobiliario',
            'Oficina técnica / diseño', 'CNC / mecanizado',
        ],
        'instalaciones' => [
            'Taller', 'Maquinaria CNC', 'Cabina de acabados', 'Oficina CAD/CAM',
        ],
        'actividades' => [
            'Mecanizado', 'Montaje de muebles', 'Instalación en obra',
            'Programación CNC', 'Acabados',
        ],
    ],

    'Actividades Físicas y Deportivas' => [
        'acento' => '#0369a1',
        'ciclos' => [
            'CFGM Conducción de Actividades Físico-deportivas en el Medio Natural',
            'CFGS Acondicionamiento Físico',
            'CFGS Enseñanza y Animación Sociodeportiva',
        ],
        'competencias' => [
            'Diseño de sesiones deportivas',
            'Conducción de grupos en el medio natural',
            'Acondicionamiento físico', 'Primeros auxilios',
            'Animación sociodeportiva',
        ],
        'areas_interes' => [
            'Actividades en el medio natural', 'Sala de fitness',
            'Animación deportiva', 'Escuelas deportivas',
        ],
        'tipos_empresa' => [
            'Gimnasio / centro deportivo', 'Club deportivo',
            'Empresa de turismo activo', 'Ayuntamiento (deportes)',
        ],
        'instalaciones' => [
            'Sala de musculación / fitness', 'Piscina',
            'Espacios naturales', 'Material de actividades en el medio natural',
        ],
        'actividades' => [
            'Impartición de sesiones',
            'Acompañamiento en rutas / actividades',
            'Programas de acondicionamiento físico',
            'Organización de eventos deportivos',
        ],
    ],

    'Instalación y Mantenimiento' => [
        'acento' => '#475569',
        'ciclos' => [
            'CFGM Instalaciones Frigoríficas y de Climatización',
            'CFGM Instalaciones de Producción de Calor',
            'CFGS Mantenimiento de Instalaciones Térmicas y de Fluidos',
        ],
        'competencias' => [
            'Montaje de instalaciones térmicas', 'Diagnóstico de averías',
            'Manejo de gases refrigerantes', 'Lectura de esquemas técnicos',
            'Mantenimiento preventivo',
        ],
        'areas_interes' => [
            'Climatización', 'Refrigeración industrial',
            'Calefacción y ACS', 'Mantenimiento industrial',
        ],
        'tipos_empresa' => [
            'Instaladora de climatización', 'Empresa de mantenimiento industrial',
            'Servicio técnico (SAT)', 'Frigorista',
        ],
        'instalaciones' => [
            'Taller de instalaciones', 'Banco de pruebas frigoríficas',
            'Almacén de materiales', 'Vehículo taller',
        ],
        'actividades' => [
            'Montaje de equipos', 'Puesta en marcha',
            'Mantenimiento preventivo y correctivo',
            'Manejo de gases refrigerantes (certificado)',
        ],
    ],

    'Sanidad' => [
        'acento' => '#dc2626',
        'ciclos' => [
            'CFGM Cuidados Auxiliares de Enfermería',
            'CFGM Farmacia y Parafarmacia',
            'CFGS Higiene Bucodental',
            'CFGS Documentación Sanitaria',
        ],
        'competencias' => [
            'Atención al paciente', 'Técnicas de higiene bucodental',
            'Gestión de documentación clínica', 'Dispensación en farmacia',
            'Esterilización de material',
        ],
        'areas_interes' => [
            'Atención sociosanitaria', 'Farmacia',
            'Higiene bucodental', 'Documentación sanitaria',
        ],
        'tipos_empresa' => [
            'Clínica dental', 'Farmacia / parafarmacia',
            'Centro de salud / hospital', 'Residencia sociosanitaria',
        ],
        'instalaciones' => [
            'Gabinete clínico', 'Zona de esterilización',
            'Mostrador de farmacia', 'Archivo de documentación clínica',
        ],
        'actividades' => [
            'Apoyo en consulta / gabinete', 'Atención al público',
            'Gestión de historiales', 'Control de existencias',
        ],
    ],

    'Industrias Alimentarias' => [
        'acento' => '#ca8a04',
        'ciclos' => [
            'CFGM Panadería, Repostería y Confitería',
            'CFGM Aceites de Oliva y Vinos',
            'CFGS Procesos y Calidad en la Industria Alimentaria',
        ],
        'competencias' => [
            'Elaboración de productos alimentarios', 'Control de calidad',
            'Manejo de maquinaria de producción', 'Aplicación de APPCC',
            'Envasado y etiquetado',
        ],
        'areas_interes' => [
            'Panadería y repostería', 'Elaboración de bebidas',
            'Control de calidad', 'Producción industrial',
        ],
        'tipos_empresa' => [
            'Obrador / panadería', 'Bodega / almazara',
            'Industria alimentaria', 'Laboratorio de calidad',
        ],
        'instalaciones' => [
            'Obrador', 'Línea de producción',
            'Laboratorio de control de calidad', 'Cámaras frigoríficas',
        ],
        'actividades' => [
            'Elaboración de productos', 'Control de procesos',
            'Envasado y etiquetado', 'Análisis de calidad',
        ],
    ],

    'Comercio y Marketing' => [
        'acento' => '#0891b2',
        'ciclos' => [
            'CFGM Actividades Comerciales',
            'CFGS Gestión de Ventas y Espacios Comerciales',
            'CFGS Marketing y Publicidad',
            'CFGS Comercio Internacional',
        ],
        'competencias' => [
            'Atención al cliente', 'Gestión de ventas',
            'Marketing digital', 'Gestión de stocks',
            'Escaparatismo / visual merchandising',
        ],
        'areas_interes' => [
            'Ventas y atención al cliente', 'Marketing y publicidad',
            'Comercio internacional', 'Gestión de espacios comerciales',
        ],
        'tipos_empresa' => [
            'Comercio minorista', 'Agencia de marketing',
            'Empresa de importación / exportación', 'Gran superficie',
        ],
        'instalaciones' => [
            'Punto de venta', 'Almacén',
            'Sala de marketing / diseño', 'Plataforma de e-commerce',
        ],
        'actividades' => [
            'Atención al cliente', 'Gestión de pedidos y stock',
            'Campañas de marketing', 'Escaparatismo',
        ],
    ],

    'Hostelería y Turismo' => [
        'acento' => '#ea580c',
        'ciclos' => [
            'CFGM Cocina y Gastronomía',
            'CFGM Servicios en Restauración',
            'CFGS Dirección de Cocina',
            'CFGS Dirección de Servicios de Restauración',
            'CFGS Guía, Información y Asistencias Turísticas',
        ],
        'competencias' => [
            'Técnicas culinarias', 'Servicio en sala',
            'Gestión de reservas', 'Atención al cliente turístico',
            'Organización de eventos',
        ],
        'areas_interes' => [
            'Cocina', 'Sala y bar', 'Recepción / atención turística',
            'Organización de eventos',
        ],
        'tipos_empresa' => [
            'Restaurante', 'Hotel / alojamiento',
            'Agencia de viajes', 'Catering / eventos',
        ],
        'instalaciones' => [
            'Cocina', 'Sala / comedor', 'Recepción', 'Bar / cafetería',
        ],
        'actividades' => [
            'Elaboración de platos', 'Servicio en sala',
            'Atención en recepción', 'Organización de eventos',
        ],
    ],
];

/** Datos de la familia, o una plantilla genérica si no está en el catálogo. */
function datos_familia(string $nombre): array
{
    return DATOS_FAMILIAS[$nombre] ?? [
        'acento'        => '#14395e',
        'ciclos'        => [],
        'competencias'  => ['Competencia técnica 1', 'Competencia técnica 2', 'Competencia técnica 3'],
        'areas_interes' => [],
        'tipos_empresa' => [],
        'instalaciones' => [],
        'actividades'   => [],
    ];
}
