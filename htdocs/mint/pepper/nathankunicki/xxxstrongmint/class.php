<?php
/******************************************************************************
 Pepper
 
 Developer		: Nathan Kunicki
 Plug-in Name	: XXX Strong Mint
 
 http://www.dynamiclatitude.com/
 
 ******************************************************************************
 Plug-ins classes must have a developer prefix (in this case NK_) and  a 
 unique name within that developers plug-ins to prevent conflict with other
 developers and their plug-ins.
 The onEvent handlers must be present even if they perform no action.
 
 The $Mint object will always be available to the plug-in.
 ******************************************************************************/
 
//error_reporting(0);
$installPepper = "NK_XXXStrongMint";

class NK_XXXStrongMint extends Pepper { 

	var $version	= 221; 
	var $info		= array
	(
		'pepperName'	=> 'XXX Strong Mint',
		'pepperUrl'		=> 'http://www.dynamiclatitude.com/xxxstrong/',
		'pepperDesc'	=> 'XXX Strong Mint, consume with caution!  XXX Strong Mint exposes the most sensitive part of your visitors to you, their IP address!',
		'developerName'	=> 'Nathan Kunicki',
		'developerUrl'	=> 'http://www.dynamiclatitude.com/'
	);
	var $panes		= array
	(
		'XXX Strong Mint'	=> array
		(
			'Most Recent',
			'Repeat',
			'Search'
		)
	);
	var $manifest	= array
	(
		'visit'	=> array
		(
			'xxx_proxy_ip' 			=> "VARCHAR(15) NOT NULL",
			'xxx_hostname' 			=> "VARCHAR(100) NOT NULL",
			'xxx_proxy_hostname'	=> "VARCHAR(100) NOT NULL",
			'xxx_ip'				=> "VARCHAR(15) NOT NULL"
		)
	);
	var $prefs      = array
	(
		'highlightProxies'          => 1,
		'showIPReferers'          	=> 1,
		'onTheFly'         			=> 0,
		'hostnameOnTop'    			=> 0
	);
	
	/**************************************************************************
	 isCompatible()
	 **************************************************************************/
function isCompatible()
{
    if ($this->Mint->version >= 120)
    {
        return array
        (
            'isCompatible'  => true
        );
    }
    else
    {
        return array
        (
            'isCompatible'  => false,
            'explanation'   => '<p>XXX Strong Mint is only compatible with Mint 1.2 and higher.</p>'
    );
    }
}


/******************************************************************************
 Plug-in Constructor
 Establishes a link with the current $Mint instance and defines some required 
 information about this plug-in
 ******************************************************************************
	function NK_XXXStrongMint($plugin_id) {
		global $Mint;
		$this->Mint =& $Mint;
		$this->plugin_id = $plugin_id;
		
		// Used to display info about this plug-in
		$this->info['version']			= "2.1";
		$this->info['developer']		= "Nathan Kunicki";
		$this->info['plugin']			= "XXX Strong Mint";
		$this->info['description']		= "XXX Strong Mint, consume with caution!  XXX Strong Mint exposes the most sensitive part of your visitors to you, their IP address!";
		$this->info['developer_url']	= "http://www.dynamiclatitude.com/";
		$this->info['documentation']	= "http://www.dynamiclatitude.com/xxxstrong/";
		
		// Used internally
		$this->info['src']				= "nathankunicki/xxxstrongmint/";
		$this->info['class']			= "NK_XXXStrongMint";
		
		// Panes used to keep track of the data displayed by this plug-in and 
		// for ordering preferences
		$this->panes['XXX Strong Mint']	= array('Most Recent', 'Repeat', 'Search');
		}
	
*/

	/**************************************************************************
	 install()
	 This function will be called by the Mint plug-in installer
	 
	 It may add columns to the Mint table but the onRecord/onJavaScript event 
	 handlers must provide and validate the necessary data
	 
	 **************************************************************************
	function install() {
	
		$prefs['highlightProxies']	= 1;
		$prefs['showIPReferers']	= 1;
		$prefs['onTheFly']			= 0;
		$prefs['hostnameOnTop']		= 0;
		$this->Mint->savePluginPreferences($this->plugin_id,$prefs);
		$query = "ALTER TABLE {$this->Mint->db['tblprefix']}visit ADD xxx_proxy_ip VARCHAR(15) NOT NULL, ADD xxx_hostname VARCHAR(100) NOT NULL, ADD xxx_proxy_hostname VARCHAR(100) NOT NULL";
					
		if (mysql_query($query)) {
			$this->Mint->registerPlugIn($this->info['src'],$this->info['class'],$this->panes);
			}
		}
		*/
	/**************************************************************************
	 uninstall()
	 This function will be called by the Mint plug-in remover
	 
	 Should delete any columns or additional tables added by $this->install().
	 Mint will take care of deleting any associated preferences or data
	 
	 Need to:
	 - Reconsider this as other plug-ins may form dependancies on these 
	 non-standard columns
	 
	 **************************************************************************
	function uninstall() {
		$query = "ALTER TABLE {$this->Mint->db['tblprefix']}visit DROP xxx_proxy_ip, DROP xxx_hostname, DROP xxx_proxy_hostname";
		mysql_query($query);
		}
		*/
	
