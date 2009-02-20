<?php
/******************************************************************************
 Leaves

 Developer		: Shaun Inman
 Plug-in Name	: Leaves
 
 http://www.shauninman.com/

 ------------------------------------------------------------------------------
 LIB
 ******************************************************************************/

if (!defined('REQUEST_UA')) 		{ define('REQUEST_UA', 'SI_Request'); }
if (!defined('REQUEST_TIMEOUT'))	{ define('REQUEST_TIMEOUT', 10); }

/*-----------------------------------------------------------------------------
 p()										 A convenience function for print_r
 ******************************************************************************/
function p($obj = null, $title = '')
{
	echo '<pre>';
	if (!empty($title)) { echo "{$title}: "; }
	print_r($obj);
	echo '</pre>'."\n";
}

/******************************************************************************
 array_to_query()
 
 Converts an array into the equivalent query string, accounting for nested arrays
 ******************************************************************************/
function array_to_query($array = array(), $nested = array())
{
	$tmpArray = array();
	foreach ($array as $key => $value)
	{
		if (is_array($value))
		{
			$nested[] = $key;
			$tmpArray[] = array_to_query($value, $nested);
			array_pop($nested);
		}
		else
		{
			if (!empty($nested))
			{
				$keyName = $nested[0];
				if (count($nested) > 1)
				{
					$keyName .= '['.implode('][', array_slice($nested, 1)).']';
				}
				$keyName .= '['.$key.']';
			}
			else
			{
				$keyName = $key;
			}
			
			$tmpArray[] = "{$keyName}={$value}";
		}
	}
	$array = implode('&', $tmpArray);
	return $array;
}

/*-----------------------------------------------------------------------------
 get()									  Get the contents of the requested url
 ******************************************************************************/
function get($url = '', $headers = array())
{
	return request('GET', $url, '', $headers);
}

/*-----------------------------------------------------------------------------
 post()							Get the contents of the requested url with post
 ******************************************************************************/
function post($url = '', $post = '', $headers = array())
{
	return request('POST', $url, $post, $headers);
}

/******************************************************************************
 request()
 
 Used by get and post functions. Branches based on availability of cURL. $method
 is either GET or POST, $post may be an associative array or a query string, 
 $headers should be an array of strings in the following format, Header: value
 ******************************************************************************/
function request($method = '', $url = '', $post = '', $headers = array())
{
	$use_curl		= in_array('curl', get_loaded_extensions());
	$response 		= '';
	$response_obj	= array();
	$time_out		= REQUEST_TIMEOUT;
	$error			= array
	(
		'no' 		=> 0,
		'msg'		=> ''
	);
	$headers[] = 'User-Agent: '.REQUEST_UA;
	
	// Parse url for parts
	$parsed_url		= array
	(
		'scheme'	=> 'http',
		'host'		=> 'localhost',
		'path'		=> '/',
		'query'		=> '',
		'port'		=> 80	
	);
	$parsed_url		= array_merge($parsed_url, parse_url($url));
	$break			= str_repeat("\r\n", 2);
	
	// Determine how to handle provided post data
	if (is_array($post) && count($post))
	{
		$post = array_to_query($post);
	}
	
	$path_with_query = (!empty($parsed_url['query'])) ? "{$parsed_url['path']}?{$parsed_url['query']}" : $parsed_url['path'];
	
	// cURL branch
	if ($use_curl)
	{
		$request = curl_init
		(
			"{$parsed_url['scheme']}://{$parsed_url['host']}:{$parsed_url['port']}{$path_with_query}"
		);
		
		curl_setopt($request, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($request, CURLOPT_CONNECTTIMEOUT, $time_out);
		curl_setopt($request, CURLOPT_HEADER, 1);
		curl_setopt($request, CURLOPT_RETURNTRANSFER, 1);
		
		if ($method == 'POST')
		{
			curl_setopt($request, CURLOPT_POST, true);
			curl_setopt($request, CURLOPT_POSTFIELDS, $post);
		}
		
		$response = curl_exec($request);
		
		if ($error['no'] = curl_errno($request))
		{
			$error['msg'] = curl_error($request);
			// e($error['msg'].' ('.$error['no'].')');
		}
		curl_close($request);
	}
	
	// Socket branch
	else
	{
		$base_headers = array
		(
			"{$method} {$path_with_query} HTTP/1.0",
			"Host: {$parsed_url['host']}",
		);
		
		if ($method == 'POST')
		{
			$base_headers[] = 'Content-type: application/x-www-form-urlencoded';
			$base_headers[] = 'Content-length: '.strlen($post);
		}
		
		$headers = array_merge($base_headers, $headers);
		$request = @fsockopen
		(
			$parsed_url['host'],
			$parsed_url['port'],
			$error['no'],
			$error['msg'],
			$time_out
		);
		
		if ($request)
		{
			$headers	= implode("\r\n", $headers).$break.$post;
			
			fwrite($request, $headers);
			stream_set_timeout($request, $time_out);
			while (!feof($request))
			{
				$response .= fgets($request, 1024);
			}
		}
		else
		{
			// e($error['msg'].' ('.$error['no'].')');
		}
	}
	
	$response		= explode($break, 'Status: '.$response, 2);
	$response[0]	= explode("\r\n", $response[0]);
	
	$response_headers = array();
	$response_headers['response_code'] = ''; // originally defaulted to 404
	foreach($response[0] as $header)
	{
		list($key, $value) = explode(': ', $header, 2);
		$response_headers[$key] = $value;
		if ($key == 'Status')
		{
			$status_array = explode(' ', $value, 3);
			if (isset($status_array[1]))
			{
				$response_headers['response_code'] = $status_array[1];
			}
		}
	}
	
	$response_obj['headers']	= $response_headers;
	$response_obj['body']		= (isset($response[1])) ? $response[1] : '';
	
	// handle redirects (needto revisit)
	if (isset($response_obj['headers']['Location']))
	{
		$location = $response_obj['headers']['Location'];
		if (preg_match('#^/#', $location, $m))
		{
			$location = $parsed_url['scheme'].'://'.$parsed_url['host'].$response_obj['headers']['Location'];
		}
		$response_obj = request($method, $location, $post, $headers);
	}
	
	return $response_obj;
}