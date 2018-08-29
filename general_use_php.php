<?php
//-------------------
//     general_use php
//-------------------

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$username = "username";
$password = "password";
$dsn = 'mysql:dbname=dbname;host=host;charset=UTF8';
try {
	$general_db_connection = new PDO($dsn, $username, $password);
	$general_db_connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e) {
    print $e->getMessage();
    die();
}

$current_total_infos = 10;

function general_insert($function_use_case, $insert_object) {
	global $general_db_connection;
	global $current_total_infos;
	if ($function_use_case == "component") {
	//component insert (single component being inserted)
		$number_of_infos = count($insert_object) - 2;
		$component_id = $insert_object[0];
		$component_type = $insert_object[1];
		for ($i=0; $i < $current_total_infos; $i++) {
			$k = $i + 1;
			$bind_val = "";
			if ($i < $number_of_infos) {
				$bind_val = $insert_object[$i+2];
			} else {
				$bind_val = "";
			}
			$component_info_string = "component_info_" . $k;
			$$component_info_string = $bind_val;
		}
		//statment & bind
		$insert_component_statment = $general_db_connection->prepare('INSERT INTO `general-use-components`(`id`, `type`, `info-1`, `info-2`, `info-3`, `info-4`, `info-5`, `info-6`, `info-7`, `info-8`, `info-9`, `info-10`) VALUES (:id, :type, :info_1, :info_2, :info_3, :info_4, :info_5, :info_6, :info_7, :info_8, :info_9, :info_10);');
		$insert_component_statment->bindParam(':id', $component_id, PDO::PARAM_INT);
		$insert_component_statment->bindParam(':type', $component_type, PDO::PARAM_STR);
		$insert_component_statment->bindParam(':info_1', $component_info_1, PDO::PARAM_STR);
		$insert_component_statment->bindParam(':info_2', $component_info_2, PDO::PARAM_STR);
		$insert_component_statment->bindParam(':info_3', $component_info_3, PDO::PARAM_STR);
		$insert_component_statment->bindParam(':info_4', $component_info_4, PDO::PARAM_STR);
		$insert_component_statment->bindParam(':info_5', $component_info_5, PDO::PARAM_STR);
		$insert_component_statment->bindParam(':info_6', $component_info_6, PDO::PARAM_STR);
		$insert_component_statment->bindParam(':info_7', $component_info_7, PDO::PARAM_STR);
		$insert_component_statment->bindParam(':info_8', $component_info_8, PDO::PARAM_STR);
		$insert_component_statment->bindParam(':info_9', $component_info_9, PDO::PARAM_STR);
		$insert_component_statment->bindParam(':info_10', $component_info_10, PDO::PARAM_STR);
		$insert_component_statment->execute();
	} elseif ($function_use_case == "link") {
	//link insert (single link being inserted)
	//(component-id-1, order, component-id-2)
		$parent_id = $insert_object[0];
		$be_order = $insert_object[1];
		$child_id = $insert_object[2];
		//statment & bind
		$insert_link_statment = $general_db_connection->prepare('INSERT INTO `ordered-links`(`parent-id`, `be-order`, `child-id`) VALUES (:parent_id, :be_order, :child_id);');
		$insert_link_statment->bindParam(':parent_id', $parent_id, PDO::PARAM_INT);
		$insert_link_statment->bindParam(':be_order', $be_order, PDO::PARAM_INT);
		$insert_link_statment->bindParam(':child_id', $child_id, PDO::PARAM_INT);
		$insert_link_statment->execute();
	}
}
function general_delete($function_use_case, $delete_object) {
	global $general_db_connection;
	//delete actual component, once called only deleted if not linked anywhere else
	if ($function_use_case == "component") {
		//just delete specific, check first
		//check if no links to it anymore, if the id is in component-id-1 / component-id-2 dont delete
		$return_array = array();
		$delete_component_id = $delete_object[0];
		//statment & bind
		$delete_component_check_statment_1 = $general_db_connection->prepare('SELECT * FROM `ordered-links` WHERE `parent-id` = :id;');
		$delete_component_check_statment_2 = $general_db_connection->prepare('SELECT * FROM `ordered-links` WHERE `child-id` = :id;');
		$delete_component_check_statment_1->bindParam(':id', $delete_component_id, PDO::PARAM_INT);
		$delete_component_check_statment_2->bindParam(':id', $delete_component_id, PDO::PARAM_INT);

		$delete_component_check_statment_1->execute();
		$delete_component_check_statment_2->execute();

		$results_1 = $delete_component_check_statment_1->fetchAll(PDO::FETCH_ASSOC);
		$results_2 = $delete_component_check_statment_2->fetchAll(PDO::FETCH_ASSOC);

		$delete_component_statment = $general_db_connection->prepare('DELETE FROM `general-use-components` WHERE `id` = :id;');
		$delete_component_statment->bindParam(':id', $delete_component_id, PDO::PARAM_INT);

		if (count($results_1) == 0){
			if (count($results_2) == 0){
				$delete_component_statment->execute();
				array_push($return_array, "1");
			}
		} else {
			array_push($return_array, "0");
		}
		return $return_array;
	} elseif ($function_use_case == "link") {
		//link unnescary (all link deleting done in updating object)
		return;
	}
}