	/**************************************************************************
	 onRecord()
	 Operates on existing $_GET values, values generated as a result of the 
	 JavaScript output below or existing $_SERVER variables and returns an 
	 associative array with a column name as the index and the value to be 
	 stored in that column as the value.
	 **************************************************************************/
	function onRecord() {	
 		// $prefs = $this->Mint->getPluginPreferences($this->plugin_id);
 		
 		$proxy_ip_address = "";
 	
 		$proxy_ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
		$proxy_ip_address = $this->Mint->escapeSQL($proxy_ip_address);
			
		$hostname = gethostbyaddr($_SERVER['REMOTE_ADDR']);
		$proxy_hostname = "";
		
		if ($proxy_ip_address != "") {
			if ($proxy_ip_address != "unknown") {
				$proxy_hostname = gethostbyaddr($proxy_ip_address);
			}
		}			
 		
 		return array('xxx_proxy_ip'=>$proxy_ip_address, 'xxx_hostname'=>$hostname, 'xxx_proxy_hostname'=>$proxy_hostname, 'xxx_ip'=>$_SERVER['REMOTE_ADDR']);
		}
		
	/**************************************************************************
	 onJavaScript()
	 Returns a JavaScript string responsible for extracting the necessary values
	 (if any) necessary for this plug-in.
	 
	 Should follow format of the new SI Object()
	 **************************************************************************
	function onJavaScript() {
		}
		*/
	
	/**************************************************************************
	 onDisplay()
	 Produces what the user sees when they are browsing their Mint install
	 
	 Returns an associative array of associative arrays that contain an HTML 
	 string for each display unit this plug-in is responsible for, plus a formal 
	 display name and the containing element's id (for ordering in preferences 
	 and anchor linking)
	 
	 **************************************************************************/
	function onDisplay($pane,$tab,$column='',$sort='') {
		$html = '';
		switch($pane) {
		/* XXX Strong Mint ****************************************************/
			case 'XXX Strong Mint': 
				switch($tab) {
				/* Most Recent*************************************************/
					case 'Most Recent':
						$html .= $this->getHTML_IPAddresses();
						break;
					case 'Repeat';
						$html .= $this->getHTML_Top10();
						break;
					case 'Search';
						$html .= $this->getHTML_Search();
						break;
				}
				break;
			}
		return $html;
		}
	
	/**************************************************************************
	 onWidget()
	 
	 **************************************************************************/
	function onWidget() { }
	
	/**************************************************************************
	 onDisplayPreferences()
	 
	 Should return an assoicative array (indexed by pane name) that contains the
	 HTML contents of that pane's preference. Preferences used by all panes in 
	 this plug-in should be indexed as 'global'. Any pane that isn't represeneted
	 by an index in the return array will simply display the string "This pane
	 does not have any preferences" (or similar).
	 
	 **************************************************************************/
	function onDisplayPreferences() {
		$prefs = $this->prefs;
		
		$high_checked = ($prefs['highlightProxies'])?' checked="checked"':'';
		$oTF_checked = ($prefs['onTheFly'])?' checked="checked"':'';
		$ipR_checked = ($prefs['showIPReferers'])?' checked="checked"':'';
		$hnOT_checked = ($prefs['hostnameOnTop'])?' checked="checked"':'';		
				
	$preferences['Global']	= "<table><tr><td><label><input type=\"checkbox\" name=\"highlightProxies\" value=\"1\"$high_checked /> Highlight visitors who are using a transparent proxy in red.</label></td></tr><tr><td><label><input type=\"checkbox\" name=\"showIPReferers\" value=\"1\"$ipR_checked /> Show where the user came from underneath the page they visited.</label></td></tr><tr><td><label><input type=\"checkbox\" name=\"onTheFly\" value=\"1\"$oTF_checked /> Do on-the-fly reverse DNS lookups for IP addresses without hostnames (This may adversely affect the speed at which Mint displays data, however it will not affect any visitors.)</label></td></tr><tr><td><label><input type=\"checkbox\" name=\"hostnameOnTop\" value=\"1\"$hnOT_checked /> Reverse the display of IP address and hostname.</label></td></tr></table>";
	
	return $preferences;
		
		}
	
