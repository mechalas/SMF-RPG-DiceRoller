<?php

/**
 * Core functions for RPG Dice Roller mod fo SMF
 *
 * @author John Mechalas <john.mechalas@gmail.com>
 * @copyright 2019 John Mechalas
 * @license BSD 3-Clause
 * @version 1.1.1
 */

/**
 * Callback for integrate_pre_load
 *
 */
function rpg_dice_init()
{
	global $RPGDR, $RPGDRSEED, $RPGDRINIT;
	
	$RPGDR= array();
	$RPGDRSEED= array();

	$RPGDR['init']= 1;
	$RPGDR['msg_list']= '';
	$RPGDR['req']= array();

	$RPGDRINIT=1;
}

/**
 * Callback for integrate_bbc_codes
 *
 * @param array &$bbc
 * @return void
 */

function rpg_dice_bbc(array &$bbc)
{
	/*
	 * The bbc hook can't post-process the content, so we cheat
	 * and use validate to call an anonymous function, which allows
	 * us to replace the local &$data in Subs.php.
	 * 
	 * This is a hack, but without post-processing options on a per-
	 * post basis, the only alternative is inserting code into Subs.php
	 */

	$bbc[] = array(
		'tag' => 'dice',
		'type' => 'unparsed_equals_content',
		'content' => '<div class="rpg_dice_roll"><span class="rpg_dice_label">$2: </span>$1</div>',
		'validate' => function(&$tag, &$data, $disabled) { return $data[0] = rpg_dice_roller_evaluate($data[0]); } 
	);
		#'validate' => create_function('&$tag, &$data, $disabled', '$data[0] = rpg_dice_roller_evaluate($data[0]);')

	$bbc[] = array(
		'tag' => 'dice',
		'type' => 'unparsed_content',
		'content' => '<div class="rpg_dice_roll">$1</div>',
		'validate' => function(&$tag, &$data, $disabled) { return $data = rpg_dice_roller_evaluate($data); }
	);
		#'validate' => create_function('&$tag, &$data, $disabled', '$data = rpg_dice_roller_evaluate($data);'),
}

/**
 * Callback for integrate_bbc_button
 *
 * @param array &$buttons
 * @return void
 */

function rpg_dice_button(array &$buttons)
{
	$buttons[count($buttons) - 1][] = array (
		'image'	=> 'rpg_dr_dice',
		'code' => 'dice',
		'before' => '[dice]',
		'after' => '[/dice]',
		'description' => 'Dice roller'
	);
}

/**
 * Insert a stylesheet link.
 *
 */
function rpg_dice_css()
{
	global $context, $settings;

	$context['insert_after_template'] .= '
	<link rel="stylesheet" type="text/css" href="' . (file_exists($settings['theme_dir'] . '/css/rpgdiceroller.css') ? $settings['theme_url'] : $settings['default_theme_url']) . '/css/rpgdiceroller.css" />';
}

/**
 * Evaluate a dice expression and return the result string.
 *
 * @param string $roll_string
 * @return $string
 */
