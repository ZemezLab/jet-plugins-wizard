<?php

$license = array(
	'enabled' => true,
	'server'  => 'http://192.168.9.40/_2018/04_April/crocoblock-api/',
	'item_id' => 9,
);

/**
 * Plugins configuration example.
 *
 * array(
	'cherry-services-list' => array(
		'name'   => esc_html__( 'Cherry Services List', 'jet-plugins-wizard' ),
		'sourse' => 'wordpress', // 'git', 'local', 'remote', 'wordpress' (default).
		'path'   => false, // git repository, remote URL or local path.
		'access' => 'skins',
	),
	'cherry-data-importer' => array(
		'name'   => esc_html__( 'Cherry Data Importer', 'jet-plugins-wizard' ),
		'sourse' => 'git', // 'git', 'local', 'remote', 'wordpress' (default).
		'path'   => false, // git repository, remote URL or local path.
		'access' => 'base',
	),
)
 * or 'get_from_api' => URL
 *
 * @var array
 */
$plugins = array(
	'get_from' => 'http://192.168.9.40/_2018/04_April/crocoblock-api/wp-content/uploads/static/wizard-plugins.json',
);

/**
 * Skins configuration example
 * Format:
 * array(
		'base' => array(
			'cherry-data-importer',
		),
		'skins' => array(
			'default' => array(
				'full'  => array(
					'cherry-services-list',
				),
				'lite'  => false,
				'demo'  => false,
				'thumb' => false,
				'name'  => esc_html__( 'Default', 'jet-plugins-wizard' ),
			),
		),
	)
 * or 'get_from_api' => URL
 * @var array
 */
$skins = array(
	'get_from' => 'http://192.168.9.40/_2018/04_April/crocoblock-api/wp-content/uploads/static/wizard-skins.json',
);

/**
 * Default plugin texts
 *
 * @var array
 */
$texts = array(
	'theme-name' => 'Kava'
);
