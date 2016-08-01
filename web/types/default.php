<?php
/**
 * Usage:
 * File Name: default.php
 * Author: annhe  
 * Mail: i@annhe.net
 * Created Time: 2016-06-24 12:52:34
 **/

function typeDefault($iTopAPI, $type, $value) 
{
	$output = "contacts_list,applicationsolution_list";
	$query = "SELECT $type AS f WHERE f.name IN ('$value')";

	$data = $iTopAPI->coreGet("FunctionalCI", $query, $output);
	$result = $data['objects'];
	if($result){
		$contacts = array();
		$apps = array();
		foreach($result as $k=>$v){
			$contacts = array_merge($contacts, $v['fields']['contacts_list']);
			$apps = array_merge($apps, $v['fields']['applicationsolution_list']);
		}
	}
	$c_array = array();
	foreach($contacts as $k=>$v){
		array_push($c_array,$v['contact_id']);
	}

	$app_array = array();
	foreach($apps as $k=>$v){
		array_push($app_array,$v['applicationsolution_id']);
	}

	$c_str = implode("','", $c_array);

	if($app_array){
		$app_str = implode("','", $app_array);
		$query = "SELECT Person AS p JOIN lnkContactToApplicationSolution AS l ON l.contact_id=p.id " . 
			"WHERE l.applicationsolution_id IN ('$app_str') AND p.status='active' AND p.notify='yes' " .
			"UNION SELECT Person AS p WHERE p.id IN ('$c_str') AND p.status='active' AND p.notify='yes'";
	}else{
		$query = "SELECT Person AS p WHERE p.id IN ('$c_str') AND p.status='active' AND p.notify='yes'";
	}
	$output = "friendlyname, email, phone";
	$data = $iTopAPI->coreGet("Person", $query, $output);
	
	return($data);
}
