<?php
// include 
include_once('include/config.inc.php');

//print_r($SPACEWEB_CONFIG['fConcepts']);

// security layer
include_once('include/security.inc.php');


// be sure to load the right language
include_once('include/language/es.php');

//print_r($_SESSION);

// class
include_once 'class/Elo.php';
include_once 'class/Concept.php';
include_once 'class/Post.php';
include_once ('class/SpaceUtil.static.php');
include_once ('class/Manager.static.php'); // to be replaced by MongoDAO (class)
include_once ('class/MongoDAO.php');

?>