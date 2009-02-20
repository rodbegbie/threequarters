<?php
/******************************************************************************
 Pepper
 
 Developer		: Jeff Miller
 Plug-in Name	: Error Tracker
 
 http://www.xhtmled.com/
 
 ******************************************************************************

 Plug-ins classes must have a developer prefix (in this case XHTMLed_) and a 
 unique name within that developers plug-ins to prevent conflict with other
 developers and their plug-ins.

 The onEvent handlers must be present even if they perform no action.
 
 The $Mint object will always be available to the plug-in.

 Changelog:
 v1.06
   - Updated for Mint 1.2 by Geoffrey Grosenbach http://nubyonrails.com.

 v1.05
   - Corrected an issue with HTML entities in the preferences pane.
   - Added compatibility with the new getCfgValue() API calls. (Mint 1.1+ is now required)

 v1.04
   - The path to the error now links to the missing file and a secondary line has been added that displays and links to the referring page.
   - Truncated the error text to cut down on horizontal scrolling. (the full path may be viewed in the popup text by mousing over the error)

 v1.03
   - Added an error checking routine that displays an error if one or more of the required fields are missing in the Preferences pane.

 v1.02
   - Fixed a bug where the entire page title was being displayed instead of the error code.

 v1.01
   - Initial release.

 ******************************************************************************/

$installPepper = "XHTMLed_ErrorTracker";

class XHTMLed_ErrorTracker extends Pepper
{
	var $version	= 106;
	var $info		= array
	(
		'pepperName'	=> 'Error Tracker',
		'pepperUrl'		=> 'http://errortracker.xhtmled.com/',
		'pepperDesc'	=> 'Tracks common HTTP errors (401, 403, 404, and 500) that your visitors are receiving while accessing your site.',
		'developerName'	=> 'Jeff Miller',
		'developerUrl'	=> 'http://www.xhtmled.com/'
	);
	var $panes		= array
	(
		'Error Tracker'	=> array
		(
			'Most Common',
			'Most Recent'
		)
	);
	var $prefs		= array
	(
		'src'		=> 'xhtmled/errortracker/',
		'class'		=> 'XHTMLed_ErrorTracker'
	);
	var $manifest	= array();
	
	/**************************************************************************
	 isCompatible()
	 **************************************************************************/
	function isCompatible()
	{
		return array
		(
			'isCompatible'	=> true,
		);
	}
	
	/**************************************************************************
	 onRecord()
	 **************************************************************************/
	function onRecord()
	{
 		return array();
	}
	
	/**************************************************************************
	 onDisplay()
	 **************************************************************************/
 	function onDisplay($pane,$tab,$column='',$sort='') {
 		$html = '';

 		switch($pane) {
 		/* Visitors ***********************************************************/
 			case 'Error Tracker': 
 				switch($tab) {
 				/* Most Common ************************************************/
 					case 'Most Common':
 						$html .= $this->getHTML_ErrorsCommon();
 						break;
 				/* Most Recent ************************************************/
 					case 'Most Recent':
 						$html .= $this->getHTML_ErrorsRecent();
 						break;
 					}
 				break;
 			}
 		return $html;
 		}
	
