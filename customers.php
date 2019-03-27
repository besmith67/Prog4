<?php
/* ---------------------------------------------------------------------------
 * filename    : customers.php
 * author      : Branden Smith, besmith2@svsu.edu
 * description : Class that calls functions based on url input. Default shows list of customers
 * ---------------------------------------------------------------------------
 */
session_start();
if(!isset($_SESSION["custid"])){ // if "username" (email) is not set,
	session_destroy();			// end session
	header('Location: login.php');     // redirect to login page
	exit;
}

// include the class that handles database connections
require "../database/database.php";

// include the class containing functions/methods for "customers" table
require "customers.class.php";
$cust = new Customer();
 
// set active record field values, if any 
// (field values not set for display_list and display_create_form)
if(isset($_GET["id"]))          $id = $_GET["id"]; 
if(isset($_POST["name"]))       $cust->name = htmlspecialchars($_POST["name"]);
if(isset($_POST["email"]))      $cust->email = htmlspecialchars($_POST["email"]);
if(isset($_POST["mobile"]))     $cust->mobile = htmlspecialchars($_POST["mobile"]);
if(isset($_POST["password"]))     $cust->password = $_POST["password"];

// "fun" is short for "function" to be invoked 
if(isset($_GET["fun"])) $fun = $_GET["fun"];
else $fun = "display_list"; //default function if not specified

switch ($fun) {
    case "display_list":        $cust->list_records();
        break;
    case "display_create_form": $cust->create_record(); 
        break;
    case "display_read_form":   $cust->read_record($id); 
        break;
    case "display_update_form": $cust->update_record($id);
        break;
    case "display_delete_form": $cust->delete_record($id); 
        break;
    case "insert_db_record":    $cust->insert_db_record(); 
        break;
    case "update_db_record":    $cust->update_db_record($id);
        break;
    case "delete_db_record":    $cust->delete_db_record($id);
        break;
	case "show_images":    $cust->show_images();
        break;
    default: 
        echo "Error: Invalid function call (customer.php)";
        exit();
        break;
}