	/**************************************************************************
	 onSavePreferences()
	 
	 **************************************************************************/
	function onSavePreferences() {
		$prefs['highlightProxies']	= (isset($_POST['highlightProxies']))?$_POST['highlightProxies']:0;
		$prefs['onTheFly']	= (isset($_POST['onTheFly']))?$_POST['onTheFly']:0;
		$prefs['showIPReferers']	= (isset($_POST['showIPReferers']))?$_POST['showIPReferers']:0;
		$prefs['hostnameOnTop']	= (isset($_POST['hostnameOnTop']))?$_POST['hostnameOnTop']:0;
		$this->prefs = $prefs;
//		$this->Mint->savePluginPreferences($this->plugin_id,$prefs);
		}
	
	/**************************************************************************
	 onCustom()
	 
	 **************************************************************************/
	function onCustom() {
		if (isset($_POST['action']) && $_POST['action']=='getvisits' && isset($_POST['ipaddress'])) {
			$ipaddress	= $this->Mint->escapeSQL($_POST['ipaddress']);
			echo $this->getHTML_Visits($ipaddress);
		} else {
			if (isset($_POST['action']) && $_POST['action'] == 'getsearchresults' && isset($_POST['ipaddress'])) {
				$ipaddress	= $this->Mint->escapeSQL($_POST['ipaddress']);
				$inc_hostnames	= $this->Mint->escapeSQL($_POST['inc_hostnames']);
				$start_search_point = 0;
				$sort_by = "";
				if ($this->Mint->escapeSQL($_POST['sort_by_1']) == "true") $sort_by = "dt";
				if ($this->Mint->escapeSQL($_POST['sort_by_2']) == "true") $sort_by = "total";
				if ($this->Mint->escapeSQL($_POST['sort_by_3']) == "true") $sort_by = "ip";
				if ($this->Mint->escapeSQL($_POST['sort_by_4']) == "true") $sort_by = "xxx_hostname";
				$sort_order = "";
				if ($this->Mint->escapeSQL($_POST['sort_order_1']) == "true") $sort_order = "DESC";
				if ($this->Mint->escapeSQL($_POST['sort_order_2']) == "true") $sort_order = "ASC";
				echo $this->getHTML_SearchResults($ipaddress, $inc_hostnames, $sort_by, $sort_order, $start_search_point);
			}
		}
	}
		
