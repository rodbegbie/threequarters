<?php
/******************************************************************************
 Leaves

 Developer		: Shaun Inman
 Plug-in Name	: Leaves
 
 http://www.shauninman.com/
 
 ******************************************************************************/
 if (!defined('MINT')) { header('Location:/'); } // Prevent viewing this file

$f 			= $_GET['f'];
$id 		= $_GET['id'];
$url		= $Mints[$id]['url'];
$display	= $Mints[$id]['display'];

$select = '';
foreach ($Mints as $i => $installation)
{
	$selected = ($i == $id) ? ' selected="selected"' : '';
	$select .= '<option value="'.$i.'"'.$selected.'>'.$installation['display'].'</option>';
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<title>Mint: <?php echo $display; ?></title>
<link type="text/css" rel="stylesheet" href="app/styles/label.css" />
<script type="text/javascript" language="javascript">
// <![CDATA[

function u(e)
{
	var option 	= e.options[e.selectedIndex];
	var id		= option.value;
	var label	= document.getElementById('label');

	label.innerHTML = option.text;
	label.parentNode.href = parent.urls[id];
	parent.d(<?php echo $f; ?>, id);
};

// ]]>
</script>
</head>
<body>
<div><a href="<?php echo $url; ?>" target="_top"><span id="label"><?php echo $display; ?></span></a></div>
<select onchange="u(this);">
	<?php echo $select; ?>
</select>
</body>
</html>