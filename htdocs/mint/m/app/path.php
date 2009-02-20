<?php
/******************************************************************************
 Leaves

 Developer		: Shaun Inman
 Plug-in Name	: Leaves
 
 http://www.shauninman.com/
 
 ******************************************************************************/
 if (!defined('MINT')) { header('Location:/'); } // Prevent viewing this file

define('MINT_EMBEDDED', true);
define('MINT_ROOT', str_replace('m/app/path.php', '', __FILE__));
include(MINT_ROOT.'app/lib/mint.php');
include(MINT_ROOT.'config/db.php');

if (!$Mint->isLoggedIn())
{
	header('Location:../');
	exit();
}

include(MINT_ROOT.'m/config/mints.php');
include(MINT_ROOT.'m/app/lib.php');

if (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone') || strpos($_SERVER['HTTP_USER_AGENT'], 'iPod')) !== false)
{
	include('paths/jr.php');
}
else
{
	if (isset($_GET['label']))
	{
		include('paths/label.php');
	}
	else
	{
		include('paths/monumint.php');
	}
}
