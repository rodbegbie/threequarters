<?php
/******************************************************************************
 Pepper
 
 Developer		: Shaun Inman
 Plug-in Name	: Directories Pepper
 
 http://www.shauninman.com/

 ******************************************************************************/
if (!defined('MINT')) { header('Location:/'); }; // Prevent viewing this file
$installPepper = "SI_Directories";

class SI_Directories extends Pepper
{
	var $version	= 101;
	var $info		= array
	(
		'pepperName'	=> 'Directories',
		'pepperUrl'		=> 'http://www.haveamint.com/',
		'pepperDesc'	=> 'The Directories Pepper breaks your page views down by their top-level parent directory. Advanced preferences allow you to specify the matching regular expression (to target sub-directories) and ignore pages outside of those directories.',
		'developerName'	=> 'Shaun Inman',
		'developerUrl'	=> 'http://www.shauninman.com/'
	);
	var $panes		= array
	(
		'Directories'	=> array
		(
			'Refresh'
		),
	);
	var $prefs		= array
	(
		'directoryRegEx'			=> '/([^/]+)/',
		'ignoreRoot'				=> 0
	);
	var $manifest	= array
	(
		'visit'	=> array
		(
			'directory' 			=> "VARCHAR(255) NOT NULL",
			'directory_checksum' 	=> "INT(10) NOT NULL"
		)
	);
	
	/**************************************************************************
	 isCompatible()
	 **************************************************************************/
	function isCompatible()
	{
		if ($this->Mint->version < 215)
		{
			$compatible = array
			(
				'isCompatible'	=> false,
				'explanation'	=> '<p>This Pepper requires Mint 2.15. Mint 2, a paid upgrade from Mint 1.x, is available at <a href="http://www.haveamint.com/">haveamint.com</a>.</p>'
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
 		$resource	= $this->escapeSQL(preg_replace('/#.*$/', '', htmlentities($_GET['resource'])));
		$directory	= '/';
		$regex 		= '#(?:'.str_replace('.', '\.', implode('|', $this->Mint->domains)).')(?:\:\d+)?('.$this->prefs['directoryRegEx'].')#i';
		
		if (preg_match($regex, $resource, $m))
		{
			$directory = isset($m[2]) ? $m[2] : $m[1];
		}

 		return array
 		(
	 		'directory' 			=> $directory,
		 	'directory_checksum'	=> crc32($directory)
		);
	}
	
	/**************************************************************************
	 onDisplay()
	 **************************************************************************/
	function onDisplay($pane, $tab, $column = '', $sort = '')
	{
		$html = '';
		
		switch($pane) 
		{
			case 'Directories': 
				switch($tab) 
				{
					case 'Refresh':
						$html .= $this->getHTML_Directories();
					break;
				}
			break;
		}
		return $html;
	}
	
	/**************************************************************************
	 onDisplayPreferences()
	 **************************************************************************/
	function onDisplayPreferences()
	{
		/* Directories **********************************************************/
		$regex = $this->prefs['directoryRegEx'];
		
		$preferences['Directory Matching']	= <<<HERE
			<table>
				<tr>
					<td>The default regular expression (<code>/([^/]+)/</code>) will match any top-level directory. The contents of the outermost pair of parentheses will be used as the directory display name.</td>
				</tr>
				<tr>
					<td><span><input type="text" id="directoryRegEx" name="directoryRegEx" value="{$this->prefs['directoryRegEx']}" /></span></td>
				</tr>
			</table>

HERE;

		/* Global *************************************************************/
		$checked = $this->prefs['ignoreRoot'] ? ' checked="checked"' : '';
		$preferences['Unmatched']	= <<<HERE
<table>
	<tr>
		<td>Umatched pages are grouped under root (<code>/</code>).</td>
	</tr>
	<tr>
		<td><label><input type="checkbox" name="ignoreRoot" value="1"$checked /> Ignore unmatched pages</label></td>
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
		$this->prefs['directoryRegEx']	= $this->escapeSQL($_POST['directoryRegEx']);
		$this->prefs['ignoreRoot']		= isset($_POST['ignoreRoot']) ? $_POST['ignoreRoot'] : 0;
	}
	
	/**************************************************************************
	 onCustom()
	 **************************************************************************/
	function onCustom() 
	{
		if
		(
			isset($_POST['action']) 		&& 
			$_POST['action']=='getDirectoryPages'	&& 
			isset($_POST['directory_checksum'])
		)
		{
			$directory_checksum = $this->escapeSQL($_POST['directory_checksum']);
			echo $this->getHTML_DirectoryPages($directory_checksum);
		}
	}
	
	/**************************************************************************
	 getHTML_Directories()
	 **************************************************************************/
	function getHTML_Directories()
	{
		$html = '';
		
		$tableData['hasFolders'] = true;
		$tableData['table'] = array('id'=>'','class'=>'folder');
		$tableData['thead'] = array
		(
			// display name, CSS class(es) for each column
			array('value'=>'Pages','class'=>'sort'),
			array('value'=>'Directory/Pages','class'=>'focus'),
			array('value'=>'Hits','class'=>'sort')
		);
		
		$ignoreRoot = '';
		if ($this->prefs['ignoreRoot'])
		{
			$ignoreRoot = "AND `directory_checksum` != '2043925204'";
		}
		
		$query = "SELECT `directory`, `directory_checksum`, COUNT(DISTINCT `resource_checksum`) as `pages`, COUNT(`directory_checksum`) as `hits`, `dt`
					FROM `{$this->Mint->db['tblPrefix']}visit`
					WHERE `directory_checksum` != 0 $ignoreRoot
					GROUP BY `directory_checksum` 
					ORDER BY `hits` DESC, `pages` DESC , `dt` DESC 
					LIMIT 0,{$this->Mint->cfg['preferences']['rows']}";
		if ($result = $this->query($query)) 
		{
			while ($r = mysql_fetch_array($result)) 
			{
				$directory = $this->Mint->abbr($r['directory']);
				$tableData['tbody'][] = array
				(
					$r['pages'],
					$directory.' ', // space prevents year directories from being formatted as numbers, eg. 2007 != 2,007
					$r['hits'],

					'folderargs' => array
					(
						'action'				=>'getDirectoryPages',
						'directory_checksum'	=>$r['directory_checksum']
					)
				);
			}
		}
		$html = $this->Mint->generateTable($tableData);
		return $html;
	}
	
	/**************************************************************************
	 getHTML_DirectoryPages()
	 **************************************************************************/
	function getHTML_DirectoryPages($directory_checksum)
	{
		$html = '';
		
		$query = "SELECT `resource`, `resource_title`, COUNT(`resource_checksum`) as `total`, `dt`
					FROM `{$this->Mint->db['tblPrefix']}visit` 
					WHERE `directory_checksum` = {$directory_checksum}
					GROUP BY `resource_checksum` 
					ORDER BY `total` DESC, `dt` DESC ";
		
		$v = array();
		$tableData['classes'] = array
		(
			'sort',
			'focus',
			'sort'
		);
		
		if ($result = $this->query($query))
		{
			while ($r = mysql_fetch_array($result))
			{
				$res_title = $this->Mint->abbr((!empty($r['resource_title']))?stripslashes($r['resource_title']):$r['resource']);
				$tableData['tbody'][] = array
				(
					'&nbsp;',
					"<a href=\"{$r['resource']}\">$res_title</a></span>",
					$r['total']
				);
			}
		}
		
		$html = $this->Mint->generateTableRows($tableData);
		return $html;
	}
}