	/**************************************************************************
	 getHTML_Search()
	 
	 **************************************************************************/
	function getHTML_Search() {
		$html = '';
		
		$html .= '<div style="position: absolute; left: 0px; right: 0px; top: 0px; height: 18px; background-color: #BCE27F;"><div style="position: absolute; top: 2px; left: 11px;">Search</div></div>';
		
		$html .= "<form method=\"post\" onsubmit=\"tab = (getElementById('pane-" . $this->Mint->cfg['pepperShaker'][$this->pepperId]['panes'][0] . "').childNodes[3].childNodes[4])?(getElementById('pane-" . $this->Mint->cfg['pepperShaker'][$this->pepperId]['panes'][0] . "').childNodes[3].childNodes[4]):(getElementById('pane-" . $this->Mint->cfg['pepperShaker'][$this->pepperId]['panes'][0] . "').childNodes[1].childNodes[4]); tab.defaultHTML = 'Search'; tab.className = ''; tab.innerHTML = 'Searching&#8230;'; SI.Request.post('/mint/?MintPath=Custom&action=getsearchresults&ipaddress=' + document.xxx_search.xxx_searchip.value + '&sort_by_1=' + document.xxx_search.xxx_sort_by[0].checked + '&sort_by_2=' + document.xxx_search.xxx_sort_by[1].checked + '&sort_by_3=' + document.xxx_search.xxx_sort_by[2].checked + '&sort_by_4=' + document.xxx_search.xxx_sort_by[3].checked + '&inc_hostnames=' + document.xxx_search.xxx_inc_hostnames.checked + '&sort_order_1=' + document.xxx_search.xxx_sort_order[0].checked + '&sort_order_2=' + document.xxx_search.xxx_sort_order[1].checked, getElementById('pane-" . $this->Mint->cfg['pepperShaker'][$this->pepperId]['panes'][0] . "-content'), SI.Mint.onTabLoaded, tab); return false;\" action=\"' . $this->Mint->cfg['install_dir'] . '\" name=\"xxx_search\" id=\"xxx_search\"><div style=\"z-index: 99; height: 268px; background-color: #666666; color: #FFFFFF;\">";
		$html .= '<div style="position: absolute; top: 42px; left: 11px;">Search</div>';
		$html .= '<div style="position: absolute; top: 40px; left: 70px; right: 11px;"><span style="display: block; margin: 0; padding: 1px; border: 1px solid #5C5C5C; background-color: #FFF;"><input type="text" style="border: 1px solid #C3C3C3; border-top-color: #7C7C7C; border-bottom-color: #DDD; font-size: 10px; line-height: 12px; width: 100%;" id="xxx_searchip" name="xxx_searchip" value="" /></span></div>';
		$html .= '<div style="position: absolute; top: 64px; right: 11px">Wildcards allowed, eg. <code style="color: #92DAFF;">*.127.0.155</code> or <code style="color: #92DAFF;">*.192.*</code></div>';
		$html .= '<div style="background-color: #6E6E6E; border-top: 1px solid #616161; border-bottom: 1px solid #616161; position: absolute; top: 90px; left: 0px; right: 0px; height: 17px;"><div style="position: absolute; top: 1px; left: 11px;">Options</div></div>';
		$html .= '<div style="position: absolute; left: 10px; top: 120px;"><label><input type="radio" id="xxx_sort_by" name="xxx_sort_by" value="dt" checked="checked"> Sort by when</label> <label><input type="radio" id="xxx_sort_by" name="xxx_sort_by" value="visits"> Sort by visits</label></div>';
		$html .= '<div style="position: absolute; left: 10px; top: 140px;"><label><input type="radio" id="xxx_sort_by" name="xxx_sort_by" value="ip"> Sort by IP address</label> <label><input id="xxx_sort_by" type="radio" name="xxx_sort_by" value="hostname"> Sort by hostname</label></div>';
		$html .= '<div style="position: absolute; left: 10px; top: 180px;"><label><input type="checkbox" name="xxx_inc_hostnames" id="xxx_inc_hostnames"> Include hostnames</label></div>';
		$html .= '<div style="position: absolute; left: 10px; top: 200px;"><label><input type="radio" id="xxx_sort_order" name="xxx_sort_order" value="desc" checked="checked"> Descending</label> <label><input id="xxx_sort_order" type="radio" name="xxx_sort_order" value="asc"> Ascending</label></div>';
		$html .= '<div style="background-color: #7F7F7F; position: absolute; top: 230px; left: 0px; right: 0px; height: 1px;"></div>';
		$html .= "<a href=\"#\" onclick=\"tab = (getElementById('pane-" . $this->Mint->cfg['pepperShaker'][$this->pepperId]['panes'][0] . "').childNodes[3].childNodes[4])?(getElementById('pane-" . $this->Mint->cfg['pepperShaker'][$this->pepperId]['panes'][0] . "').childNodes[3].childNodes[4]):(getElementById('pane-" . $this->Mint->cfg['pepperShaker'][$this->pepperId]['panes'][0] . "').childNodes[1].childNodes[4]); tab.defaultHTML = 'Search'; tab.className = ''; tab.innerHTML = 'Searching&#8230;'; SI.Request.post('/mint/?MintPath=Custom&action=getsearchresults&ipaddress=' + document.xxx_search.xxx_searchip.value + '&sort_by_1=' + document.xxx_search.xxx_sort_by[0].checked + '&sort_by_2=' + document.xxx_search.xxx_sort_by[1].checked + '&sort_by_3=' + document.xxx_search.xxx_sort_by[2].checked + '&sort_by_4=' + document.xxx_search.xxx_sort_by[3].checked + '&inc_hostnames=' + document.xxx_search.xxx_inc_hostnames.checked + '&sort_order_1=' + document.xxx_search.xxx_sort_order[0].checked + '&sort_order_2=' + document.xxx_search.xxx_sort_order[1].checked, getElementById('pane-" . $this->Mint->cfg['pepperShaker'][$this->pepperId]['panes'][0] . "-content'), SI.Mint.onTabLoaded, tab); return false;\"><img style=\"position: absolute; top: 242px; right: 11px;\" src=\"" . $this->Mint->cfg['installDir'] . "/app/images/btn-continue.png\" /></a></div></form>";
		
		return $html;
		
	}
	
