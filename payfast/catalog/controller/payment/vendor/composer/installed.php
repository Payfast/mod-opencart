<?php
return array(
    'root'     => array(
        'name'           => '__root__',
        'pretty_version' => 'dev-develop',
        'version'        => 'dev-develop',
        'reference'      => 'f77f8ca579b1dd65759564c3312a8e49df38197d',
        'type'           => 'library',
        'install_path'   => __DIR__ . '/../../',
        'aliases'        => array(),
        'dev'            => true,
    ),
    'versions' => array(
        '__root__'               => array(
            'pretty_version'  => 'dev-develop',
            'version'         => 'dev-develop',
            'reference'       => 'f77f8ca579b1dd65759564c3312a8e49df38197d',
            'type'            => 'library',
            'install_path'    => __DIR__ . '/../../',
            'aliases'         => array(),
            'dev_requirement' => false,
        ),
        'payfast/payfast-common' => array(
            'pretty_version'  => 'v1.0.2',
            'version'         => '1.0.2.0',
            'reference'       => '04c664a1d49e118c85507a56de35fe85eaee6048',
            'type'            => 'library',
            'install_path'    => __DIR__ . '/../payfast/payfast-common',
            'aliases'         => array(),
            'dev_requirement' => false,
        ),
    ),
);
