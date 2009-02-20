<?php
/******************************************************************************
 Pepper
 
 Developer: Joost de Valk
 Plug-in Name: TwitterCounter
 
 http://yoast.com/
 
 ******************************************************************************/
if (!defined('MINT')) { header('Location:/'); }; // Prevent viewing this file 

$installPepper = "Yoast_TwitterCounter";
	
class Yoast_TwitterCounter extends Pepper
{
	var $version	= 104; 
	var $info		= array
	(
		'pepperName'	=> 'TwitterCounter',
		'pepperUrl'		=> 'http://yoast.com/tools/mint/twittercounter/',
		'pepperDesc'	=> 'This Pepper will display TwitterCounter statistics within Mint.',
		'developerName'	=> 'Joost de Valk',
		'developerUrl'	=> 'http://yoast.com/'
	);
	var $panes = array
	(
		'TwitterCounter' => array
		(
			'Stats',
			'Last Week',
		)
	);
	var $prefs = array
	(
		'tcusername' => '',
		'tccache' => 0,
		'tccachetime' => 2
	);
	
	/**************************************************************************
	 isCompatible()
	 **************************************************************************/
	function isCompatible()
	{
		if ($this->Mint->version < 200)
		{
			$compatible = array
			(
				'isCompatible'	=> false,
				'explanation'	=> '<p>This Pepper requires Mint 2, a paid upgrade, now available at <a href="http://www.haveamint.com/">haveamint.com</a>.</p>'
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
	 onDisplay()
	 **************************************************************************/
	function onDisplay($pane, $tab, $column = '', $sort = '')
	{
		$html = '';
		
		switch($pane) 
		{
			case 'TwitterCounter': 
				switch($tab) 
				{
					case 'Stats':
						$html .= $this->getHTML_Overview();
					break;
					case 'Last Week':
						$html .= $this->getHTML_LastWeek();
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
		$preferences = array();
		$prefs = $this->prefs;

		if (isset($this->Mint->cfg['pepperLookUp']['Yoast_TwitterCounter']))
		{
			$preferences['Twitter username'] = <<<HERE
			<table>
				<tr>
					<td><label for="username">Twitter Username</label></td>
					<td><span><input type='text' id='username' name='tcusername' value='{$prefs['tcusername']}' /></span></td>
				</tr>
				<tr>
					<td colspan='2'>Enter your Twitter Username.</td>
				</tr>
			</table>
HERE;
			$checked = ($prefs['tccache'])?' checked="checked"':'';
			$preferences['Cache'] = <<<HERE
			<table class='snug'>
				<tr>
					<td><input type="checkbox" id="cache" name="tccache" class="cinch" value="0"$checked /> <label>Enable Cache</label></td>
				</tr>
				<tr>
					<td>Enabling caching greatly enhances load times. Before turning on caching, CHMOD the joostdevalk/twittercounter/cache folder to 777.</td>
				</tr>
			</table>
HERE;
			$preferences['Cache Expiration Time'] = <<<HERE
			<table class='snug'>
				<tr>
					<td>Cache Expires in </td>
					<td><span class='inline'><input type='text' id='cachetime' size='3' maxlength='3' name='tccachetime' value='{$prefs['tccachetime']}' class='cinch' /></span></td>
					<td> Hours</td>
				</tr>
			</table>
HERE;
		}
		
		return $preferences;

	}
	
	/**************************************************************************
	 onSavePreferences()
	 **************************************************************************/
	function onSavePreferences() 
	{
		$this->prefs['tcusername'] = $this->escapeSQL($_POST['tcusername']);
		$this->prefs['tccache'] = (isset($_POST['tccache']))?1:0;
		$this->prefs['tccachetime'] = $this->escapeSQL($_POST['tccachetime']);
	}
	
	/**************************************************************************
	onCustom()
	**************************************************************************/
	function onCustom() 
	{

	}
	
	/**************************************************************************
	get_TechnoratiData()
	**************************************************************************/
	function get_TwitterCounterData() {
		
		// Prep cache
		$username 	= $this->prefs['tcusername'];
		$cache 		= $this->prefs['tccache'];
		$cachetime 	= $this->prefs['tccachetime'];
		$cachefile 	= dirname(__FILE__) . '/cache/' . md5($username) . '.cache';
		$cachetime 	= $cachetime * 3600;
		
		// Read from cache if possible
		if ($cache) {			  	
			if (file_exists($cachefile))
			{
				$cachefile_created = @filemtime($cachefile);
				@clearstatcache();
				
				if (time() - $cachetime < $cachefile_created)
					return unserialize(file_get_contents($cachefile));
			}
		}
		
		require_once "class-snoopy.php";
		
		$snoopy = new Snoopy;
		
		$result = $snoopy->fetch("http://twittercounter.com/api/?username=".$username."&output=php&results=7");
		if ($result) {
			$data = array();
			$data = unserialize($snoopy->results);
			
			if (isset($data['error'])) {
				$data = false;
			} else {
				// Write cache information
				if ($cache) {
					$file_exists = file_exists($cachefile);
				   	$myfile = fopen($cachefile, "w");
					fwrite($myfile, serialize($data));
					if (is_resource($myfile && $file_exists))
						fclose($myfile);
				}				
			}
		} else {
			$data = false;
		}
		
		return $data;
	}
	
	/**************************************************************************
	 getHTML_Overview()
	 **************************************************************************/
	function getHTML_Overview()	{		
		$html = '';
		$error = false;
		
		if ( $this->prefs['tcusername'] != '' ) {

			$tableData['table'] = array('id'=>'','class'=>'inline-foot striped');
			
			$data = $this->get_TwitterCounterData();
			
			if ($data) {
				$tableData['tbody'][] = array("Followers",$data['followers_current']);
				$tableData['tbody'][] = array("Rank",$data['rank']);
				$tableData['tbody'][] = array("Added yesterday",($data['followers_current']-$data['followers_yesterday']));
				$tableData['tbody'][] = array("<strong>Prediction based total tracking time</strong>","");
				$tableData['tbody'][] = array("Average growth",number_format($data['average_growth']));
				$tableData['tbody'][] = array("Prediction tomorrow",$data['tomorrow']);
				$tableData['tbody'][] = array("Prediction next month",$data['next_month']);
				$tableData['tbody'][] = array("<strong>Prediction based on last two weeks</strong>","");
				$tableData['tbody'][] = array("Average growth",number_format($data['average_growth_2w']));
				$tableData['tbody'][] = array("Prediction tomorrow",$data['tomorrow_2w']);
				$tableData['tbody'][] = array("Prediction next month",$data['next_month_2w']);
				// $tableData['tbody'][] = array("TwitterCounter","<pre>Data: ".print_r($data,true)." End Data</pre>");
				
				$html = $this->Mint->generateTable($tableData);
			} else {
				$error = true;
			}
		} else {
			$error = true;
		}
		if ($error) {
			$html = $this->error_msg();
		}
		return $html;
	}
	
	function getHTML_LastWeek()	{		
		$html = '';
		$error = false;
		
		if ( $this->prefs['username'] != '' ) {

			$data = $this->get_TwitterCounterData();
			
			if (isset($data['error'])) {
				$error = true;
			} else {
				$graphData	= array(
					'titles' => array
					(
						'background' => 'Total',
					),
					'key' => array
					(
						'background' => 'Total',
					)
				);

				$high		= 0;
				$day		= $this->Mint->getOffsetTime('today');
						
				$data['followersperdate'] = array_reverse($data['followersperdate']);
			
				$i = 0;
				foreach($data['followersperdate'] as $date => $followers) {
					$timestamp = strtotime(str_replace("date","",$date));
					$dayOfWeek = $this->Mint->offsetDate('w', $timestamp);
					$dayLabel = substr($this->Mint->offsetDate('D', $timestamp), 0, 2);
	
					if ($followers > $high) {
						$high = $followers;
					}
					$graphData['bars'][] = array(
						$followers,
						0,
						($dayOfWeek == 0) ? '' : (($dayOfWeek == 6) ? 'Weekend' : $dayLabel),
						$this->Mint->formatDateRelative($timestamp, "day"),
						($dayOfWeek == 0 || $dayOfWeek == 6) ? 1 : 0	
					);
					$i++;
				}
			
				$graphData['bars'] = array_reverse($graphData['bars']);
				$html = $this->getHTML_Graph($high, $graphData);
			}
		} else {
			$error = true;
		}
		if ($error) {
			$html = $this->error_msg();
		}
		return $html;
	}
	
	function error_msg() {
		return '<p style="color:#000; font-weight:bold;">Please make sure you\'ve entered your Twitter username in the <a href="?preferences">preferences</a> correctly.</p>';
	}
}