<?php
/******************************************************************************
 Pepper
 
 Developer		: Shaun Inman
 Plug-in Name	: Refresh
 
 http://www.shauninman.com/
 
 ******************************************************************************/
if (!defined('MINT')) { header('Location:/'); }; // Prevent viewing this file
$installPepper = "SI_Doorbell";

class SI_Doorbell extends Pepper
{
	var $version	= 3;
	var $info		= array
	(
		'pepperName'	=> 'Doorbell',
		'pepperUrl'		=> 'http://www.haveamint.com/',
		'pepperDesc'	=> 'Ding-dong. You have company! The Doorbell Pepper will chirp and/or flash every time someone accesses a page on your site. Unique users flash green while repeat users flash white with a lower chirp. Requires Flash plugin 7 or later. Uses <a href="http://blog.deconcept.com/swfobject/">SWFObject</a> by <a href="http://blog.deconcept.com/">Geoff Stearns</a>.',
		'developerName'	=> 'Shaun Inman',
		'developerUrl'	=> 'http://www.shauninman.com/'
	);
	var $prefs		= array
	(
		'feedback'	=> 0
	);
	
	// Custom properties
	var $file		= 'pepper/shauninman/doorbell/lastvisit.txt';
	
	/**************************************************************************
	 isCompatible()
	 **************************************************************************/
	function isCompatible()
	{
		if ($this->Mint->version < 203)
		{
			$compatible = array
			(
				'isCompatible'	=> false,
				'explanation'	=> '<p>This Pepper requires Mint 2.03. Mint 2, a paid upgrade from Mint 1.x, is available at <a href="http://www.haveamint.com/">haveamint.com</a>.</p>'
			);
		}
		else
		{
			$compatible = array
			(
				'isCompatible'	=> true,
			);
		}
		return $compatible;
	}
	
	/**************************************************************************
	 onRecord()
	 **************************************************************************/
	function onRecord()
	{
		$unique = 0;
		if
		(
			$this->Mint->acceptsCookies &&
			(
				!isset($_COOKIE['MintUniqueHour']) || 
				(
					isset($_COOKIE['MintUniqueHour']) && 
					$_COOKIE['MintUniqueHour'] != $this->Mint->getOffsetTime('hour')
				)
			)
		)
		{
			$unique = 1;
		}
		
		if (file_exists($this->file) && is_writable($this->file))
		{
			$handle = fopen($this->file, 'w');
			fwrite($handle, "{$unique},".time());
			fclose($handle);
			return true;
		}
		
		return array();
	}
	
	/**************************************************************************
	 onAfterDisplay() 
	 **************************************************************************/
	function onAfterDisplay()
	{
		$html = '';
		
		if ($this->Mint->cfg['mode'] != 'client')
		{
			$last_visit = gmdate("U", filemtime($this->file));
			$html .= <<<HERE
<div id="doorbell-flasher" style="position:fixed;top:0;left:0;"></div>
<div id="doorbell" style="position:fixed;top:0;left:0;"></div>
<script type="text/javascript" src="pepper/shauninman/doorbell/doorbell.js"></script>
<script type="text/javascript" language="javascript">
// <![CDATA[
SI.Doorbell.feedback	= {$this->prefs['feedback']};
SI.Doorbell.lastvisit	= {$last_visit};
SI.Doorbell.init();
// ]]>
</script>
HERE;
	}
	
	return $html;
	}
	
	/**************************************************************************
	 onDisplayPreferences()
	 **************************************************************************/
	function onDisplayPreferences() 
	{
		$audio	= ($this->prefs['feedback'] == 0) ? ' checked="checked"' : '';
		$visual	= ($this->prefs['feedback'] == 1) ? ' checked="checked"' : '';
		$both	= ($this->prefs['feedback'] == 2) ? ' checked="checked"' : '';

		if (!is_writable($this->file))
		{
			$preferences['Warning'] = <<<HERE
<table>
	<tr>
		<td>The Doorbell Pepper's <code>lastvisit.txt</code> file does not appear to be writable. Please update its permissions accordingly.</td>
	</tr>
</table>
HERE;
		}
		
		$preferences['Feedback'] = <<<HERE
<table class="snug">
	<tr>
		<td style="padding-right: 10px;"><label><input type="radio" name="doorbellFeedback" value="0" {$audio} /> Audible chirp</label></td>
		<td style="padding-right: 10px;"><label><input type="radio" name="doorbellFeedback" value="1" {$visual} /> Visual flash</label></td>
		<td style="padding-right: 10px;"><label><input type="radio" name="doorbellFeedback" value="2" {$both} /> Both</label></td>
	</tr>
</table>
HERE;

		return $preferences;
	}

	/**************************************************************************
	 onSavePreferences()
	 **************************************************************************/
	function onSavePreferences() 
	{
		$this->prefs['feedback'] = (isset($_POST['doorbellFeedback'])) ? $_POST['doorbellFeedback'] : 0;
	}
}