	/**************************************************************************
	 getHTML_SearchResults()
	 
	 **************************************************************************/
	function getHTML_SearchResults($search_query, $inc_hostnames, $sort_by, $sort_order, $start_search) {
	
		$start_search_point = $start_search;
		
		$html = '';
		
		$prefs = $this->prefs;
		
		$highProxies = $prefs['highlightProxies'];
		$onTheFly = $prefs['onTheFly'];
		$hnOTOpt = $prefs['hostnameOnTop'];
		
		$tableData['hasFolders'] = true;
		
		$tableData['table'] = array('id'=>'','class'=>'folder stacked-rows');
		
		if ($hnOTOpt == 1) {
			$header_text = "Hostname";
		} else {
			$header_text = "IP Address";
		}
		
		$tableData['thead'] = array(
			// display name, CSS class(es) for each column
			array('value'=>$header_text,'class'=>''),
			array('value'=>'Hits','class'=>''),
			array('value'=>'When','class'=>'')
			);
			
		$mint_row_count = $this->Mint->cfg['preferences']['rows'] + 1;
		
		$search_query = str_replace("*","%",$search_query);
		if ($search_query == "") { $search_query = "%"; }
		
		$incHostnamesTxt = "";

		if ($inc_hostnames == "true") {
			$incHostnamesTxt = " OR `xxx_hostname` LIKE '$search_query'";
		}
			
		$query = "CREATE TEMPORARY TABLE `{$this->Mint->db['tblPrefix']}xxx_search_temp` SELECT * FROM `{$this->Mint->db['tblPrefix']}visit` WHERE `xxx_ip` LIKE '" .$search_query . "' $incHostnamesTxt";
		
		$this->query($query);
		
		$query = "SELECT `ip_long`, COUNT(`ip_long`) AS `total`, MAX(`dt`) AS `dt`,  
				  MAX(`xxx_proxy_ip`) AS `xxx_proxy_ip`, MAX(`xxx_hostname`) AS `xxx_hostname`,
				  MAX(`xxx_proxy_hostname`) AS `xxx_proxy_hostname`
				  FROM `{$this->Mint->db['tblPrefix']}xxx_search_temp` 
				  GROUP BY `ip_long` 
				  ORDER BY `{$sort_by}` {$sort_order} 
				  LIMIT {$start_search_point}, {$mint_row_count}";
		
		//$query = "SELECT `ip`, `dt`, `proxy_ip`, `hostname`, `proxy_hostname`   
		//			FROM `{$this->Mint->db['tblprefix']}visit` 
		//			ORDER BY `dt` DESC 
		//			LIMIT 0,{$this->Mint->cfg['preferences']['rows']}";
		
		$fam = array();
		$total = 0;
		if ($result = $this->query($query)) {
			$ip_count = 0;
			while ($r = mysql_fetch_array($result)) {
				if ($r['ip_long'] != "") {
					if ($ip_count < $this->Mint->cfg['preferences']['rows']) {
						$ip_count = $ip_count + 1;
						$hostName = $r['xxx_hostname'];
						if ($onTheFly == 1) {
							if ($hostName == "") {
								$hostName = gethostbyaddr(LongtoIP($r['ip_long']));
							}
						}
						$proxyHostname = $r['xxx_proxy_hostname'];
						$ip_text = "";
						if ($hnOTOpt == 1) {
							if ($r['xxx_proxy_ip'] != "") {
								if ($highProxies == 1) {
									$ip_text .= "<span style=\"color: #FF0000; font-size: 1.0em;\">";
								}
								if ($r['xxx_proxy_ip'] != "unknown") {
									$ip_text .= "<abbr title=\"Proxied for " . $r['xxx_proxy_ip'] . " (" . $proxyHostname . ")\">";
								} else {
									$ip_text .= "<abbr title=\"Anonymous Proxy\">";
								}
							}
							if ($hostName != '' && $r['xxx_proxy_ip'] == "") {
								$ip_text = $ip_text . "<abbr title=\"" . $hostName . "\">" . $this->Mint->abbr($hostName, 30, true) . "</abbr><br />";
							} else {
								$ip_text = $ip_text . LongtoIP($r['ip_long']) . "<br />";
							}
							$ip_addr = LongtoIP($r['ip_long']);
							if ($r['xxx_proxy_ip'] != "") {
								$ip_text .= "</abbr>";
							}
	
							if ($hostName != '') {
								$ip_text .= "<span>" . $ip_addr . "</span>";
							}
						
						} else {
							$ip_addr = LongtoIP($r['ip_long']);
							if ($r['xxx_proxy_ip'] != "") {
								if ($highProxies == 1) {
									$ip_text .= "<span style=\"color: #FF0000; font-size: 1.0em;\">";
								}
								if ($r['xxx_proxy_ip'] != "unknown") {
									$ip_text .= "<abbr title=\"Proxied for " . $r['xxx_proxy_ip'] . " (" . $proxyHostname . ")\">";
								} else {
									$ip_text .= "<abbr title=\"Anonymous Proxy\">";
								}
							}
							$ip_text .= $ip_addr;
							if ($r['xxx_proxy_ip'] != "") {
								$ip_text .= "</abbr>";
								if ($highProxies == 1) {
									$ip_text .= "</span>";
								}
							}
							$ip_text = $ip_text . "<br /><span><abbr title=\"" . $hostName . "\">" . $this->Mint->abbr($hostName, 30, true) . "</abbr></span>";
						
						}
						$total = $total + 1;
						$tableData['tbody'][] = array(
						$ip_text, 
						$r['total'], $this->Mint->formatDateTimeRelative($r['dt']),
				
						'folderargs'=>array(
							'action'=>'getvisits',
							'ipaddress'=>$r['ip_long'],
							)
						);
					}	
				}
			}
		
		}
		
		$query = "DROP TEMPORARY TABLE `{$this->Mint->db['tblPrefix']}xxx_search_temp`";
		
		mysql_query($query);
		
		$html = $this->Mint->generateTable($tableData);
		$num_records = $start_search_point + $total;
		$start_search_point = $start_search_point + 1;
		if ($num_records != 0) {
			$html .= "<div style=\"padding-top: 5px; padding-bottom: 5px; padding-left: 10px;\">Displaying results " . $start_search_point . " to " . $num_records . ".</div>";
		} else {
			$html .= "<div style=\"padding-top: 5px; padding-bottom: 5px; padding-left: 10px;\">No results found.</div>";
		}
		return $html;
		}
	
	
	