	/**************************************************************************
	 onDisplayPreferences()
	 **************************************************************************/
   	function onDisplayPreferences() {
   		$unauthorizedTitle = htmlentities($this->prefs['unauthorizedTitle']);
   		$forbiddenTitle = htmlentities($this->prefs['forbiddenTitle']);
   		$fileNotFoundTitle = htmlentities($this->prefs['fileNotFoundTitle']);
   		$internalServerErrorTitle = htmlentities($this->prefs['internalServerErrorTitle']);

   		/* Global *************************************************************/
   		$preferences['Error Tracker']	= <<<HERE
   <table>
   	<tr>
   		<th scope="row">401</th>
   		<td><span><input type="text" id="unauthorizedTitle" name="unauthorizedTitle" value="$unauthorizedTitle" /></span></td>
   	</tr>
   	<tr>
   		<td></td>
   		<td>The title, eg. <code>401</code>, <code>Unauthorized</code>, etc. of your 401 error page.</td>
   	</tr>
   	<tr>
   		<th scope="row">403</th>
   		<td><span><input type="text" id="forbiddenTitle" name="forbiddenTitle" value="$forbiddenTitle" /></span></td>
   	</tr>
   	<tr>
   		<td></td>
   		<td>The title, eg. <code>403</code>, <code>Forbidden</code>, etc. of your 403 error page.</td>
   	</tr>
   	<tr>
   		<th scope="row">404</th>
   		<td><span><input type="text" id="fileNotFoundTitle" name="fileNotFoundTitle" value="$fileNotFoundTitle" /></span></td>
   	</tr>
   	<tr>
   		<td></td>
   		<td>The title, eg. <code>404</code>, <code>File Not Found</code>, etc. of your 404 error page.</td>
   	</tr>
   	<tr>
   		<th scope="row">500</th>		<td><span><input type="text" id="internalServerErrorTitle" name="internalServerErrorTitle" value="$internalServerErrorTitle" /></span></td>
   	</tr>
   	<tr>
   		<td></td>
   		<td>The title, eg. <code>500</code>, <code>Internal Server Error</code>, etc. of your 500 error page.</td>
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
    $this->prefs['unauthorizedTitle']	= $this->escapeSQL(($_POST['unauthorizedTitle'])?$_POST['unauthorizedTitle']:'401 Error');
    $this->prefs['forbiddenTitle']	= $this->escapeSQL(($_POST['forbiddenTitle'])?$_POST['forbiddenTitle']:'403 Error');
    $this->prefs['fileNotFoundTitle']	= $this->escapeSQL(($_POST['fileNotFoundTitle'])?$_POST['fileNotFoundTitle']:'404 Error');
    $this->prefs['internalServerErrorTitle']	= $this->escapeSQL(($_POST['internalServerErrorTitle'])?$_POST['internalServerErrorTitle']:'500 Error');
	}


	/**************************************************************************
	 getHTML_ErrorsRecent()
	 
	 **************************************************************************/
	function getHTML_ErrorsRecent() {
		$html = '';

		$tableData['table'] = array('id'=>'','class'=>'');
		$tableData['thead'] = array(
			// display name, CSS class(es) for each column
			array('value'=>'Errors','class'=>'stacked-rows'),
			array('value'=>'Type','class'=>''),
			array('value'=>'When','class'=>'')
			);

		$unauthorizedTitle = $this->prefs['unauthorizedTitle'];	
		$forbiddenTitle = $this->prefs['forbiddenTitle'];
		$fileNotFoundTitle = $this->prefs['fileNotFoundTitle'];
		$internalServerErrorTitle = $this->prefs['internalServerErrorTitle'];

		if ((!empty($unauthorizedTitle)) && (!empty($forbiddenTitle)) && (!empty($fileNotFoundTitle)) && (!empty($internalServerErrorTitle))) {
			
			$query = "SELECT dt, resource, resource_title, referer
					  FROM `{$this->Mint->db['tblPrefix']}visit` 
			 		  WHERE `resource_title` LIKE '%$unauthorizedTitle%' OR `resource_title` LIKE '%$forbiddenTitle%' OR `resource_title` LIKE '%$fileNotFoundTitle%' OR `resource_title` LIKE '%$internalServerErrorTitle%'
			 		  ORDER BY `dt` DESC 
					  LIMIT 0,{$this->Mint->cfg['preferences']['rows']}
					 ";

			if ($result = mysql_query($query)) {
				while ($r = mysql_fetch_array($result)) {
					$dt = $this->Mint->formatDateTimeRelative($r['dt']);
					$ref_title = (!empty($r['referer_title']))?stripslashes($r['referer_title']):$this->Mint->abbr($r['referer']);
					$resource = stripslashes($r['resource']);
					$resource = parse_url($resource);
					$resource = $resource['path'] . $resource['query'] . $resource['fragment'];

					$resource_title = stripslashes($r['resource_title']);
					$error_array = array("$unauthorizedTitle","$forbiddenTitle","$fileNotFoundTitle","$internalServerErrorTitle"); 
					$error_array_length = count($error_array); 

					for ($i=0;$i<$error_array_length;$i++) { 
						if (strstr($resource_title,$error_array[$i]))
							$error_type = $error_array[$i]; 
							}
				
					if ($error_type == $unauthorizedTitle)
						$error_type = '401';
					if ($error_type == $forbiddenTitle)
						$error_type = '403';
					if ($error_type == $fileNotFoundTitle)
						$error_type = '404';
					if ($error_type == $internalServerErrorTitle)
						$error_type = '500';

					$res_html = "<a href=\"$resource\" title=\"$resource\" rel=\"nofollow\">" . $this->Mint->abbr($resource) . "</a>";

					if (!empty($ref_title) && $this->Mint->cfg['preferences']['secondary'])
						$res_html .= "<br /><span>From <a href=\"{$r['referer']}\" title=\"{$r['referer']}\" rel=\"nofollow\">$ref_title</a></span>";

					$tableData['tbody'][] = array($res_html,
												  $error_type,
												  $dt
												 );
					}
				}
			} else {
				$tableData['tbody'][] = array("<p style=\"color: #c00; font-size: 120%;\">Required field(s) missing!</p><p>Please go to the Error Tracker section under the Preferences pane and enter a value for each of the fields.</p>",
											  '',
											  ''
											 );
			}

		$html = $this->Mint->generateTable($tableData);
		return $html;
		}

