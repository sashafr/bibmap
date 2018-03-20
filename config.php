<?php
//PARAMETERS IN THE JAVASCRIPT THAT CAN BE EDITED BY THE USER

$mapID = "aa5a26d26cc24279b0d619fb62b84453"; //the map ID, which comes from the URL in AGOL
$polygonZoomLevel = 75000;  //this is the zoom level at which points switch to polygons
$layerIDList = array("ParksCentroids_shapefile_7933", "Parks21_shapefile_8161"); //this is the list of Layer ids
//that contain the points on the map.  You can get layer ids from the console.  The layer id will contain its name in AGOL but it will also contain additional numbers
$showFieldsInConsole = "true";
$extentPadding = 0.25;   //this is the percentage (as a decimal) of the visible layer's extent that will be added around the layer when it zooms,
//in order to ensure that the entire layer is visible.  Because extents round to set levels, changing this value incrementally won't
//slowly change the zoom, rather there is a thresold at which the zoom will change from one level to another
$leftMarginMultiplier = 5; //this number will multipled by the x margin to increase the padding on the left hand side of the map, to account for the fact that the sidebar
//covers the left portion of the map.


//these are the indices for various fields that are used to create the popups and citations.  If you set $showFieldsInConsole to true, the field names and indices will
//appear in the console.  Use these indices and don't try to guess the indices based on the attribute table in ArcGIS Online
$englishTitleField = 5;
$turkishTitleField = 5;
$otherTitleField = 5;
$publicationField = 5;
$languageField = 7;
$pageStartField = 11;
$pageEndField = 11;
$volumeField = 11;
$numberField = 11;
$uniqueIDField = 20;
$dateField = 11;


//these are the indices all the fields that contain author names
$authorIndices = array(16);
$firstNameIndices = array(16);
$lastNameIndices = array(16);

//these are the names of the fields for language and publication
$languageFieldName = "USE_";
$publicationFieldName = "ADDRESS";

$distances = array(5, 10, 20);
$distanceUnits = "kilometers"; //this is the value of the units for the distance-based functions.  it must be in English.  the options are
//feet, kilometers, meters, miles, nautical-miles, or yards.  How the word is displayed in other languages can be set
//in the language dictionaries under the variable $distanceUnitsWord

?>