	/**************************************************************************
	 getHTML_Top10()
	 
	 **************************************************************************/
	function getHTML_Top10() {
	
		$html = '';
		
		$prefs = $this->prefs;
		
		$highProxies = $prefs['highlightProxies'];
		$onTheFly = $prefs['onTheFly'];
		$hnOTOpt = $prefs['hostnameOnTop'];
		
		$tableData['hasFolders'] = true;
		
		$tableData['table'] = array('id'=>'','class'=>'folder stacked-rows');
		
		if ($hnOTOpt == 1) {
			$header_text = "Hostname";
		} else {
			$header_text = "IP Address";
		}
		
		$tableData['thead'] = array(
			// display name, CSS class(es) for each column
			array('value'=>$header_text,'class'=>''),
			array('value'=>'Hits','class'=>''),
			array('value'=>'When','class'=>'')
			);
			
		$mint_row_count = $this->Mint->cfg['preferences']['rows'] + 1;
			
		$query = "SELECT `ip_long`, COUNT(`ip_long`) AS `total`, MAX(`dt`) AS `dt`,  
				  MAX(`xxx_proxy_ip`) AS `xxx_proxy_ip`, MAX(`xxx_hostname`) AS `xxx_hostname`,
				  MAX(`xxx_proxy_hostname`) AS `xxx_proxy_hostname`
				  FROM `{$this->Mint->db['tblPrefix']}visit`
				  GROUP BY `ip_long` 
				  ORDER BY `total` DESC 
				  LIMIT 0, {$mint_row_count}";
		
		//$query = "SELECT `ip`, `dt`, `proxy_ip`, `hostname`, `proxy_hostname`   
		//			FROM `{$this->Mint->db['tblprefix']}visit` 
		//			ORDER BY `dt` DESC 
		//			LIMIT 0,{$this->Mint->cfg['preferences']['rows']}";
		
		$fam = array();
		$total = 0;
		if ($result = $this->query($query)) {
			$ip_count = 0;
			while ($r = mysql_fetch_array($result)) {
				if ($r['ip_long'] != "") {
					if ($ip_count < $this->Mint->cfg['preferences']['rows']) {
						$ip_count = $ip_count + 1;
						$hostName = $r['xxx_hostname'];
						if ($onTheFly == 1) {
							if ($hostName == "") {
								$hostName = gethostbyaddr(LongtoIP($r['ip_long']));
							}
						}
						$proxyHostname = $r['xxx_proxy_hostname'];
						$ip_text = "";
						if ($hnOTOpt == 1) {
							if ($r['xxx_proxy_ip'] != "") {
								if ($highProxies == 1) {
									$ip_text .= "<span style=\"color: #FF0000; font-size: 1.0em;\">";
								}
								if ($r['xxx_proxy_ip'] != "unknown") {
									$ip_text .= "<abbr title=\"Proxied for " . $r['xxx_proxy_ip'] . " (" . $proxyHostname . ")\">";
								} else {
									$ip_text .= "<abbr title=\"Anonymous Proxy\">";
								}
							}
							if ($hostName != '' && $r['xxx_proxy_ip'] == "") {
								$ip_text = $ip_text . "<abbr title=\"" . $hostName . "\">" . $this->Mint->abbr($hostName, 30, true) . "</abbr><br />";
							} else {
								$ip_text = $ip_text . LongtoIP($r['ip_long']) . "<br />";
							}
							$ip_addr = LongtoIP($r['ip_long']);
							if ($r['xxx_proxy_ip'] != "") {
								$ip_text .= "</abbr>";
							}
	
							if ($hostName != '') {
								$ip_text .= "<span>" . $ip_addr . "</span>";
							}
						
						} else {
							$ip_addr = LongtoIP($r['ip_long']);
							if ($r['xxx_proxy_ip'] != "") {
								if ($highProxies == 1) {
									$ip_text .= "<span style=\"color: #FF0000; font-size: 1.0em;\">";
								}
								if ($r['xxx_proxy_ip'] != "unknown") {
									$ip_text .= "<abbr title=\"Proxied for " . $r['xxx_proxy_ip'] . " (" . $proxyHostname . ")\">";
								} else {
									$ip_text .= "<abbr title=\"Anonymous Proxy\">";
								}
							}
							$ip_text .= $ip_addr;
							if ($r['xxx_proxy_ip'] != "") {
								$ip_text .= "</abbr>";
								if ($highProxies == 1) {
									$ip_text .= "</span>";
								}
							}
							$ip_text = $ip_text . "<br /><span><abbr title=\"" . $hostName . "\">" . $this->Mint->abbr($hostName, 30, true) . "</abbr></span>";
						
						}
						
						$tableData['tbody'][] = array(
						$ip_text, 
						$r['total'], $this->Mint->formatDateTimeRelative($r['dt']),
				
						'folderargs'=>array(
							'action'=>'getvisits',
							'ipaddress'=>$r['ip_long'],
							)
						);
					}	
				}
			}
		
		}
		
		$html = $this->Mint->generateTable($tableData);
		return $html;
		}
		
