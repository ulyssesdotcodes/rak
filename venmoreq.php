<?php

echo get_json("https://venmo.com/api/v2/user_find?client_id=1170&client_secret=m5DBawhJKgsTrmPTZ4FhEwFnsmj6FUwQ&foursquare_ids=".$_POST[fs_id]);

function get_json($url){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_URL, $url);
		$result = curl_exec($ch);
		curl_close($ch);
		
		return $result;
	}

?>