function general_return($function_use_case, $return_object) {
	global $general_db_connection;
	if ($function_use_case == "component") {
		$return_id = $return_object[0];
		$return_array = array();
		$temp_return_array = array();

		$return_component_info_statment = $general_db_connection->prepare('SELECT * FROM `general-use-components` WHERE `id` = :id;');
		$return_component_info_statment->bindParam(':id', $return_id, PDO::PARAM_INT);
		$return_component_info_statment->execute();
		$component_info = $return_component_info_statment->fetchAll(PDO::FETCH_ASSOC);

		$return_component_links_statment = $general_db_connection->prepare('SELECT * FROM `ordered-links` WHERE `parent-id` = :id;');
		$return_component_links_statment->bindParam(':id', $return_id, PDO::PARAM_INT);
		$return_component_links_statment->execute();
		$component_links_info = $return_component_links_statment->fetchAll(PDO::FETCH_ASSOC);

		if (count($component_links_info) != 0) {
			foreach ($component_links_info as $link) {
				array_push($temp_return_array, array($link["be-order"], $link["child-id"]));
			}
		}
		$number_of_childeren = count($temp_return_array);
		for ($x=0; $x<$number_of_childeren; $x++) {
			$y = $x + 1;
			while ($temp_return_array[$x][0] != $y) {
				$temp = $temp_return_array[$x];
				array_splice($temp_return_array, $x, 1);
				array_push($temp_return_array, $temp);
			}
			$temp_return_array[$x] = general_return("component", array($temp_return_array[$x][1]));
		}
		if (count($component_info) != 0) {
			array_push($return_array, $component_info[0]["id"], $component_info[0]["type"]);
			$number_of_infos = "";
			//-----------------------------------------------------------------------
			//below be all the different classes of components
			if ($component_info[0]["type"] == "component-type") {
				$number_of_infos = 1;
			}
			if ($component_info[0]["type"] == "singular-value") {
				$number_of_infos = 1;
			}
			if ($component_info[0]["type"] == "keypair") {
				$number_of_infos = 2;
			}
			if ($component_info[0]["type"] == "text") {
				$number_of_infos = 1;
			}
			//-----------------------------------------------------------------------
			for ($i=0; $i < $number_of_infos; $i++) {
				$k = $i + 1;
				$info_str = "info-" . $k;
				array_push($return_array, $component_info[0][$info_str]);
			}
			array_push($return_array, $temp_return_array);
			return $return_array;
		}
	}
}