	/**************************************************************************
	 getHTML_IPAddresses()
	 
	 **************************************************************************/
	function getHTML_IPAddresses() {
	
		$html = '';
		
		$prefs = $this->prefs;
		
		$highProxies = $prefs['highlightProxies'];
		$onTheFly = $prefs['onTheFly'];
		$hnOTOpt = $prefs['hostnameOnTop'];
		
		$tableData['hasFolders'] = true;
		
		if ($hnOTOpt == 1) {
			$header_text = "Hostname";
		} else {
			$header_text = "IP Address";
		}
		
		$tableData['table'] = array('id'=>'','class'=>'folder stacked-rows');
		$tableData['thead'] = array(
			// display name, CSS class(es) for each column
			array('value'=>$header_text,'class'=>''),
			array('value'=>'Hits','class'=>''),
			array('value'=>'When','class'=>'')
			);
			
		$mint_row_count = $this->Mint->cfg['preferences']['rows'] + 1;
			
		$query = "SELECT `ip_long`, COUNT('ip_long') AS `total`, MAX(`dt`) AS `dt`, 
				  MAX(`xxx_proxy_ip`) AS `xxx_proxy_ip`, MAX(`xxx_hostname`) AS `xxx_hostname`,
				  MAX(`xxx_proxy_hostname`) AS `xxx_proxy_hostname`
				  FROM `{$this->Mint->db['tblPrefix']}visit`
				  GROUP BY `ip_long` 
				  ORDER BY `dt` DESC 
				  LIMIT 0, 19";
		
		//$query = "SELECT `ip`, `dt`, `proxy_ip`, `hostname`, `proxy_hostname`   
		//			FROM `{$this->Mint->db['tblprefix']}visit` 
		//			ORDER BY `dt` DESC 
		//			LIMIT 0,{$this->Mint->cfg['preferences']['rows']}";
		
		$fam = array();
		$total = 0;
		$ip_count = 0;
		if ($result = $this->query($query)) {
			while ($r = mysql_fetch_array($result)) {
				if ($r['ip_long'] != "") {
					if ($ip_count < $this->Mint->cfg['preferences']['rows']) {
							$ip_count = $ip_count + 1;
						$hostName = $r['xxx_hostname'];
						if ($onTheFly == 1) {
							if ($hostName == "") {
								$hostName = gethostbyaddr(LongtoIP($r['ip_long']));
							}
						}
						$proxyHostname = $r['xxx_proxy_hostname'];
						$ip_text = "";
						if ($hnOTOpt == 1) {
							if ($r['xxx_proxy_ip'] != "") {
								if ($highProxies == 1) {
									$ip_text .= "<span style=\"color: #FF0000; font-size: 1.0em;\">";
								}
								if ($r['xxx_proxy_ip'] != "unknown") {
									$ip_text .= "<abbr title=\"Proxied for " . $r['xxx_proxy_ip'] . " (" . $proxyHostname . ")\">";
								} else {
									$ip_text .= "<abbr title=\"Anonymous Proxy\">";
								}
							}						
							if ($hostName != '' && $r['xxx_proxy_ip'] == "") {
								$ip_text = $ip_text . "<abbr title=\"" . $hostName . "\">" . $this->Mint->abbr($hostName, 30, true) . "</abbr><br />";
							} else {
								$ip_text = $ip_text . LongtoIP($r['ip_long']) . "<br />";
							}
							$ip_addr = LongtoIP($r['ip_long']);
							if ($r['xxx_proxy_ip'] != "") {
								$ip_text .= "</abbr>";
							}
	
							if ($hostName != '') {
								$ip_text .= "<span>" . $ip_addr . "</span>";
							}
						
						} else {
							$ip_addr = LongtoIP($r['ip_long']);
							if ($r['xxx_proxy_ip'] != "") {
								if ($highProxies == 1) {
									$ip_text .= "<span style=\"color: #FF0000; font-size: 1.0em;\">";
								}
								if ($r['xxx_proxy_ip'] != "unknown") {
									$ip_text .= "<abbr title=\"Proxied for " . $r['xxx_proxy_ip'] . " (" . $proxyHostname . ")\">";
								} else {
									$ip_text .= "<abbr title=\"Anonymous Proxy\">";
								}
							}
							$ip_text .= $ip_addr;
							if ($r['xxx_proxy_ip'] != "") {
								$ip_text .= "</abbr>";
								if ($highProxies == 1) {
									$ip_text .= "</span>";
								}
							}
							$ip_text = $ip_text . "<br /><span><abbr title=\"" . $hostName . "\">" . $this->Mint->abbr($hostName, 30, true) . "</abbr></span>";
						
						}
						
						$tableData['tbody'][] = array(
						$ip_text, $r['total'],
						$this->Mint->formatDateTimeRelative($r['dt']),
				
						'folderargs'=>array(
							'action'=>'getvisits',
							'ipaddress'=>$r['ip_long']
							)
						);
					}
				}
			}
		}
		
		$html = $this->Mint->generateTable($tableData);
		return $html;
		}
	
