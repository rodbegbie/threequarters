<?php
/******************************************************************************
 Leaves

 Developer		: Shaun Inman
 Plug-in Name	: Leaves
 
 http://www.shauninman.com/
 
 ******************************************************************************/
 if (!defined('MINT')) { header('Location:/'); } // Prevent viewing this file

$labels = '';
$frames = '';
$cols	= '';

$col_count 		= count($Mints);
if ($col_count > MAX_FRAMES_DESKTOP)
{
	$col_count = MAX_FRAMES_DESKTOP;
}

if ($col_count > 1)
{
	$col_percent	= floor(100 / $col_count).'%,';
	$cols = str_repeat($col_percent, $col_count - 1);
}
$cols .= '*';

if (isset($_COOKIE['Mint_SI_monumint']))
{
	$display_order = explode(',', $_COOKIE['Mint_SI_monumint']);
	
	// if MAX_FRAMES_DESKTOP or the number of Mints has changed 
	// since the last time the cookie was set, fill out the 
	// display order
	if (count($display_order) < $col_count)
	{
		foreach($Mints as $id => $installation)
		{
			if (!in_array($id, $display_order))
			{
				$display_order[] = $id;
			}
			
			if (count($display_order) >= $col_count)
			{
				break;
			}
		}
	}
	else
	{
		$display_order = array_slice($display_order, 0, $col_count);
	}
}
else
{
	$display_order = array();
	for ($i = 0; $i < $col_count; $i++)
	{
		$display_order[] = $i;
	}
}

foreach($display_order as $f => $id)
{	
	$labels .= '<frame src="?label&id='.$id.'&f='.$f.'" />';
	$frames .= '<frame name="f'.$f.'" src="'.$Mints[$id]['url'].'" />';
}

$urls = array();
foreach ($Mints as $installation)
{
	$urls[] = $installation['url'];
}

$Mint->bakeCookie('Mint_SI_monumint', join(',', $display_order));

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>Monumint</title>
<script type="text/javascript" src="app/script.js"></script>
<script type="text/javascript" language="javascript">
// <![CDATA[

window.onload 		= function(){};
var displayOrder 	= Cookie.Mint_SI_monumint.split(',');
var urls 			= ['<?php echo join("','", $urls)?>'];

function d(f, id)
{
	frames['f' + f].location.href = urls[id];
	displayOrder[f] = id;
	Cookie.bake('Mint_SI_monumint', displayOrder.join(','));
};

// ]]>
</script>
</head>
<frameset rows="32,*" frameborder="0">
	<frameset cols="<?php echo $cols; ?>" frameborder="0">
<?php echo $labels; ?>
	</frameset>
	<frameset cols="<?php echo $cols; ?>" frameborder="0">
<?php echo $frames; ?>
	</frameset>
</frameset>
</html>