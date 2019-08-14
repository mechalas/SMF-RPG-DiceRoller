<?php
/**
  * Installation script for RPG Die Roller mod
  *
  * @author John Mechalas <john.mechalas@gmail.com>
  * @license BSD 3-Clause License
  */

if ( ! defined('SMF') ) require_once('SSI.php');

global $smcFunc, $modSettings;

db_extend('Packages');
db_extend('Extra');

$smcFunc['db_add_column'](
	'smf_messages',
	array(
		'name'		=> 'rpg_dr_seed',
		'type'		=> 'int',
		'null'		=> 'true',
		'default'	=> NULL,
		'auto'		=> false,
		'unsigned'	=> true
	),
	array( 'no_prefix' => false ),
	'',
	'fatal'
);

$hooks = array(
	'integrate_pre_include' => '$sourcedir/RPGDice.php',
	'integrate_pre_load' => 'rpg_dice_init',
	'integrate_bbc_codes' => 'rpg_dice_bbc',
	'integrate_bbc_buttons' => 'rpg_dice_button',
	'integrate_personal_message'=> 'rpg_dice_msg'
);


foreach ($hooks as $hook => $function)
	add_integration_function($hook, $function);

