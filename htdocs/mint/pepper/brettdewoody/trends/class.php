<?php
/******************************************************************************
 Pepper
 
 Developer		: Brett DeWoody and Ronald Heft
 Plug-in Name	: Trends
 
 http://cavemonkey50.com/code/trends

 ******************************************************************************/

if (!defined('MINT')) { header('Location:/'); }; // Prevent viewing this file
$installPepper = "BD_Trends";

class BD_Trends extends Pepper {

	var $version = 306;

	var $info = array
	(
		'pepperName'	=> 'Trends', 
		'pepperUrl'		=> 'http://cavemonkey50.com/code/trends/',
		'pepperDesc'	=> 'Tracks trends across a specified period.',
		'developerName'	=> '<a href="http://www.brettdewoody.com">Brett DeWoody</a> and <a href="http://cavemonkey50.com/">Ronald Heft</a>',
		'developerUrl'	=> ''
	);

	var $panes = array
	(
		'Trends Internal' => array
		(
			'Popular',
			'Best',
			'Worst',
			'New',
			'Old',
			'Watched'
		),
		'Trends External' => array
		(
			'Quick View',
			'Referrers',
			'Searches'
		)
	);

	var $prefs = array
	(
		'compare_days' => 7,
		'compare_to' => 7,
		'compare_timeframe' => 1
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
	Panes and Prefs
	**************************************************************************/
	function onDisplay($pane, $tab, $column = '', $sort = '')
	{
		$html = '';
		switch($pane) {
		/* The Internal Window ***********************************************/
			case 'Trends Internal': 
				switch($tab) {
				/* The Panes *************************************************/
					case 'Popular':
						$html .= $this->getHTML_MostPopular();
						break;
					case 'Best':
						$html .= $this->getHTML_Best();
						break;
					case 'Worst':
						$html .= $this->getHTML_Worst();
						break;
					case 'New':
						$html .= $this->getHTML_New();
						break;
					case 'Old':
						$html .= $this->getHTML_Old();
						break;
					case 'Watched':
						$html .= $this->getHTML_Watched();
						break;
					}
				break;
		/* The External Window ***********************************************/
			case 'Trends External': 
				switch($tab) {
				/* The Panes *************************************************/
					case 'Quick View':
						$html .= $this->getHTML_QuickView();
						break;
					case 'Referrers':
						$html .= $this->getHTML_Referrers();
						break;
					case 'Searches':
						$html .= $this->getHTML_Searches();
						break;
					}
				break;
			}
		return $html;
	}


	/**************************************************************************
	Preferences
	**************************************************************************/
	function onDisplayPreferences()
	{
		/* Global *************************************************************/
		$preferences['Time Span To Compare'] = "
			<table class='snug'>
				<tr>
					<td>Compare The Last </td>
					<td><span class='inline'><input type='text' id='compare_days' size='3' name='compare_days' value='{$this->prefs['compare_days']}' class='cinch' /></span></td>
					<td> Days</td>
				</tr>
			</table>";
		$preferences['Time Span To Compare Against'] = "
			<table class='snug'>
				<tr>
					<td>Against The Previous </td>
					<td><span class='inline'><input type='text' id='compare_to' size='3' name='compare_to' value='{$this->prefs['compare_to']}' class='cinch' /></span></td>
					<td> Days Average</td>
				</tr>
			</table>";

		return $preferences;
	}
	
	
	/**************************************************************************
	onSavePreferences()
	**************************************************************************/
	function onSavePreferences()
	{
		$this->prefs['compare_days'] = $this->escapeSQL($_POST['compare_days']);
		if (($_POST['compare_to']) >= ($this->prefs['compare_days']))
			$this->prefs['compare_to'] = $this->escapeSQL($_POST['compare_to']);
		else
			$this->prefs['compare_to'] = $this->prefs['compare_days'];
		$this->prefs['compare_timeframe'] = ($this->prefs['compare_to'] / $this->prefs['compare_days']);
	}
	
	
	/**************************************************************************
	onCustom()
	**************************************************************************/
	function onCustom() 
	{
		if 
		(
			isset($_POST['action']) && 
			$_POST['action']=='getpagehistory' && 
			isset($_POST['page'])
		)
		{
			$page = $this->escapeSQL($_POST['page']);
			$column = $this->escapeSQL($_POST['column']);
			$diff = $this->escapeSQL($_POST['diff']);
			$filter = $this->escapeSQL($_POST['filter']);
			echo $this->get_PageHistory($page,$diff,$column,$filter);
		}
	}
	
	
	/*************************************************************************
	Work Horse - Page Comparing
	*************************************************************************/
	function getPageCompare($type, $filter)
	{
		$prefs = $this->prefs;
		$performance_data = array();

		if ($filter == 0)
			$timeStart1 = time() - ($this->prefs['compare_days'] * 24 * 60 * 60);
		else
			$timeStart1 = time() - ($filter * 60 * 60);
		$timeStop1 = time();

		$timeStart2 = $timeStart1 - ($this->prefs['compare_to'] * 24 * 60 * 60);
		$timeStop2 = $timeStart1;
		
		if ($type == "watched") {
			$watched = $this->Mint->data['0']['watched'];
			if (!empty($watched)) {
				$where = "WHERE (`dt` > {$timeStart1} AND `dt` <= {$timeStop1}) AND (`resource_checksum` = '".implode("' OR `resource_checksum` = '", $watched)."') ";
			} else { $where = "WHERE id=-2"; }
		} else {
			$where = "WHERE `dt` > {$timeStart1} AND `dt` <= {$timeStop1}";
		}

        $sql = "SELECT `resource`, `resource_checksum`, `resource_title`, COUNT(`resource_checksum`) as `total`, `dt`
				FROM `{$this->Mint->db['tblPrefix']}visit`
				$where
				GROUP BY `resource_checksum`
				ORDER BY `total` DESC, `dt` DESC";

		$query = $this->query($sql);

		/* Now find it in the previous time segment ***********************************/
		$i = 0;
		while (($daterange1 = mysql_fetch_array($query)) && ($i < $this->Mint->cfg['preferences']['rows']))
		{

			$sql_temp = "SELECT `resource_checksum`, COUNT(`resource_checksum`) as `total_temp`, `dt`
					FROM `{$this->Mint->db['tblPrefix']}visit`
					WHERE `dt` > {$timeStart2} AND `dt` <= {$timeStop2} AND `resource_checksum` = {$daterange1['resource_checksum']}
					GROUP BY `resource_checksum`
					ORDER BY `total_temp` DESC, `dt` DESC
					LIMIT 1";

			$query_temp = $this->query($sql_temp);

			if (mysql_num_rows($query_temp) == 0) {
				if ( ($type == "pop") || ($type == "watched") ) {
					/* Page is New! *******************************************************/
					$sql_new = "SELECT `resource_checksum`, COUNT(`resource_checksum`) as `total_temp`, `dt`
						FROM `{$this->Mint->db['tblPrefix']}visit`
						WHERE `dt` <= {$timeStart2} AND `resource_checksum` = {$daterange1['resource_checksum']}
						GROUP BY `resource_checksum`
						ORDER BY `total_temp` DESC, `dt` DESC
						LIMIT 1";
			
					$query_new = $this->query($sql_new);
				
					if (mysql_num_rows($query_new) == 0)
						$diff = "NEW";
					else
						$diff = "HITS";

					$performance_data[] = array($diff, 
						$daterange1["total"], 
						false,
						$daterange1["resource_title"],
						$daterange1["resource"],
						$daterange1["resource_checksum"]
						);
					
					if ($type != "watched") $i++;
				}
			} else {
				/* Time to analyze ****************************************************/
				$daterange2 = mysql_fetch_array($query_temp);

				$hits_1 = $daterange1["total"];
				if ($filter == 0)
					$hits_2 = ($daterange2["total_temp"]/$this->prefs['compare_timeframe']);
				else
					$hits_2 = ($daterange2["total_temp"]/($this->prefs['compare_to']/($filter/24)));
				$diff = (($hits_1/$hits_2)*100)-100;

				if ( ($type == "pop") || ( ($type == "best") && ($diff > 0) ) || ( ($type == "worst") && ($diff < 0) ) || ($type == "watched") ) {
					if ($type != "watched") $i++;
					$performance_data[] = array($diff, 
						$daterange1["total"],
						$daterange2["total_temp"],
						$daterange1["resource_title"],
						$daterange1["resource"],
						$daterange1["resource_checksum"]
						);
				}
			}
		}

		return $performance_data;
	}
	
	
	/***********************************************************************************
	Work Horse - Status
	***********************************************************************************/
	function get_Status($type, $filter)
	{
		$prefs = $this->prefs;
		$status_data = array();

		if ($filter == 0)
			$timeStart1 = time() - ($this->prefs['compare_days'] * 24 * 60 * 60);
		else
			$timeStart1 = time() - ($filter * 60 * 60);
		$timeStop1 = time();

		$timeStart2 = $timeStart1 - ($this->prefs['compare_to'] * 24 * 60 * 60);
		$timeStop2 = $timeStart1;
		
		if ($type == "new") {
        	$sql = "SELECT `resource`, `resource_checksum`, `resource_title`, COUNT(`resource_checksum`) as `total`, `dt`
				FROM `{$this->Mint->db['tblPrefix']}visit`
				WHERE `dt` > {$timeStart1} AND `dt` <= {$timeStop1}
				GROUP BY `resource_checksum`
				ORDER BY `total` DESC, `dt` DESC";
		} elseif ($type == "old") {
			$sql = "SELECT `resource`, `resource_checksum`, `resource_title`, COUNT(`resource_checksum`) as `total`, `dt`
				FROM `{$this->Mint->db['tblPrefix']}visit`
				WHERE `dt` <= {$timeStop2}
				GROUP BY `resource_checksum`
				ORDER BY `total` DESC, `dt` DESC";
		}

		$query = $this->query($sql);

		/* Now find it in the previous time segment ***********************************/
		$i = 0;
		while (($daterange1 = mysql_fetch_array($query)) && ($i < ($this->Mint->cfg['preferences']['rows']))) {

			if ($type == "new") {
				$sql_temp = "SELECT `resource_checksum`, `dt`
					FROM `{$this->Mint->db['tblPrefix']}visit`
					WHERE `resource_checksum` = {$daterange1['resource_checksum']} AND `dt` <= {$timeStop2}
					GROUP BY `resource_checksum`
					LIMIT 1";
			} elseif ($type == "old") {
				$sql_temp = "SELECT `resource_checksum`, `dt`
					FROM `{$this->Mint->db['tblPrefix']}visit`
					WHERE `resource_checksum` = {$daterange1['resource_checksum']} AND `dt` > {$timeStart1} AND `dt` <= {$timeStop1}
					GROUP BY `resource_checksum`
					LIMIT 1";
			}

			$query_temp = $this->query($sql_temp);
			
			$sql_date = "SELECT `resource_checksum`, `dt`
				FROM `{$this->Mint->db['tblPrefix']}visit`
				WHERE `resource_checksum` = {$daterange1['resource_checksum']}
				ORDER BY `dt` DESC
				LIMIT 1";

			$query_date = $this->query($sql_date);

			if (mysql_num_rows($query_temp) == 0) {
				if ($type == "old") {
					$date = mysql_fetch_array($query_date);
					$date = $date["dt"];
				} else
					$date = $daterange1["dt"];
				
				$i++;
				$status_data[] = array(
					$daterange1["total"],
					$daterange1["resource_title"],
					$daterange1["resource"],
					$date
				);
			}
		}

		return $status_data;
    }


	/***********************************************************************************
	Work Horse - Page History
	***********************************************************************************/
	function get_PageHistory($page, $diff, $column, $filter)
	{
		$html = '';
		$prefs = $this->prefs;

		if ($filter == 0)
			$compare_days = $this->prefs['compare_days'];
		else
			$compare_days = $filter/24;

		if ($compare_days > 1)
			$compare_days = round($compare_days);

		$hits = array();
		$dates = array();

		$time_hourstart = $this->Mint->getOffsetTime('hour');
		$time_daystart = $this->Mint->getOffsetTime('today');

		if ($diff != "NEW") {
			$page = mysql_real_escape_string($page);
			
			$lim_sql = "SELECT `dt`
					FROM `{$this->Mint->db['tblPrefix']}visit`
					WHERE `{$column}` = '{$page}'
					ORDER BY `dt` asc";
			$lim_query = $this->query($lim_sql);
			$lim = mysql_fetch_array($lim_query);
			
			$i = 0;
			do { 
				if ($compare_days >= 1) {
					if ($i == 0) {
						$timeStart1 = $time_daystart - (($compare_days-1) * 24 * 60 * 60);
						$timeStop1 = time();
					} else {
						$timeStart1 = $time_daystart - (($compare_days-1) * 24 * 60 * 60) - ($compare_days * 24 * 60 * 60 * $i);
						$timeStop1 = $time_daystart - (($compare_days-1) * 24 * 60 * 60) - (($compare_days * 24 * 60 * 60) * ($i-1));
					}
				} else {
					if ($i == 0) {
						$timeStart1 = $time_hourstart - (($compare_days * 24 - 1) * 60 * 60);
						$timeStop1 = time();
					} else {
						$timeStart1 = $time_hourstart - (($compare_days * 24 - 1) * 60 * 60) - (($compare_days * 24 * 60 * 60) * $i);
						$timeStop1 = $time_hourstart - (($compare_days * 24 - 1) * 60 * 60) - (($compare_days * 24 * 60 * 60) * ($i-1));
					}
				}

				$sql = "SELECT COUNT(`{$column}`) as `total`
					FROM `{$this->Mint->db['tblPrefix']}visit`
					WHERE `dt` > ({$timeStart1}) AND `dt` <= ({$timeStop1}) AND `{$column}` = '{$page}'
					GROUP BY `{$column}`";
				$query = $this->query($sql);
				$history = mysql_fetch_array($query);
				$page_history = $history["total"];
				
				if ($page_history == 0)
					$hits[] = 0;
				else
					$hits[] = $page_history;

				if ($compare_days == 1) {
					if ($i == 0) {
						$dates[] = '<strong>'.$this->Mint->offsetDate('n/d',$timeStart1).'</strong> - Today';
					} else {
						$dates[] = '<strong>'.$this->Mint->offsetDate('n/d',$timeStart1).'</strong> - '.$this->Mint->offsetDate('l',$timeStart1);
					}
				} elseif ($compare_days < 1) {
					if ($i == 0) {
						$dates[] = $this->Mint->offsetDate('h A',$timeStart1).' - Now';
					} else {
						$dates[] = $this->Mint->offsetDate('h A',$timeStart1).' - '.$this->Mint->offsetDate('h A',$timeStop1).'&nbsp;&nbsp;&nbsp;'.$this->Mint->offsetDate('l',$timeStop1); 
					}
				} else {
					if ($i ==0) {
						$dates[] = $this->Mint->offsetDate('m/d',$timeStart1).' - Now';
					} else {
						$dates[] = $this->Mint->offsetDate('m/d',$timeStart1).' - '.$this->Mint->offsetDate('m/d',$timeStop1); 
					}
				}
				
				$i++;
			} while ( $i <= 9 && ($lim["dt"] < $timeStart1) );

			$max_hits = max($hits);
			$j = $i;

			for($i = 0; $i < $j; $i++) {

				$bars = floor(37/$max_hits*$hits[$i]);

				$tableData['classes'] = array
				(
					'sort',
					'focus',
					'sort'
				);
				$tableData['tbody'][] = array
					(
						$hits[$i],
						$dates[$i],
						"<img align='left' src='pepper/brettdewoody/trends/bar.gif' width='".$bars."' height='15px' />"
					);
			}
			
		} else {
			if ($column == "resource_checksum")
				$type = "Page";
			elseif ($column == "referer_checksum")
				$type = "Referrer";
			elseif ($column == "search_terms")
				$type = "Search";
			$tableData['classes'] = array
				(
					'sort',
					'focus',
					'sort'
				);
			$tableData['tbody'][] = array
				(
					"",
					$type." is New; No History",
					""
				);
		}

		$html = $this->Mint->generateTableRows($tableData);
		return $html;
	}


	/***********************************************************************************
	Work Horse - Referrers
	***********************************************************************************/
	function getRefererData($view = 'active', $filter)
	{
		$prefs = $this->prefs;
		$referer_data = array();

		if ($filter == 0)
			$timeStart1 = time() - ($this->prefs['compare_days'] * 24 * 60 * 60);
		else
			$timeStart1 = time() - ($filter * 60 * 60);
		$timeStop1 = time();

		$timeStart2 = $timeStart1 - ($this->prefs['compare_to'] * 24 * 60 * 60);
		$timeStop2 = $timeStart1;

		$ignore_referrers = $this->Mint->cfg['preferences']['pepper']['0']['ignoreReferringDomains'];

		// Ignore certain domains
		$ignoredDomains	= preg_split('/[\s,]+/', $ignore_referrers);
		$ignoreQuery = '';
		if (!empty($ignoredDomains))
		{
			foreach ($ignoredDomains as $domain)
			{
				if (empty($domain))
				{
					continue;
				}
				$ignoreQuery .= ' AND `domain_checksum` != '.crc32($domain);
			}
		}

        $sql = "SELECT `resource`, `resource_title`, `referer`, `referer_checksum`, `referer_is_local`, `search_terms`, COUNT(`referer_checksum`) as `total`, `dt`
				FROM `{$this->Mint->db['tblPrefix']}visit`
				WHERE `dt` > {$timeStart1} AND `dt` <= {$timeStop1} AND `referer_is_local` = 0 AND `search_terms` = '' $ignoreQuery
				GROUP BY `referer_checksum`
				ORDER BY `total` DESC, `dt` DESC
				LIMIT 0,{$this->Mint->cfg['preferences']['rows']}";

		$query = $this->query($sql);

		/* Now find it in the previous time segment ***********************************/
		while ($daterange1 = mysql_fetch_array($query)) {

			$sql_temp = "SELECT `referer_checksum`, COUNT(`referer_checksum`) as `total_temp`, `dt`
					FROM `{$this->Mint->db['tblPrefix']}visit`
					WHERE `dt` > {$timeStart2} AND `dt` <= {$timeStop2} AND `referer_checksum` = {$daterange1['referer_checksum']}
					GROUP BY `referer_checksum`
					ORDER BY `total_temp` DESC, `dt` DESC
					LIMIT 1";

			$query_temp = $this->query($sql_temp);

			if (mysql_num_rows($query_temp) == 0) {
				/* Referrer is New! *******************************************************/
				$sql_new = "SELECT `referer_checksum`, COUNT(`referer_checksum`) as `total_temp`, `dt`
					FROM `{$this->Mint->db['tblPrefix']}visit`
					WHERE `dt` <= {$timeStart2} AND `referer_checksum` = {$daterange1['referer_checksum']}
					GROUP BY `referer_checksum`
					ORDER BY `total_temp` DESC, `dt` DESC
					LIMIT 1";
				$query_new = $this->query($sql_new);
				if (mysql_num_rows($query_new) == 0)
					$diff = "NEW";
				else
					$diff = "HITS";

				$referer_data[] = array($diff, 
					$daterange1["total"], 
					false,
					$daterange1["referer"],
					$daterange1["referer_checksum"],
					$daterange1["resource_title"],
					$daterange1["resource"]
					);
					
			} else {
				/* Time to analyze ****************************************************/
				$daterange2 = mysql_fetch_array($query_temp);

				$hits_1 = $daterange1["total"];
				$hits_2 = ($daterange2["total_temp"]/$this->prefs['compare_timeframe']);
				$diff = (($hits_1/$hits_2)*100)-100;

				$referer_data[] = array($diff, 
					$daterange1["total"],
					$daterange2["total_temp"],
					$daterange1["referer"],
					$daterange1["referer_checksum"],
					$daterange1["resource_title"],
					$daterange1["resource"]
					);
				}
		}

		return $referer_data;
	}


	/***********************************************************************************
	Work Horse - Searches
	***********************************************************************************/
	function getSearchData($view = 'active', $filter)
	{
		$prefs = $this->prefs;
		$search_data = array();

		if ($filter == 0)
			$timeStart1 = time() - ($this->prefs['compare_days'] * 24 * 60 * 60);
		else
			$timeStart1 = time() - ($filter * 60 * 60);
		$timeStop1 = time();

		$timeStart2 = $timeStart1 - ($this->prefs['compare_to'] * 24 * 60 * 60);
		$timeStop2 = $timeStart1;

        $sql = "SELECT `resource`, `resource_title`, `referer`, `search_terms`, COUNT(`search_terms`) as `total`, `dt`
				FROM `{$this->Mint->db['tblPrefix']}visit`
				WHERE `dt` > {$timeStart1} AND `dt` <= {$timeStop1} AND `search_terms` != ''
				GROUP BY `search_terms`
				ORDER BY `total` DESC, `dt` DESC
				LIMIT 0,{$this->Mint->cfg['preferences']['rows']}";

		$query = $this->query($sql); 

		/* Now find it in the previous time segment ***********************************/
		while ($daterange1 = mysql_fetch_array($query)) {

			$term = $daterange1["search_terms"];
			$term = mysql_real_escape_string($term); 
			$sql_temp = "SELECT `referer`, `search_terms`, COUNT(`search_terms`) as `total_temp`, `dt`
					FROM `{$this->Mint->db['tblPrefix']}visit`
					WHERE `dt` > {$timeStart2} AND `dt` <= {$timeStop2} AND `search_terms` = '{$term}'
					GROUP BY `search_terms`
					ORDER BY `total_temp` DESC, `dt` DESC
					LIMIT 1";

			$query_temp = $this->query($sql_temp);

			if (mysql_num_rows($query_temp) == 0) {
				/* Search is New! *******************************************************/
				$sql_new = "SELECT `referer`, `search_terms`, COUNT(`search_terms`) as `total_temp`, `dt`
					FROM `{$this->Mint->db['tblPrefix']}visit`
					WHERE `dt` <= {$timeStart2} AND `search_terms` = '{$term}'
					GROUP BY `search_terms`
					ORDER BY `total_temp` DESC, `dt` DESC
					LIMIT 1";
					
				$query_new = $this->query($sql_new);
				
				if (mysql_num_rows($query_new) == 0)
					$diff = "NEW";
				else
					$diff = "HITS";

				$search_data[] = array($diff, 
					$daterange1["total"], 
					false,
					$daterange1["search_terms"],
					$daterange1["referer"],
					$daterange1["resource_title"],
					$daterange1["resource"]
					);
					
			} else {
				/* Time to analyze ****************************************************/
				$daterange2 = mysql_fetch_array($query_temp);

				$hits_1 = $daterange1["total"];
				$hits_2 = ($daterange2["total_temp"]/$this->prefs['compare_timeframe']);
				$diff = (($hits_1/$hits_2)*100)-100;

				$search_data[] = array($diff, 
					$daterange1["total"],
					$daterange2["total_temp"],
					$daterange1["search_terms"],
					$daterange1["referer"],
					$daterange1["resource_title"],
					$daterange1["resource"]
					);
				}
		}

		return $search_data;
	}


	/***********************************************************************************
	Work Horse - Quick View
	***********************************************************************************/

	function get_QuickView($view = 'active', $filter)
	{
		$prefs = $this->prefs;
		$quick_data = array();

		if ($filter == 0)
			$timeStart1 = time() - ($this->prefs['compare_days'] * 24 * 60 * 60);
		else
			$timeStart1 = time() - ($filter * 60 * 60);
		$timeStop1 = time();

		/* Time Frame Direct */
        $time_direct_sql = "SELECT COUNT(`referer_is_local`) as `total`, `dt`
			FROM `{$this->Mint->db['tblPrefix']}visit`
			WHERE `dt` > {$timeStart1} AND `dt` <= {$timeStop1} AND `referer_is_local` = -1
			GROUP BY `referer_is_local`
			ORDER BY `total` DESC, `dt` DESC
			LIMIT 1";

		$time_direct_query = $this->query($time_direct_sql);
		$time_direct_fetch = mysql_fetch_array($time_direct_query);
		$time_direct = $time_direct_fetch["total"];

		/* Time Frame Referrers */
        $time_referrers_sql = "SELECT `search_terms`, COUNT(`referer_is_local`) as `total`, `dt`
			FROM `{$this->Mint->db['tblPrefix']}visit`
			WHERE `dt` > {$timeStart1} AND `dt` <= {$timeStop1} AND `referer_is_local` = 0 AND `search_terms` = ''
			GROUP BY `referer_is_local`
			ORDER BY `total` DESC, `dt` DESC
			LIMIT 1";

		$time_referrers_query = $this->query($time_referrers_sql);
		$time_referrers_fetch = mysql_fetch_array($time_referrers_query);
		$time_referrers = $time_referrers_fetch["total"];

		/* Time Frame Searches */
        $time_searches_sql = "SELECT `search_terms`, COUNT(`referer_is_local`) as `total`, `dt`
			FROM `{$this->Mint->db['tblPrefix']}visit`
			WHERE `dt` > {$timeStart1} AND `dt` <= {$timeStop1} AND `referer_is_local` = 0 AND `search_terms` != ''
			GROUP BY `referer_is_local`
			ORDER BY `total` DESC, `dt` DESC
			LIMIT 1";

		$time_searches_query = $this->query($time_searches_sql);
		$time_searches_fetch = mysql_fetch_array($time_searches_query);
		$time_searches = $time_searches_fetch["total"];

		/* It's Calculating Time */
		$time_total = $time_direct+$time_referrers+$time_searches;
		$time_direct_total = ($time_direct/$time_total)*100;
		$time_referrers_total = ($time_referrers/$time_total)*100;
		$time_searches_total = ($time_searches/$time_total)*100;

		/* Direct */
        $direct_sql = "SELECT COUNT(`referer_is_local`) as `total`, `dt`
			FROM `{$this->Mint->db['tblPrefix']}visit`
			WHERE `referer_is_local` = -1
			GROUP BY `referer_is_local`
			ORDER BY `total` DESC, `dt` DESC
			LIMIT 1";

		$direct_query = $this->query($direct_sql);
		$direct_fetch = mysql_fetch_array($direct_query);
		$direct = $direct_fetch["total"];

		/* Referrers */
        $referrers_sql = "SELECT `search_terms`, COUNT(`referer_is_local`) as `total`, `dt`
			FROM `{$this->Mint->db['tblPrefix']}visit`
			WHERE `referer_is_local` = 0 AND `search_terms` = ''
			GROUP BY `referer_is_local`
			ORDER BY `total` DESC, `dt` DESC
			LIMIT 1";

		$referrers_query = $this->query($referrers_sql);
		$referrers_fetch = mysql_fetch_array($referrers_query);
		$referrers = $referrers_fetch["total"];

		/* Searches */
        $searches_sql = "SELECT `search_terms`, COUNT(`referer_is_local`) as `total`, `dt`
			FROM `{$this->Mint->db['tblPrefix']}visit`
			WHERE `referer_is_local` = 0 AND `search_terms` != ''
			GROUP BY `referer_is_local`
			ORDER BY `total` DESC, `dt` DESC
			LIMIT 1";

		$searches_query = $this->query($searches_sql);
		$searches_fetch = mysql_fetch_array($searches_query);
		$searches = $searches_fetch["total"];

		/* It's Calculating Time */
		$total = $direct+$referrers+$searches;
		$direct_total = ($direct/$total)*100;
		$referrers_total = ($referrers/$total)*100;
		$searches_total = ($searches/$total)*100;

		$quick_data[] = array(
			$time_direct,
			$time_direct_total,
			$time_referrers,
			$time_referrers_total,
			$time_searches,
			$time_searches_total,
			$direct,
			$direct_total,
			$referrers,
			$referrers_total,
			$searches,
			$searches_total
			);

		return $quick_data;
    }


	/*****************************************************************************************************
	**************************** QUIT CALCULATING AND GET DOWN TO BUSINESS *******************************
	*****************************************************************************************************/	

	/**************************************************************************
	Page HTML Printing
	**************************************************************************/
	function getHTML_Print ($performance_data, $filter) {
		$html = '';
		$prefs = $this->prefs;
		
		if ($this->filter == 0)
			$day1 = $this->prefs['compare_days'];
		else
			$day1 = $this->filter / 24;
		
		if ($day1 == 1)
			$day1 = "24 Hours";
		elseif ($day1 < 1 && $day1 >= .042)
			$day1 = round(($day1*24),1)." Hours";
		elseif ($day1 < .042)
			$day1 = ceil(($day1*24*60))." Minutes";
		else
			$day1 = "$day1 Days";

		$day2 = $this->prefs['compare_to'];
		if ($day2 == 1)
			$day2 = "24 Hours";
		elseif ($day2 < 1 && $day2 >= .042)
			$day2 = round(($day2*24),1)." Hours";
		elseif ($day2 < .042)
			$day2 = ceil(($day2*24*60))." Minutes";
		else
			$day2 = "$day2 Days";
			
		$tableData['table'] = array('id'=>'','class'=>'folder');
		$tableData['thead'] = array(
			// display name, CSS class(es) for each column
			array('value'=>'Hits','class'=>'sort'),
			array('value'=>"Comparing $day1 to Previous $day2",'class'=>'focus'),
			array('value'=>'% +/-','class'=>'sort')
		);
		
		/* Display Results ****************************************************************/

		if (count($performance_data) == 0)
			$tableData['tbody'][] = array(
				"","There are no pages which meet this requirement.",""
			);
		
		foreach($performance_data as $performance) { 

			$diff = $performance[0];
			$hits_new = $performance[1];
			$hits_old = $performance[2];
			$title = $performance[3];
			$address = $performance[4];
			$page = $performance[5];

			$res_title = $this->Mint->abbr((!empty($title))?stripslashes($title):$address);
			$res_html = "<a href=\"$address\">$res_title</a>";

			$hits = $hits_new;

			if ($hits_old == false) {

				$tableData['hasFolders'] = true;
				$img = " ";
				if ($diff == "NEW")
					$diff_text = "<div style='text-align:left;'>New!</div>";
				else
					$diff_text = "<div style='text-align:left;'>No Hits</div>";

				$tableData['tbody'][] = array(
					$hits_new,
					$res_html,
					"$img $diff_text",
					'folderargs' => array
					(
						'action'=>'getpagehistory',
						'page'=>$page,
						'diff'=>$diff,
						'filter'=>$filter,
						'column'  =>"resource_checksum"
					)
				);

			} elseif ($diff < 0) {
				
				$tableData['hasFolders'] = true;
			   	$img = "<img src='pepper/brettdewoody/trends/down.gif' />";
				$diff = abs(round($diff,0))."%";

				$tableData['tbody'][] = array(
					$hits,
					$res_html,
					"<div style='text-align:left;'>$img $diff</div>",
					'folderargs' => array
					(
						'action'	=>'getpagehistory',
						'page'	=>$page,
						'filter'=>$filter,
						'column'  =>"resource_checksum"
					)
				);

			} elseif ($diff > 0) {
				
				$tableData['hasFolders'] = true;
			   	$img = "<img src='pepper/brettdewoody/trends/up.gif' />";
				$diff = round($diff,0)."%";

				$tableData['tbody'][] = array(
					$hits,
					$res_html,
					"<div style='text-align:left;'>$img $diff</div>",
					'folderargs' => array
					(
						'action'=>'getpagehistory',
						'page'=>$page,
						'filter'=>$filter,
						'column'  =>"resource_checksum"
					)
				);

			} else {
				
				$tableData['hasFolders'] = true;
			   	$img = "<img src='pepper/brettdewoody/trends/up.gif' />";
				$diff = round($diff,0)."%";

				$tableData['tbody'][] = array(
					$hits,
					$res_html,
					"<div style='text-align:left;'>$img $diff</div>",
					'folderargs' => array
					(
						'action'=>'getpagehistory',
						'page'=>$page,
						'filter'=>$filter,
						'column'  =>"resource_checksum"
					)
				);
			}

		}

		return $tableData;
	}
	
	
	/**************************************************************************
	Most Popular 
	**************************************************************************/
	function getHTML_MostPopular()
	{
		$html = '';
		
		$filters = array
		(
			'Default'	=> 0,
			'Past hour'	=> 1,
			'2h'		=> 2,
			'4h'		=> 4,
			'8h'		=> 8,
			'24h'		=> 24,
			'48h'		=> 48,
			'72h'		=> 72
		);
		
		$html .= $this->generateFilterList('Popular', $filters);
		
		$performance_data = $this->getPageCompare('pop', $this->filter);
		$tableData = $this->getHTML_Print($performance_data, $this->filter);
		
		$html .= $this->Mint->generateTable($tableData);
		
		return $html;
	}
	
	
	/**************************************************************************
	Best
	**************************************************************************/
	function getHTML_Best()
	{
		$filters = array
		(
			'Default'	=> 0,
			'Past hour'	=> 1,
			'2h'		=> 2,
			'4h'		=> 4,
			'8h'		=> 8,
			'24h'		=> 24,
			'48h'		=> 48,
			'72h'		=> 72
		);
		
		$html .= $this->generateFilterList('Best', $filters);
		
		$performance_data = $this->getPageCompare('best', $this->filter);
		$tableData = $this->getHTML_Print($performance_data, $this->filter);
		
		$html .= $this->Mint->generateTable($tableData);
		
		return $html;
	}
	
	
	/**************************************************************************
	Worst
	**************************************************************************/
	function getHTML_Worst()
	{
		$filters = array
		(
			'Default'	=> 0,
			'Past hour'	=> 1,
			'2h'		=> 2,
			'4h'		=> 4,
			'8h'		=> 8,
			'24h'		=> 24,
			'48h'		=> 48,
			'72h'		=> 72
		);
		
		$html .= $this->generateFilterList('Worst', $filters);
		
		$performance_data = $this->getPageCompare('worst', $this->filter);
		$tableData = $this->getHTML_Print($performance_data, $this->filter);
		
		$html .= $this->Mint->generateTable($tableData);
		
		return $html;
	}
	

	/**************************************************************************
	New
	**************************************************************************/
	function getHTML_New()
	{
		$html = '';
		$prefs = $this->prefs;
		
		$filters = array
		(
			'Default'	=> 0,
			'Past hour'	=> 1,
			'2h'		=> 2,
			'4h'		=> 4,
			'8h'		=> 8,
			'24h'		=> 24,
			'48h'		=> 48,
			'72h'		=> 72
		);
		
		$html .= $this->generateFilterList('New', $filters);
		
		$new_data = $this->get_Status('new', $this->filter); 

		if ($this->filter == 0)
			$day1 = $this->prefs['compare_days'];
		else
			$day1 = $this->filter / 24;
			
		if ($day1 == 1)
			$day1 = "24 Hours";
		elseif ($day1 < 1 && $day1 >= .042)
			$day1 = round(($day1*24),1)." Hours";
		elseif ($day1 < .042)
			$day1 = ceil(($day1*24*60))." Minutes";
		else
			$day1 = "$day1 Days";

		$day2 = $this->prefs['compare_to'];
		if ($day2 == 1)
			$day2 = "24 Hours";
		elseif ($day2 < 1 && $day2 >= .042)
			$day2 = round(($day2*24),1)." Hours";
		elseif ($day2 < .042)
			$day2 = ceil(($day2*24*60))." Minutes";
		else
			$day2 = "$day2 Days";


		$tableData['table'] = array('id'=>'','class'=>'');
		$tableData['thead'] = array(
			// display name, CSS class(es) for each column
			array('value'=>'New Hits','class'=>'sort'),
			array('value'=>"New Pages in the Past $day1",'class'=>'focus')
			);

		/* Display Results ****************************************************************/

		if (count($new_data) == 0) {
			
			$tableData['tbody'][] = array(
			"","There were no new pages viewed in the past ".strtolower($day1)."."
			);
			
		} else {
			
			foreach($new_data as $new) {

				$hits = $new[0];
				$title = $new[1];
				$address = $new[2];
				$date = $this->Mint->offsetDate('m/d/y - g:i a', $new[3]);
				$address_display = $this->Mint->abbr($address);

				$res_title = $this->Mint->abbr((!empty($title))?stripslashes($title):$address);
				$res_html = "<a href=\"$address\">$res_title</a>".(($this->Mint->cfg['preferences']['secondary'])?"<br /><span>First hit on $date</span>":'');


				$tableData['tbody'][] = array(
					$hits,
					$res_html
				);
			}
		}

		$html .= $this->Mint->generateTable($tableData);
		return $html;
	}


    /**************************************************************************
	Old
	**************************************************************************/
	function getHTML_Old()
	{
		$html = '';
		$prefs = $this->prefs;
		
		$filters = array
		(
			'Default'	=> 0,
			'Past day'	=> 24,
			'2d'		=> 48,
			'5d'		=> 120,
			'1w'		=> 168,
			'2w'		=> 336,
			'3w'		=> 504,
			'4w'		=> 672
		);
		
		$html .= $this->generateFilterList('Old', $filters);
		
		$old_data = $this->get_Status('old', $this->filter); 

		if ($this->filter == 0)
			$day1 = $this->prefs['compare_days'];
		else
			$day1 = $this->filter / 24;
			
		if ($day1 == 1)
			$day1 = "24 Hours";
		elseif ($day1 < 1 && $day1 >= .042)
			$day1 = round(($day1*24),1)." Hours";
		elseif ($day1 < .042)
			$day1 = ceil(($day1*24*60))." Minutes";
		elseif ($day1 < 13)
			$day1 = "$day1 Days";
		else
			$day1 = ceil($day1/7)." Weeks";

		$day2 = $this->prefs['compare_to'];
		if ($day2 == 1)
			$day2 = "24 Hours";
		elseif ($day2 < 1 && $day2 >= .042)
			$day2 = round(($day2*24),1)." Hours";
		elseif ($day2 < .042)
			$day2 = ceil(($day2*24*60))." Minutes";
		else
			$day2 = "$day2 Days";


		$tableData['table'] = array('id'=>'','class'=>'');
		$tableData['thead'] = array(
			// display name, CSS class(es) for each column
			array('value'=>'Old Hits','class'=>'sort'),
			array('value'=>"Old Pages in the Past $day1",'class'=>'focus')
			);

		/* Display Results ****************************************************************/

		if (count($old_data) == 0) {
			
			$tableData['tbody'][] = array(
			"","There were no unviewed pages in the past ".strtolower($day1)."!"
			);
			
		} else {
			
			foreach($old_data as $old) {

				$hits = $old[0];
				$title = $old[1];
				$address = $old[2];
				$date = $this->Mint->offsetDate('m/d/y - g:i a', $old[3]);
				$address_display = $this->Mint->abbr($address);

				$res_title = $this->Mint->abbr((!empty($title))?stripslashes($title):$address);
				$res_html = "<a href=\"$address\">$res_title</a>".(($this->Mint->cfg['preferences']['secondary'])?"<br /><span>Last hit on $date</span>":'');

				$tableData['tbody'][] = array
				(
					$hits,
					$res_html
				);
			}
		}

		$html .= $this->Mint->generateTable($tableData);
		return $html;
	}


	/**************************************************************************
	Watched
	**************************************************************************/
	function getHTML_Watched()
	{
		$filters = array
		(
			'Default'	=> 0,
			'Past hour'	=> 1,
			'2h'		=> 2,
			'4h'		=> 4,
			'8h'		=> 8,
			'24h'		=> 24,
			'48h'		=> 48,
			'72h'		=> 72
		);
		
		$html .= $this->generateFilterList('Watched', $filters);
		
		$performance_data = $this->getPageCompare('watched', $this->filter);
		$tableData = $this->getHTML_Print($performance_data, $this->filter);
		
		$html .= $this->Mint->generateTable($tableData);
		
		return $html;
	}


	/**************************************************************************
	Quick View
	**************************************************************************/
	function getHTML_QuickView()
	{
		$html = '';
		$prefs = $this->prefs;
		
		$filters = array
				(
					'Default'	=> 0,
					'Past hour'	=> 1,
					'2h'		=> 2,
					'4h'		=> 4,
					'8h'		=> 8,
					'24h'		=> 24,
					'48h'		=> 48,
					'72h'		=> 72
				);

				$html .= $this->generateFilterList('Quick View', $filters);
		
		$quick_data = $this->get_QuickView('active', $this->filter);

		if ($this->filter == 0)
			$day1 = $this->prefs['compare_days'];
		else
			$day1 = $this->filter / 24;
		
		if ($day1 == 1)
			$day1 = "24 Hours";
		elseif ($day1 < 1 && $day1 >= .042)
			$day1 = round(($day1*24),1)." Hours";
		elseif ($day1 < .042)
			$day1 = ceil(($day1*24*60))." Minutes";
		else
			$day1 = "$day1 Days";


		$tableData['table'] = array('id'=>'','class'=>'');
		$tableData['thead'] = array(
			// display name, CSS class(es) for each column
			array('value'=>"Site Visits",'class'=>'focus'),
			array('value'=>'Hits','class'=>'sort'),
			array('value'=>'Total','class'=>'sort')
			);

		/* Display Results ****************************************************************/

		foreach ($quick_data as $data) {
			$tableData['tbody'][] = array(
				"Direct (Past $day1)",
				$data[0],
				abs(round($data[1],0))."%"
				);
			$tableData['tbody'][] = array(
				"Referrers (Past $day1)",
				$data[2],
				abs(round($data[3],0))."%"
				);
			$tableData['tbody'][] = array(
				"Searches (Past $day1)",
				$data[4],
				abs(round($data[5],0))."%"
				);
			/* Separate Past and All Time */
			$tableData['tbody'][] = array(
				"<br />","",""
			);
			$tableData['tbody'][] = array(
				"Direct (All Time)",
				$data[6],
				abs(round($data[7],0))."%"
				);
			$tableData['tbody'][] = array(
				"Referrers (All Time)",
				$data[8],
				abs(round($data[9],0))."%"
				);
			$tableData['tbody'][] = array(
				"Searches (All Time)",
				$data[10],
				abs(round($data[11],0))."%"
				);
		}

		$html .= $this->Mint->generateTable($tableData);
		return $html;
	}

	
	/**************************************************************************
	External
	**************************************************************************/
	function getHTML_External($datas, $type, $filter)
	{
		$html = '';
		$prefs = $this->prefs;

		if ($this->filter == 0)
			$day1 = $this->prefs['compare_days'];
		else
			$day1 = $this->filter / 24;
		
		if ($day1 == 1)
			$day1 = "24 Hours";
		elseif ($day1 < 1 && $day1 >= .042)
			$day1 = round(($day1*24),1)." Hours";
		elseif ($day1 < .042)
			$day1 = ceil(($day1*24*60))." Minutes";
		else
			$day1 = "$day1 Days";

		$day2 = $this->prefs['compare_to'];
		if ($day2 == 1)
			$day2 = "24 Hours";
		elseif ($day2 < 1 && $day2 >= .042)
			$day2 = round(($day2*24),1)." Hours";
		elseif ($day2 < .042)
			$day2 = ceil(($day2*24*60))." Minutes";
		else
			$day2 = "$day2 Days";


		$tableData['table'] = array('id'=>'','class'=>'folder');
		$tableData['thead'] = array(
			// display name, CSS class(es) for each column
			array('value'=>'Hits','class'=>'sort'),
			array('value'=>"Comparing $day1 to Previous $day2",'class'=>'focus'),
			array('value'=>'% +/-','class'=>'sort')
			);

		/* Display Results ****************************************************************/

		foreach($datas as $data) { 

			$diff = $data[0];
			$hits_new = $data[1];
			$hits_old = $data[2];
			$address = $data[3];
			$checksum = $data[4];
			$title = $data[5];
			$title_address = $data[6];

			$res_title = $this->Mint->abbr($address);
			$res2_title = $this->Mint->abbr((!empty($title))?stripslashes($title):$title_address);
			if ($type == "ref") {
				$res_title = $this->Mint->abbr($address, 40);
				$res_html = "<a href=\"$address\">$res_title</a>".(($this->Mint->cfg['preferences']['secondary'])?"<br /><span>To <a href=\"$title_address\">$res2_title</a></span>":'');
				$column = "referer_checksum";
			} else {
				$res_title = $this->Mint->abbr($address);
				$res_html = "<a href=\"$checksum\">$res_title</a>".(($this->Mint->cfg['preferences']['secondary'])?"<br /><span>Found <a href=\"$title_address\">$res2_title</a></span>":'');
				$checksum = $address;
				$column = "search_terms";
			}

			$hits = $hits_new;

			if ($hits_old == false) {

				$img = " ";
				if ($diff == "NEW")
					$diff_text = "<div style='text-align:left;'>New!</div>";
				else
					$diff_text = "<div style='text-align:left;'>No Hits</div>";
				$tableData['hasFolders'] = true;

				$tableData['tbody'][] = array(
					$hits_new,
					$res_html,
					"$img $diff_text",
					'folderargs' => array
					(
						'action'	=>'getpagehistory',
						'page'	=>$checksum,
						'diff'=>$diff,
						'column'  =>$column
					)
				);

			} elseif ($diff < 0) {
				
			   	$img = "<img src='pepper/brettdewoody/trends/down.gif' />";
				$diff = abs(round($diff,0))."%";
				$tableData['hasFolders'] = true;

				$tableData['tbody'][] = array(
					$hits,
					$res_html,
					"<div style='text-align:left;'>$img $diff</div>",
					'folderargs' => array
					(
						'action'	=>'getpagehistory',
						'page'	=>$checksum,
						'filter'=>$filter,
						'column'  =>$column
					)
				);

			} elseif ($diff > 0) {
				
			   	$img = "<img src='pepper/brettdewoody/trends/up.gif' />";
				$diff = round($diff,0)."%";
				$tableData['hasFolders'] = true;

				$tableData['tbody'][] = array(
					$hits,
					$res_html,
					"<div style='text-align:left;'>$img $diff</div>",
					'folderargs' => array
					(
						'action'	=>'getpagehistory',
						'page'	=>$checksum,
						'filter'=>$filter,
						'column'  =>$column
					)
				);

			} else {
				
			   	$img = "<img src='pepper/brettdewoody/trends/up.gif' />";
				$diff = round($diff,0)."%";
				$tableData['hasFolders'] = true;

				$tableData['tbody'][] = array(
					$hits,
					$res_html,
					"<div style='text-align:left;'>$img $diff</div>",
					'folderargs' => array
					(
						'action'	=>'getpagehistory',
						'page'	=>$checksum,
						'filter'=>$filter,
						'column'  =>$column
					)
				);
			}
		}
		
		return $tableData;
	}
	

	/**************************************************************************
	Referrers
	**************************************************************************/
	function getHTML_Referrers()
	{
		$html = '';		
		$prefs = $this->prefs;
		
		$filters = array
		(
			'Default'	=> 0,
			'Past hour'	=> 1,
			'2h'		=> 2,
			'4h'		=> 4,
			'8h'		=> 8,
			'24h'		=> 24,
			'48h'		=> 48,
			'72h'		=> 72
		);
		
		$html .= $this->generateFilterList('Referrers', $filters);
		
		$referrer_data = $this->getRefererData('active', $this->filter);
		$tableData = $this->getHTML_External($referrer_data, 'ref', $this->filter);

		$html .= $this->Mint->generateTable($tableData);

		return $html;
	}
	
	
	/**************************************************************************
	Searches
	**************************************************************************/
	function getHTML_Searches()
	{
		$html = '';		
		$prefs = $this->prefs;
		
		$filters = array
		(
			'Default'	=> 0,
			'Past hour'	=> 1,
			'2h'		=> 2,
			'4h'		=> 4,
			'8h'		=> 8,
			'24h'		=> 24,
			'48h'		=> 48,
			'72h'		=> 72
		);
		
		$html .= $this->generateFilterList('Searches', $filters);
		
		$search_data = $this->getSearchData('active', $this->filter);
		$tableData = $this->getHTML_External($search_data, 'search', $this->filter);

		$html .= $this->Mint->generateTable($tableData);

		return $html;
	}

}