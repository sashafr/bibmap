<?php
//PARAMETERS IN THE JAVASCRIPT THAT CAN BE EDITED BY THE USER

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

//this is the name of the date field
$dateFieldName = " ";

//these are the indices all the fields that contain author names
$authorIndices = array(21, 25, 29, 33, 37, 41, 45, 49, 53, 57);
$firstNameIndices = array(24, 28, 32, 36, 40, 44, 48, 52, 56);
$lastNameIndices = array(19, 23, 27, 31, 35, 39, 43, 47, 51, 55, 59);

//these are the names of the fields for language and publication
$languageFieldName = "astksts_12";
$publicationFieldName = "astkstsa_1";

//text values in the HTML (from top to bottom)
$test = 'hello world';
$pageTitle = 'Turkish Archaeology Geographical Bibliography';
$translatedPage = 'http://104.131.176.181/bibmap/map.php?lang=tr'; //the page that the "Show page in" button directs to
$about = 'About';
$abouttext = 'This is a map';
$viewPageIn = 'View page in Turkish';
$enterSearchTerm = 'Enter search term';
$search = 'Search';
$clear = 'Clear';
$hide = 'Hide';
$showAll = 'Show All';
$clearSelection = 'Clear selection';
$selectDistance = 'Select distance';
$kilometers = 'kilometers';
$select = 'Select';
$by = 'by';
$circle = 'circle';
$polygon = 'polygon';
$geometrySearch = 'Geometry search';
$introduction = 'Introduction';
$introText = 'This site contains a collection of journal articles related to Turkish archaeology.  You can search by language, publication or author.
You can also search by drawing a line between two points on the map, by drawing a polygon on the map, or by searching an address and selecting a proximity.';
$searchByLanguage = 'Search by language';
$searchByPublication = 'Search by publication';
$searchByAuthor = 'Search by author';
$results = 'Results';
$sortByField = 'Sort by field';
$title = 'Title';
$author = 'Author';
$date = 'Date';

//text values in the Javascript (from top to bottom)
$show = 'Show';
$authors = 'Authors';
$publicationTranslate = 'Publication';
$textStartsOnPage = 'Text starts on page ';
//the possible languages are gotten from the Language field of the table, which gives the languages in English.  To translate
//these, put the English word for the language in $languages (which will recognize it in the table) and then the Turkish
//word for the language in $languagesTranslated, which will translate the language for display.  The languages should go
//in the same order in both arrays.
$languages = array('English', 'Turkish', 'German', 'French'); //these are all possible values of the language field (in English)
$languagesTranslated = array('English', 'Turkish', 'German', 'French'); //these are the Turkish or English words for those languages
$noResultsMatched = 'No results matched your search.  Please search again.';
$clickOnEntry = 'Click on an entry to see its location on the map.';
$EnglishTitleTranslate = 'English title';
$TurkishTitleTranslate = 'Turkish title';
$OtherTitleTranslate = 'Other title';
$distance = 'Distance';
$exportCitation = 'Export citation';



?>