	/**************************************************************************
	 getHTML_Visits()
	 
	 **************************************************************************/
		
	function getHTML_Visits($ipaddress) {
		$html = "";
		
		$prefs = $this->prefs;
		
		$ipR_show = $prefs['showIPReferers'];
		
		$query = "SELECT `ip_long`, `dt`, `resource`, `resource_title`, `referer`  
					FROM `{$this->Mint->db['tblPrefix']}visit` 
					WHERE
					`ip_long`='$ipaddress' 
					ORDER BY `dt` DESC";
		
		$v = array();
		if ($result = mysql_query($query)) {
			while ($r = mysql_fetch_array($result)) {
				$referer_text = '';
				if (($r['referer'] != '') && ($ipR_show == 1)) {
					$referer_text = "<br /><span>From <a href=\"" . $r['referer'] . "\">" . $this->Mint->abbr($r['referer'], 30) . "</a></span>";
				}
				$tableData['tbody'][] = array(
					"<a href=\"{$r['resource']}\">" . $this->Mint->abbr($r['resource_title'], 30) . "</a>" . $referer_text, "",
					$this->Mint->formatDateTimeRelative($r['dt'])
					);
				}
			}
		
		$html = $this->Mint->generateTableRows($tableData);
		return $html;
		}
	}
	
	function LongtoIP($Long) {
if ($Long == "") {
return 0;
} else {
$w = (int)(($Long/16777216) - 256*floor(($Long/16777216)/256));
$x = (int)(($Long/65536) - 256*floor(($Long/65536)/256));
$y = (int)(($Long/256) - 256*floor(($Long/256)/256));
$z = (int)(($Long) - 256*floor(($Long)/256));
return ($w.".".$x.".".$y.".".$z);

}
}

function IPtoLong($IPaddr)
{
if ($IPaddr == "") {
return 0;
} else {
$ips = split ("\.", "$IPaddr");
return ($ips[3] + $ips[2] * 256 + $ips[1] * 256 * 256 + $ips[0] * 256 * 256 * 256);
}
}
?>