	/**************************************************************************
	 getHTML_ErrorsCommon()
	 	 
	 **************************************************************************/
	function getHTML_ErrorsCommon() {
		$html = '';
		
		$tableData['table'] = array('id'=>'','class'=>'');
		$tableData['thead'] = array(
			// display name, CSS class(es) for each column
			array('value'=>'Errors','class'=>'stacked-rows'),
			array('value'=>'Type','class'=>''),
			array('value'=>'Hits','class'=>'')
			);

		$unauthorizedTitle = $this->prefs['unauthorizedTitle'];	
		$forbiddenTitle = $this->prefs['forbiddenTitle'];
		$fileNotFoundTitle = $this->prefs['fileNotFoundTitle'];
		$internalServerErrorTitle = $this->prefs['internalServerErrorTitle'];

		if ((!empty($unauthorizedTitle)) && (!empty($forbiddenTitle)) && (!empty($fileNotFoundTitle)) && (!empty($internalServerErrorTitle))) {

			$query = "SELECT dt, referer, resource, resource_title, COUNT(`referer`) as 'total'
					  FROM `{$this->Mint->db['tblPrefix']}visit` 
			 		  WHERE `resource_title` LIKE '%$unauthorizedTitle%' OR `resource_title` LIKE '%$forbiddenTitle%' OR `resource_title` LIKE '%$fileNotFoundTitle%' OR `resource_title` LIKE '%$internalServerErrorTitle%'
					  GROUP BY referer
					  ORDER BY 'total' DESC, 'dt' DESC
					  LIMIT 0,{$this->Mint->cfg['preferences']['rows']}";
			if ($result = mysql_query($query)) {
				while ($r = mysql_fetch_array($result)) {
					$resource = stripslashes($r['resource']);
					$resource = parse_url($resource);
					$resource = $resource['path'] . $resource['query'] . $resource['fragment'];

					$resource_title = stripslashes($r['resource_title']);
					$error_array = array("$unauthorizedTitle","$forbiddenTitle","$fileNotFoundTitle","$internalServerErrorTitle"); 
					$error_array_length = count($error_array); 

					for ($i=0;$i<$error_array_length;$i++){ 
						if (strstr($resource_title,$error_array[$i]))
							$error_type = $error_array[$i]; 
					}

					if ($error_type == $unauthorizedTitle)
						$error_type = '401';
					if ($error_type == $forbiddenTitle)
						$error_type = '403';
					if ($error_type == $fileNotFoundTitle)
						$error_type = '404';
					if ($error_type == $internalServerErrorTitle)
						$error_type = '500';

					$tableData['tbody'][] = array("<a href=\"$resource\" title=\"{$r['referer']}\" rel=\"nofollow\">" . $this->Mint->abbr($resource) . "</a>",
												   $error_type,
												   $r['total']
												 );
					}
				}
			} else {
				$tableData['tbody'][] = array("<p style=\"color: #c00; font-size: 120%;\">Required field(s) missing!</p><p>Please go to the Error Tracker section under the Preferences pane and enter a value for each of the fields.</p>",
											  '',
											  ''
											 );
			}
		$html = $this->Mint->generateTable($tableData);
		return $html;
		}
	}

