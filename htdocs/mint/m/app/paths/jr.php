<?php
/******************************************************************************
 Leaves

 Developer		: Shaun Inman
 Plug-in Name	: Leaves
 
 http://www.shauninman.com/
 
 ******************************************************************************/
 if (!defined('MINT')) { header('Location:/'); } // Prevent viewing this file

if ($Mint->version < 200)
{
	die('Junior Mints requires Mint 2.');
}

$html = '';

foreach($Mints as $Junior)
{
	$mint_link 	= $Mint->abbr($Junior['url']);
	$response 	= post($Junior['url'], "widget=true&mode=auth&action=Login&email={$Junior['email']}&password={$Junior['password']}");
	$junior_html = $response['body'];
	
	if (preg_match('#<!-- Mint Request Received -->#', $junior_html))
	{
			$junior_html = '<div class="error">This widget requires Mint 2.</div>';
	}
	else if (!preg_match('#<!-- Mint 2 Request Received -->#', $junior_html))
	{
		$junior_html = '<div class="error">Could not find Mint installation.</div>';
	}
	else
	{
		$junior_html = str_replace('id="', 'class="', $junior_html);

		if (preg_match('#<!-- Mint Error -->#', $junior_html))
		{
			$junior_html 	= str_replace(array('<!-- Mint 2 Request Received --><!-- Mint Error -->', '<div class="visits-list"><div class="total">Error</div></div>'), '', $junior_html);
			$junior_html 	= str_replace('class="fresh"', 'class="error"', $junior_html);

		}
		else
		{
			preg_match('#(.+<a href="[^"]+">(.*)</a></div>)#', $junior_html, $m);
			$mint_link 		= $m[2];
			$junior_html 	= str_replace($m[1], '', $junior_html);
		}
	}
	
	$junior_html 	= '<div class="junior-mint">'.'<a href="'.$Junior['url'].'" target="_blank">On '.$mint_link.'</a>'.$junior_html.'</div>';
	$html .= $junior_html;
}

$body_class = isset($_COOKIE['Mint_SI_jrmint']) ? $_COOKIE['Mint_SI_jrmint'] : 'ever';
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>Junior Mints</title>
<meta name="viewport" content="width=320; initial-scale=1; maximum-scale=1; user-scalable=0;" />
<link type="text/css" rel="stylesheet" href="app/styles/jr.css" />
<script type="text/javascript" src="app/script.js"></script>
</head>
<body class="<?php echo $body_class; ?>">
<div id="header">
	<a href=""><span>MINT</span>A Fresh Look at your Sites</a>
</div>
<div id="content">

<?php echo $html; ?>	
	
</div>
</body>
</html>