function general_update($function_use_case, $old_object, $new_object) {
	global $general_db_connection;
	global $current_total_infos;
	//update old object to new -> [ update specific data 1->x, + all the links to it if there are any (checks included for maintaining clean db)]
	if ($function_use_case == "component") {
		//variables
		$number_of_infos = count($old_object) - 3;

		$old_component_id = $old_object[0];
		$old_component_type = $old_object[1];
		for ($i=0; $i < $number_of_infos; $i++) {
			$k = $i + 1;
			$old_component_info_string = "old_component_info_" . $k;
			$$old_component_info_string = $old_object[$i+2];
		}

		$new_component_id = $new_object[0];
		$new_component_type = $new_object[1];
		for ($i=0; $i < $number_of_infos; $i++) {
			$k = $i + 1;
			$new_component_info_string = "new_component_info_" . $k;
			$$new_component_info_string = $new_object[$i+2];
		}
//check if infos are different
		//update specific data
		//type
		if ($new_component_type != $old_component_type) {
			$update_component_type_statment = $general_db_connection->prepare('UPDATE `components` SET `type` = :new_componenet_type WHERE `id` = :id;');
			$update_component_type_statment->bindParam(':new_componenet_type', $new_componenet_type, PDO::PARAM_STR);
			$update_component_type_statment->bindParam(':id', $old_component_id, PDO::PARAM_INT);
			$update_component_type_statment->execute();
		}
		//infos
		for ($i=0; $i < $number_of_infos; $i++) {
			$k = $i + 1;
			$old_component_info_string = "old_component_info_" . $k;
			$new_component_info_string = "new_component_info_" . $k;
			//-->
			if ($$new_component_info_string != $$old_component_info_string) {
				$general_update_prepare_statment = "UPDATE `general-use-components` SET `info-" . $k . "` = :component_info WHERE `id` = :id;";
				$update_info_variabled_name = "update_extra_info_" . $k;
				$$update_info_variabled_name = $general_db_connection->prepare($general_update_prepare_statment);
				$$update_info_variabled_name->bindParam(':component_info', $$new_component_info_string, PDO::PARAM_STR);
				$$update_info_variabled_name->bindParam(':id', $old_component_id, PDO::PARAM_INT);
				$$update_info_variabled_name->execute();
			}
		}
		$entry_ids = array();
		//update links for page list
		// will always be passed in asscending order
		//  check for removed list-entries > then > check if they are linked atall, if not delete their actual db place
		//    if in old but not in new = removed
		$old_component_links = $old_object[$number_of_infos + 2];
		$new_component_links = $new_object[$number_of_infos + 2];
		if (count($old_component_links) > 0) {
			foreach ($old_component_links as $old_links) {
				array_push($entry_ids, $old_links[0]);
			}
		}
		if (count($new_component_links) > 0) {
			foreach ($new_component_links as $new_links) {
				$itr = 0;
				$check = "no";
				if (count($entry_ids > 0)) {
					foreach ($entry_ids as $old_id) {
						if ($new_component_links[0] == $old_id) {
							$to_delete = $itr;
							$check = "yea";
						}
						$itr = $itr + 1;
					}
					if ($check == "yea"){
						array_splice($entry_ids, $to_delete, 1);
					}
				}
			}
		}
		//now have a array of component ids to delete check after update of links

		//drop all links to the component-id
		$delete_old_component_links_statment = $general_db_connection->prepare('DELETE FROM `ordered-links` WHERE `parent-id` = :id;');
		$delete_old_component_links_statment->bindParam(':id', $old_component_id, PDO::PARAM_INT);
		$delete_old_component_links_statment->execute();

		//insert all new links to component-id
		if (count($new_component_links) > 0) {
			for ($i=0; $i < count($new_component_links); $i++) {
				$the_be_order = $i + 1;
				general_insert("link", array($old_component_id, $the_be_order, $new_component_links[$i][0]));
			}
		}
		if (count($entry_ids) > 0){
			foreach ($entry_ids as $delete_check_id) {
				general_delete("component", array($delete_check_id));
			}
		}
	}
}
function specific_general_returns($function_use_case, $specific_object) {
	global $general_db_connection;
	$return_array = array();
	if ($function_use_case == "all of type type") {
		//object 0 is function use case
		$type_type = $specific_object[1];
		$return_component_type_info_temp_statment = $general_db_connection->prepare('SELECT * FROM `general-use-components` WHERE `type` = :type_type;');
		$return_component_type_info_temp_statment->bindParam(':type_type', $type_type, PDO::PARAM_STR);
		$return_component_type_info_temp_statment->execute();
		$component_type_info_temp = $return_component_type_info_temp_statment->fetchAll(PDO::FETCH_ASSOC);
		foreach ($component_type_info_temp as $specific_type_info) {
			array_push($return_array, general_return("component", array($specific_type_info["id"])));
		};
	return $return_array;
	}
}
//--------------------->>>

if (!empty($_POST)){
	$function_type = $_POST['ajax_use_case'];
	if ($function_type == "insert_component") {
		general_insert("component", $_POST['insert_object']);
	} else if($function_type == "insert_link") {
		general_insert("link", $_POST['insert_object']);
	} else if($function_type == "return") {
		echo json_encode(general_return("component", array($_POST['component_id'])));
	} else if($function_type == "delete_component") {
		echo json_encode(general_delete("component", $_POST['delete_object']));
	} else if($function_type == "update") {
		general_update("component", json_decode($_POST['old_update_object']), json_decode($_POST['new_update_object']));
	} else if($function_type == "specific") {
		$function_use_case = $_POST['specific_object'][0];
		echo json_encode(specific_general_returns($function_use_case, $_POST['specific_object']));
	};
};

//------------------
// Tests
//------------------

$insert_test = false;
$update_test = false;
$delete_test = false;
$return_test = false;
$specific_return_test = false;

if ($insert_test == true) {
	//example component
	general_insert("component", array(9, "keypair", "next useable id", "10"));

}
if ($update_test == true) {
	//change
	general_update("component", array("108", "keypair", "aaaa", "bbbb", array()),  array("108", "keypair", "cccc", "dddd", array()));
}

if ($delete_test == true) {
	general_delete("component", array(0));
}

if ($return_test == true) {
	echo json_encode((general_return("component", array(1006))));
}
if ($specific_return_test == true) {
	echo json_encode(specific_general_returns("all of type type", array(1, "component-type")));
}
?>
