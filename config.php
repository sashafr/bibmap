<?php
//PARAMETERS IN THE JAVASCRIPT THAT CAN BE EDITED BY THE USER

$mapID = "b6af77794c9844b38bd52aec61f7db84"; //the map ID, which comes from the URL in AGOL
$polygonZoomLevel = 750000;  //this is the zoom level at which points switch to polygons
$layerIDList = array("TurkeySites1_shapefile_1005", "TurkeySites2_shapefile_3824"); //this is the list of Layer ids
//that contain the points on the map.  You can get layer ids from the console.  The layer id will contain its name in AGOL but it will also contain additional numbers


//these are the indices for various fields that are used to create the popups and citations
//note: the first field in the table as viewed in AGOL, is actually index 2.  Indexes 0 and 1, which do not appear in
//AGOL, are the FID and the shape field.  So whatever index it looks like it is in AGOL, add 2
$englishTitleField = 12;
$turkishTitleField = 11;
$otherTitleField = 13;
$publicationField = 3;
$languageField = 14;
$pageStartField = 6;
$pageEndField = 9;
$volumeField = 4;
$numberField = 5;
$uniqueIDField = 2;
$dateField= 0;



//these are the indices all the fields that contain author names
$authorIndices = array(21, 25, 29, 33, 37, 41, 45, 49, 53, 57);
$firstNameIndices = array(20, 24, 28, 32, 36, 40, 44, 48, 52, 56);
$lastNameIndices = array(19, 23, 27, 31, 35, 39, 43, 47, 51, 55);

//these are the names of the fields for language and publication
$languageFieldName = "astksts_12";
$publicationFieldName = "astkstsa_1";

?>
