<?php
/******************************************************************************
 Leaves

 Developer		: Shaun Inman
 Plug-in Name	: Leaves
 
 http://www.shauninman.com/
 
 ******************************************************************************/
 if (!defined('MINT')) { header('Location:/'); } // Prevent viewing this file 

define('MAX_FRAMES_DESKTOP', 3); // change to best suit your resolution/Mint addiction

$Mints[] = array // defaults to the current Mint installation
(
	'url'		=> $Mint->cfg['installFull'],
	'display'	=> $Mint->cfg['siteDisplay'],
	'email'		=> $Mint->cfg['email'],
	'password'	=> $Mint->cfg['password']
);

/******************************************************************************
 Copy the example below and paste it above. Then replace with the appropriate 
 values for each Mint installation that you wish to add to this web-based 
 Junior Mint. If you do not have an iPhone/iPod touch the dummy email and 
 password may be left as is.
 
 Eg.
 
 	$Mints[] = array
 	(
 		'url'		=> 'http://yourotherdomain.com/mint/',
		'display'	=> 'Site Name',
 		'email'		=> 'you@yourotherdomain.com',
 		'password'	=> 'very1234sercret'
 	);

 ******************************************************************************/