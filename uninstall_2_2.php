<?php
/**
  * Database revert script for RPG Die Roller mod
  *
  * THIS SCRIPT SHOULD ONLY BE USED DURING MOD DEVELOPMENT
  * DO NOT INCLUDE IN PRODUCTION PACKAGES
  *
  * @author John Mechalas <john.mechalas@gmail.com>
  * @license BSD 3-Clause License
  */

if ( ! defined('SMF') ) require_once('SSI.php');

global $smcFunc, $modSettings;

db_extend('Packages');
db_extend('Extra');

$smcFunc['db_remove_column'](
	'smf_messages',
	'rpg_dr_seed',
	array( 'no_prefix' => false ),
	'fatal'
);


