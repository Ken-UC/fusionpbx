<?php
/*
	FusionPBX
	Version: MPL 1.1

	The contents of this file are subject to the Mozilla Public License Version
	1.1 (the "License"); you may not use this file except in compliance with
	the License. You may obtain a copy of the License at
	http://www.mozilla.org/MPL/

	Software distributed under the License is distributed on an "AS IS" basis,
	WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License
	for the specific language governing rights and limitations under the
	License.

	The Original Code is FusionPBX

	The Initial Developer of the Original Code is
	Mark J Crane <markjcrane@fusionpbx.com>
	Portions created by the Initial Developer are Copyright (C) 2008-2012
	the Initial Developer. All Rights Reserved.

	Contributor(s):
	Mark J Crane <markjcrane@fusionpbx.com>
*/
require_once "root.php";
require_once "resources/require.php";
require_once "resources/check_auth.php";
if (permission_exists('conference_delete')) {
	//access granted
}
else {
	echo "access denied";
	exit;
}

//add multi-lingual support
	$language = new text;
	$text = $language->get();

	//require the id
	if (is_uuid($_GET["id"])) {

		$conference_uuid = $_GET["id"];

		//get the dialplan uuid
			$sql = "select dialplan_uuid from v_conferences ";
			$sql .= "where domain_uuid = :domain_uuid ";
			$sql .= "and conference_uuid = :conference_uuid ";
			$parameters['domain_uuid'] = $_SESSION['domain_uuid'];
			$parameters['conference_uuid'] = $conference_uuid;
			$database = new database;
			$dialplan_uuid = $database->select($sql, $parameters, 'column');
			unset($sql, $parameters);

		//delete conference
			$array['conferences'][0]['conference_uuid'] = $conference_uuid;
			$array['conferences'][0]['domain_uuid'] = $_SESSION['domain_uuid'];
		//delete the dialplan details
			$array['dialplan_details'][0]['dialplan_uuid'] = $dialplan_uuid;
			$array['dialplan_details'][0]['domain_uuid'] = $_SESSION['domain_uuid'];
		//delete the dialplan entry
			$array['dialplans'][0]['dialplan_uuid'] = $dialplan_uuid;
			$array['dialplans'][0]['domain_uuid'] = $_SESSION['domain_uuid'];

		//execute
			$p = new permissions;
			$p->add('dialplan_detail_delete', 'temp');
			$p->add('dialplan_delete', 'temp');

			$database = new database;
			$database->app_name = 'conferences';
			$database->app_uuid = 'b81412e8-7253-91f4-e48e-42fc2c9a38d9';
			$database->delete($array);
			$response = $database->message;
			unset($array);

			$p->delete('dialplan_detail_delete', 'temp');
			$p->delete('dialplan_delete', 'temp');

		//syncrhonize configuration
			save_dialplan_xml();

		//apply settings reminder
			$_SESSION["reload_xml"] = true;

		//clear the cache
			$cache = new cache;
			$cache->delete("dialplan:".$_SESSION["context"]);

		//set message
			message::add($text['confirm-delete']);
	}

//redirect the browser
	header("Location: conferences.php");
	exit;

?>