function rpg_dice_roller_evaluate($roll_string, $critconfirm=0)
{
	global $RPGDR, $RPGDRSEED, $context;
	global $context;
	$parts= array();
	$rolls= '';
	$output= '';
	$msgid= -1;
	$lmsgid= -2;
	$seed= 0;
	$potentialcrit= 0;

	$RPG_Dice_Roller_error_badexp= 'Bad dice expression "'.$roll_string.'"';

	if ( is_null($roll_string) || strlen($roll_string) == 0 ) return '';

	/* If this is a JavaScript modify, only parse when the modifcation is done */

	if ( array_key_exists('current_action', $context) && 
		$context['current_action'] == 'jsmodify' &&
		(!array_key_exists('sub_template', $context) || 
		$context['sub_template'] != 'modifydone' ) ) return '';

	$msgid= $RPGDR['msg_id'];

	/* Are we editing a message? */
	if ( empty($msgid) ) {
		if ( array_key_exists('msg', $_REQUEST) ) {
			$msgid= $_REQUEST['msg'];
		} else {
			$msgid= -1;
		}
		$RPGDR['msg_id']= $msgid;
	}

	/* Are we in a new message? */

	if ( ! array_key_exists($msgid, $RPGDRSEED) )
	{
		/* Get our message's seed, if we have one. Then save it. */

		$seed= rpg_dice_get_seed($msgid);
		mt_srand($seed);
		$RPGDRSEED[$msgid]= $seed;
	}

	/* Tokenize */

	$parts= preg_split("(([0-9%dcbp]+|\+|-)|\s+)", $roll_string, null,
		PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
	if ( count($parts) % 2 == 0 ) return $RPG_Dice_Roller_error_badexp;

	$parts= array_map('trim', $parts);
	$output.= join(' ', $parts) . " => ";

	$total= dice_eval($parts[0], $rolls, $potentialcrit, $critconfirm);
	if ( is_null($total) ) return $RPG_Dice_Roller_error_badexp;
	$output.= $rolls;

	/* Run through our list of tokens and do the math */
	for ($i= 1; $i< count($parts); $i+= 2) 
	{
		$rolls= "";

		/* $i should be an operator */
		/* $i+1 should be a dice or numeric string */

		$operator= $parts[$i];
		$operand= dice_eval($parts[$i+1], $rolls, $potentialcrit, $critconfirm);
		if ( is_null($operand) ) return $RPG_Dice_Roller_error_badexp;

		if ( $operator == '+' ) {
			$total+= $operand;
		} else if ( $operator == '-' ) {
			$total-= $operand;
		} else {
			return $RPG_Dice_Roller_error_badexp;
		}

		$output.= ' ' . $operator . ' ' . $rolls;
	}
	$output.= ' = <span class="rpg_dice_total">' . $total . '</span>';

	if ( $potentialcrit && !$critconfirm ) {
		$roll_string= preg_replace('/c[0-9]*b/', '+', $roll_string);
		$roll_string= preg_replace('/c[0-9]*p/', '-', $roll_string);
		$roll_string= preg_replace('/c[0-9]*/', '', $roll_string);
		$output.= '<br/><span class="rpg_crit_confirm">Confirm Critical:</span>&nbsp;' .
			rpg_dice_roller_evaluate($roll_string, 1);
			
	}

	return $output;
}

/**
 * Evaluate an individual dice roll and return the sum. An breakout of
 * individual dice rolls is placed in $rolls.
 *
 * @param string $dice_expression
 * @param string &$rolls
 * @param int &$potentialcrit
 * @param int &$modconfirm
 * @param int $critconfirm
 * @return $string
 */
function dice_eval($expr, &$rolls, &$potentialcrit, $critconfirm)
{
	global $RPG_Dice_Roller_dice_regex;

	$matches= array();
	$rolls_ar= array();
	$criton= NULL;

	/* Just a number? */
	if ( preg_match('/^[0-9]+$/', $expr) ) 
	{
		$rolls= $val= $expr*1;
		return $val;
	}

	/* Valid dice expressions */
	
	if (preg_match('/^([1-9][0-9]{0,3})?d([1-9][0-9]{0,3}|%)$/', $expr, $matches)) {
		$ndice= $matches[1];
		if ( empty($ndice) ) $ndice= 1;
		$dsize= $matches[2];
		if ( $dsize == '%' ) $dsize=100;

	} else if (preg_match('/^1?d([1-9][0-9]{0,3}|%)(c([1-9][0-9]{0,3})?)([bp]([1-9][0-9]*))?$/', $expr, $matches)) {
		$ndice= 1;
		$dsize= $matches[1];
		if ( $dsize == '%' ) $dsize=100;
		if ( ! $critconfirm ) {
			if ( $potentialcrit ) return null;
			$criton= (count($matches) > 3 && $matches[3] != "" ) ?
				$matches[3] : $dsize;
		} 
	} else {
		return null;
	}

	$total= 0;
	for ($j= 0; $j< $ndice; ++$j) 
	{
		$val= mt_rand(1, $dsize);
		$total+= $val;
		if ( $criton && $val >= $criton && !$critconfirm ) {
			$potentialcrit= 1;
		} 
		array_push($rolls_ar, $val);
	}

	$rolls= "(" . join(", ", $rolls_ar) . ")";
	if ( $criton && $potentialcrit ) {
		$rolls= '<span class="rpg_crit_confirm">' .  $rolls . "</span>";
	}

	return $total;
}

/**
 * Insert the seed into the database.
 *
 * @param int msgid
 * @return void
 */
function rpg_dice_insert_seed($msgid)
{
	global $smcFunc, $RPGDR, $RPGDRSEED;

	if ( is_null($msgid) || empty($msgid) ) return false;

	/*
	 * If this is a new post, its seed hasn't been stored yet.
	 * Generate it using the deterministic algorithm and store
	 * it in the DB.
	 */

	if ( array_key_exists($msgid, $RPGDRSEED) ) {
		$seed= $RPGDRSEED[$msgid];
	} else {
		$seed= rpg_dice_gen_seed();
	}

	$request= $smcFunc['db_query']('', '
		UPDATE {db_prefix}messages AS m
		SET m.rpg_dr_seed = {int:seed}
		WHERE id_msg = {int:msgid}',
		array(
			'seed' => $seed,
			'msgid' => $msgid,
		)
	);
}

/**
 * Fetch the seed for the message from the database.
 *
 * @param int $msgid
 * @return int $seed
 */
function rpg_dice_get_seed($msgid)
{
	global $smcFunc;

	if ( is_null($msgid) || empty($msgid) ) return rpg_dice_gen_seed();

	$request= $smcFunc['db_query']('', '
		SELECT m.rpg_dr_seed
		FROM {db_prefix}messages AS m
		WHERE id_msg = {int:msgid}',
		array(
			'msgid' => $msgid,
		)
	);
	list ($seeds) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	/*
	 * There should be only one since msgid is unique (if it isn't, then we
	 * have database problems too big and too global to solve here).
	 */

	if ( is_null($seeds) ) return rpg_dice_gen_seed();
	return $seeds;
}

/* Generate a repeatable seed to discourage shenanigans */

function rpg_dice_gen_seed()
{
	global $user_info, $context, $smcFunc;

	$iparr= array();
	$seed= 0;

	/* Base our seed on the numebnr of posts the user has made in this
	 * topic and the high octets of their IP address */

	$request= $smcFunc['db_query']('', '
		SELECT COUNT(m.rpg_dr_seed)
		FROM {db_prefix}messages AS m
		WHERE id_member = {int:user} AND
		id_topic = {int:topic}',
		array(
			'user' => $user_info['id'],
			'topic' => $context['current_topic']
		)
	);
	list($cnt) = $smcFunc['db_fetch_row']($request);
	$smcFunc['db_free_result']($request);

	$iparr= explode(".",$user_info['ip']);
	$seed= crc32($cnt^(($iparr[0]<<8)|($iparr[1])));

	return $seed;
}
