<?php
include 'config.php';
if (isset($_GET['lang']) && $_GET['lang'] == 'tr'){
  include 'tr.php';
} else {
  include 'en.php';
}
?>

<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
  <meta name="viewport" content="initial-scale=1, maximum-scale=1, user-scalable=no">
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title></title>
  <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
  <link rel="stylesheet" href="https://js.arcgis.com/4.4/esri/css/main.css">
  <link rel="stylesheet" href="style.css">
  <script src="https://js.arcgis.com/4.4/"></script>

  <script>
  require([
    "esri/Map",
    "esri/WebMap",
    "esri/views/MapView",
    "esri/layers/FeatureLayer",
    "esri/geometry/Point",
    "esri/geometry/Polygon",
    "esri/geometry/Circle",
    "esri/geometry/Polyline",
    "esri/geometry/SpatialReference",
    "esri/geometry/Extent",
    "esri/Graphic",
    "esri/Color",
    "esri/symbols/SimpleMarkerSymbol",
    "esri/layers/GraphicsLayer",
    "esri/symbols/SimpleFillSymbol",
    "esri/symbols/SimpleLineSymbol",
    "esri/tasks/QueryTask",
    "esri/tasks/support/Query",
    "esri/tasks/support/StatisticDefinition",
    "esri/geometry/geometryEngine",
    "esri/widgets/Search",
    "esri/widgets/Popup",
    "esri/PopupTemplate",
    "esri/core/watchUtils",
    "esri/renderers/SimpleRenderer",
    "dojo/dom",
    "dojo/domReady!",
    "dojo/ready"
  ],

  function(Map, WebMap, MapView, FeatureLayer, Point, Polygon, Circle, Polyline, SpatialReference, Extent, Graphic, Color, SimpleMarkerSymbol, GraphicsLayer, SimpleFillSymbol, SimpleLineSymbol, QueryTask,
    Query, StatisticDefinition, geometryEngine, Search, Popup, PopupTemplate, watchUtils, SimpleRenderer, dom){

      //------------------------------INITIALIZE MAP AND VARIABLES---------------------------------------
      var polygon;
      var geometry;
      var layer;
      var selectedPoints = new GraphicsLayer();
      var selectedPointsGraphics;
      var biblioFeatures;
      var searchByCircle = false;
      var drawing = false;
      var biblioFeatures2 = [];
      var biblioFeatures3;
      var searchArea;
      var layerExtent;
      var listOfAuthorFields = [];
      var listOfFirstNameFields = [];
      var listOfLastNameFields = [];
      var englishTitle;
      var turkishTitle;
      var otherTitle;
      var publication;
      var language;
      var pageStart;
      var pageEnd;
      var volume;
      var number;
      var listOfLayers = [];
      var listOfBiblioFeatures2 = [];
      var searchList = [];
      var allRecords = document.getElementById("allRecords");
      var resultsShowing;
      var clicks = 0;
      var firstPoint;
      var secondPoint;
      var lineSymbol = {
        type: "simple-line",
        color: "lightblue",
        width: "10px",
        style: "short-dot"
      };
      var line = new Polyline();
      var smallestX;
      var smallestY;
      var biggestX;
      var biggestY;
      var geocodeClearClone;
      var distanceUnits = "<?php echo $distanceUnits ?>";


      var drawConfig = {
        drawingSymbol: new SimpleFillSymbol({
          color: [102, 0, 255, 0.15],
          outline: {
            color: "#6600FF",
            width: 2
          }
        }),
        finishedSymbol: new SimpleFillSymbol({
          color: [102, 0, 255, 0.45],
          outline: {
            color: "#6600FF",
            width: 2
          }
        }),
        activePolygon: null,
        isDrawActive: false
      };

      //initialize the webmap and view
      id = '<?php echo $mapID?>';
      var map = new WebMap({
        portalItem: { // autocasts as new PortalItem()
          id: id
        }
      });

      var view = new MapView({
        container: "viewDiv",  // Reference to the scene div created in step 5
        map: map,  // Reference to the map object created before the scene
        zoom: 7,  // Sets zoom level based on level of detail (LOD)
        center: [29.5, 40]  // Sets center point of view using longitude,latitude
      });

      var searchWidget = new Search({
        view: view,
        resultGraphicEnabled: false,
        popupEnabled: false,
        popupOpenOnSelect: false,
      });

      var checkedList = [];
      var nameList;

      //Add buttons
      view.ui.add("polygon-button", "top-left");
      view.ui.add("circle-button", "top-left");
      view.ui.add("distanceSelectDiv", "top-right");
      view.ui.add(searchWidget, {
        position: "top-right",
        index: 2
      });


      //---------------------------MISCELLANEOUS FUNCTIONS-----------------------
      //test if value is in array
      function isInArray(value, array) {
        return array.indexOf(value) > -1;
      }

      //order items in an array by the number of times they occur
      function orderByOccurrence(arr) {
        var counts = {};
        arr.forEach(function(value){
          if(!counts[value]) {
            counts[value] = 0;
          }
          counts[value]++;
        });
        return Object.keys(counts).sort(function(curKey,nextKey) {
          return counts[curKey] > counts[nextKey];
        });
      }

      String.prototype.toProperCase = function () {
        return this.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
      };


      //-----------------------------FUNCTIONS ASSOCIATED WITH THE VIEW-----------------------------------------
      //zoom to selected features
      function zoomToLayer(zoomLayer, multipleLayers) {
        var padding = <?php echo $extentPadding ?>;
        var leftMargin = <?php echo $leftMarginMultiplier ?>;

        //get the extent of features across multiple layers
        if (multipleLayers == true) {
          biblioFeatures4 = zoomLayer[0].source.toArray();
          if (zoomLayer[0].geometryType == "point") {
            smallestX = biblioFeatures4[0].geometry.x;
            smallestY = biblioFeatures4[0].geometry.y;
            biggestX = biblioFeatures4[0].geometry.x;
            biggestY = biblioFeatures4[0].geometry.y;
            for (i = 0; i < zoomLayer.length; i++) {
              biblioFeatures5 = zoomLayer[i].source.toArray();
              if (zoomLayer[i].geometryType == "point") {
                for (j = 0; j < biblioFeatures5.length; j++){
                  if (biblioFeatures5[j].geometry.x < smallestX) {
                    smallestX = biblioFeatures5[j].geometry.x;
                  }
                  if (biblioFeatures5[j].geometry.y < smallestY) {
                    smallestY = biblioFeatures5[j].geometry.y;
                  }
                  if (biblioFeatures5[j].geometry.x > biggestX) {
                    biggestX = biblioFeatures5[j].geometry.x;
                  }
                  if (biblioFeatures5[j].geometry.y > biggestY) {
                    biggestY = biblioFeatures5[j].geometry.y;
                  }
                }
              }
              if (zoomLayer[i].geometryType == "polygon") {
                for (j = 0; j < biblioFeatures5.length; j++) {
                  if (biblioFeatures5[j].geometry.centroid.x < smallestX) {
                    smallestX = biblioFeatures5[j].geometry.centroid.x;
                  }
                  if (biblioFeatures5[j].geometry.centroid.y < smallestY) {
                    smallestY = biblioFeatures5[j].geometry.centroid.y;
                  }
                  if (biblioFeatures5[j].geometry.centroid.x > biggestX) {
                    biggestX = biblioFeatures5[j].geometry.centroid.x;
                  }
                  if (biblioFeatures5[j].geometry.centroid.y > biggestY) {
                    biggestY = biblioFeatures5[j].geometry.centroid.y;
                  }
                }
              }
            }
          }

          if (zoomLayer[0].geometryType == "polygon") {
            smallestX = biblioFeatures4[0].geometry.centroid.x;
            smallestY = biblioFeatures4[0].geometry.centroid.y;
            biggestX = biblioFeatures4[0].geometry.centroid.x;
            biggestY = biblioFeatures4[0].geometry.centroid.y;
            for (i = 0; i < zoomLayer.length; i++){
              if (zoomLayer[i].geometryType == "polygon") {
                biblioFeatures5 = zoomLayer[i].source.toArray();
                for (j = 0; j < biblioFeatures5.length; j++) {
                  if (biblioFeatures5[j].geometry.centroid.x < smallestX) {
                    smallestX = biblioFeatures5[j].geometry.centroid.x;
                  }
                  if (biblioFeatures5[j].geometry.centroid.y < smallestY) {
                    smallestY = biblioFeatures5[j].geometry.centroid.y;
                  }
                  if (biblioFeatures5[j].geometry.centroid.x > biggestX) {
                    biggestX = biblioFeatures5[j].geometry.centroid.x;
                  }
                  if (biblioFeatures5[j].geometry.centroid.y > biggestY) {
                    biggestY = biblioFeatures5[j].geometry.centroid.y;
                  }
                }
              }

              if (zoomLayer[i].geometryType == "point") {
                biblioFeatures5 = zoomLayer[i].source.toArray();
                for (j = 0; j < biblioFeatures5.length; j++) {
                  if (biblioFeatures5[j].geometry.x < smallestX) {
                    smallestX = biblioFeatures5[j].geometry.x;
                  }
                  if (biblioFeatures5[j].geometry.y < smallestY) {
                    smallestY = biblioFeatures5[j].geometry.y;
                  }
                  if (biblioFeatures5[j].geometry.x > biggestX) {
                    biggestX = biblioFeatures5[j].geometry.x;
                  }
                  if (biblioFeatures5[j].geometry.y > biggestY) {
                    biggestY = biblioFeatures5[j].geometry.y;
                  }
                }
              }
            }
          }

          //create margins to encourage the map to round its zoom out rather than in
          var xMargin = (biggestX - smallestX) * padding;
          var yMargin = (biggestY - smallestY) * padding;

          //create a new extent
          var extent = new Extent({
            xmin: smallestX - (leftMargin * xMargin),
            ymin: smallestY - yMargin,
            xmax: biggestX + xMargin,
            ymax: biggestY + yMargin,
            spatialReference: {
              wkid: 102100
            }
          });
          //zoom to that extent
          view.goTo(extent);
        }

        //create extent from a single layer of features
        if (multipleLayers == false) {
          //if the features are points
          var biblioFeatures4 = zoomLayer;
          if (biblioFeatures4[0] && biblioFeatures4[0].geometry.x) {
            smallestX = biblioFeatures4[0].geometry.x;
            smallestY = biblioFeatures4[0].geometry.y;
            biggestX = biblioFeatures4[0].geometry.x;
            biggestY = biblioFeatures4[0].geometry.y;
          }

          if (biblioFeatures4[0] && biblioFeatures4[0].geometry.centroid) {
            smallestX = biblioFeatures4[0].geometry.centroid.x;
            smallestY = biblioFeatures4[0].geometry.centroid.y;
            biggestX = biblioFeatures4[0].geometry.centroid.x;
            biggestY = biblioFeatures4[0].geometry.centroid.y;
          }

          for (i = 0; i < biblioFeatures4.length; i++){
            if (biblioFeatures4[i].geometry.x) {
              if (biblioFeatures4[i].geometry.x < smallestX) {
                smallestX = biblioFeatures4[i].geometry.x;
              }
              if (biblioFeatures4[i].geometry.y < smallestY) {
                smallestY = biblioFeatures4[i].geometry.y;
              }
              if (biblioFeatures4[i].geometry.x > biggestX) {
                biggestX = biblioFeatures4[i].geometry.x;
              }
              if (biblioFeatures4[i].geometry.y> biggestY) {
                biggestY = biblioFeatures4[i].geometry.y;
              }
            }
            if (biblioFeatures4[i].geometry.centroid) {
              if (biblioFeatures4[i].geometry.centroid.x < smallestX) {
                smallestX = biblioFeatures4[i].geometry.centroid.x;
              }
              if (biblioFeatures4[i].geometry.centroid.y < smallestY) {
                smallestY = biblioFeatures4[i].geometry.centroid.y;
              }
              if (biblioFeatures4[i].geometry.centroid.x > biggestX) {
                biggestX = biblioFeatures4[i].geometry.centroid.x;
              }
              if (biblioFeatures4[i].geometry.centroid.y > biggestY) {
                biggestY = biblioFeatures4[i].geometry.centroid.y;
              }
            }
          }

          //create margins
          var xMargin = (biggestX - smallestX) * padding;
          var yMargin = (biggestY - smallestY) * padding;

          //create extent
          var extent = new Extent({
            xmin: smallestX - (leftMargin * xMargin),
            ymin: smallestY - yMargin,
            xmax: biggestX + xMargin,
            ymax: biggestY + yMargin,
            spatialReference: {
              wkid: 102100
            }
          });
          //zoom to that extent
          view.goTo(extent);
        }
      } //end zoomToLayer


//---------------------------BEGIN VIEW.THEN----------------------
//fires once the map is loaded.  all map activities must happen here
      view.then(function() {
        //search by word when the enter key is pressed
        document.getElementById("searchInput")
        .addEventListener("keyup", function(event) {
          event.preventDefault();
          if (event.keyCode === 13) {
            document.getElementById("searchBoxButton").click();
          }
        });

        //populate the geocoder distance selector with configurable search distances
        var distanceSelector = document.getElementById("distance_select");
        var distances = <?php echo json_encode($distances) ?>;
        var distanceUnitsWord = "<?php echo $distanceUnitsWord?>";
        for (i=0; i < distances.length; i++) {
          distanceSelector.innerHTML += "<option value = '" + distances[i] + "'>" + distances[i] + " " + distanceUnitsWord + "</option>";

        }

        //change the symbol for the layers from points to polygons when the user zooms in
        //past a certain point
        watchUtils.whenTrue(view, "stationary", function() {
          //the symbol for the polygons
          highFillSymbol = new SimpleFillSymbol({
            color: [255, 139, 0, .75],
            outline: { // autocasts as new SimpleLineSymbol()
              color: [255, 139, 0],
              width: 2
            }
          });

          //the symbol for the points
          var pointSymbol = new SimpleMarkerSymbol();
          pointSymbol.style = SimpleMarkerSymbol.STYLE_CIRCLE;
          pointSymbol.size = 4;
          pointSymbol.color = new Color([0,0,0,1]);

          if (layer && view.scale > <?php echo $polygonZoomLevel ?>) {
            var renderer2 = new SimpleRenderer();
            renderer2.symbol = pointSymbol;
            for (var i = 0; i < listOfLayers.length; i++) {
              if (listOfLayers[i].geometryType == "polygon") {
                listOfLayers[i].renderer = renderer2;
              }
            }
          }

          if (layer && view.scale < <?php echo $polygonZoomLevel ?>) {
            var renderer3 = new SimpleRenderer();
            renderer3.symbol = highFillSymbol;
            for (var i = 0; i < listOfLayers.length; i++) {
              if (listOfLayers[i].geometryType == "polygon") {
                listOfLayers[i].renderer = renderer3;
              }
            }
          }
        });
        //---------------------------DEFINE LAYERS AND FIELDS-------------------------------

        resultsShowing = false;
        document.getElementById("search").style.width = "350px";
        var allLayers = map.allLayers.toArray();

        for (var i = 0; i < allLayers.length; i++) {
          console.log(allLayers[i].id);
        }
        //identify layers
        var layerList = <?php echo json_encode($layerIDList) ?>;
        for (i = 0; i < layerList.length; i++) {
          mapLayer =  map.findLayerById(layerList[i]);
          listOfLayers.push(mapLayer);
        }
        layer = listOfLayers[0];
        var showFields = <?php echo $showFieldsInConsole ?>;
        if (showFields == true) {
          for (var i = 0; i < layer.fields.length; i++){
            console.log("field index " + i + " is " + layer.fields[i].name);
          }
        }

        zoomToLayer(listOfLayers, true);

        //get the fields associated with author first, last and full name
        var listOfAuthorFieldIndices = <?php echo json_encode($authorIndices) ?>;
        var listOfFirstNameFieldIndices = <?php echo json_encode($firstNameIndices) ?>;
        var listOfLastNameFieldIndices = <?php echo json_encode($lastNameIndices) ?>;

        for (var i = 0; i < listOfAuthorFieldIndices.length; i ++) {
          var authorNameIndex = listOfAuthorFieldIndices[i];
          listOfAuthorFields.push(layer.fields[authorNameIndex].name);

        }
        for (var i = 0; i < listOfLastNameFieldIndices.length; i ++) {
          var lastNameIndex = listOfLastNameFieldIndices[i];
          listOfLastNameFields.push(layer.fields[lastNameIndex].name);
        }

        for (var i = 0; i < listOfFirstNameFieldIndices.length; i ++) {
          var firstNameIndex = listOfFirstNameFieldIndices[i];
          listOfFirstNameFields.push(layer.fields[firstNameIndex].name);
        }

        //get the indices of other fields
        englishTitleIndex = <?php echo $englishTitleField ?>;

        turkishTitleIndex = <?php echo $turkishTitleField ?>;
        otherTitleIndex = <?php echo $otherTitleField ?>;
        publicationIndex = <?php echo $publicationField ?>;
        languageIndex = <?php echo $languageField ?>;
        pageStartIndex = <?php echo $pageStartField ?>;
        pageEndIndex = <?php echo $pageEndField ?>;
        volumeIndex =  <?php echo $volumeField ?>;
        numberIndex = <?php echo $numberField ?>;
        uniqueIDIndex = <?php echo $uniqueIDField ?>;
        dateIndex = <?php echo $dateField ?>;


        //get field names
        englishTitle = layer.fields[englishTitleIndex].name;
        if (layer.fields[turkishTitleIndex]) {
          turkishTitle = layer.fields[turkishTitleIndex].name;
        }
        else {
          turkishTitle = "";
        }
        otherTitle = layer.fields[otherTitleIndex].name;
        publication = layer.fields[publicationIndex].name;
        language = layer.fields[languageIndex].name;
        pageStart = layer.fields[pageStartIndex].name;
        pageEnd = layer.fields[pageEndIndex].name;
        volume = layer.fields[volumeIndex].name;
        number = layer.fields[numberIndex].name;
        uniqueID = layer.fields[uniqueIDIndex].name;
        date = layer.fields[dateIndex].name;

        //---------------------------ATTACH LISTENERS TO BUTTONS AND SELECTORS----------------------

        //move the search widget into the same div as the distance selector
        var widget = document.getElementsByClassName("esri-search");
        document.getElementById("distanceSelectDiv").appendChild(widget[0]);
        //change the function of the geocode button to open and close the geocoder search div
        var geocodeButton = document.getElementsByClassName("esri-search__submit-button")[0];
        var geocodeClone = geocodeButton.cloneNode(true);
        geocodeButton.parentNode.removeChild(geocodeButton);
        document.getElementById("distanceSelectDiv").appendChild(geocodeClone);
        var geocoderVisible = false;
        geocodeClone.addEventListener("click", function(){
          var searchContainer = document.getElementsByClassName("esri-search")[0];
          if (geocoderVisible == false) {
            document.getElementById("distance_select").style.display = "block";
            searchContainer.style.display = "block";
          }
          if (geocoderVisible == true) {
            document.getElementById("distance_select").style.display = "none";
            searchContainer.style.display = "none";
          }
          geocoderVisible = !geocoderVisible;
        });
        var searching = false;
        var geocodeClearButton;


        searchWidget.on("search-start", function(event){
          //change the function of the geocoder exit button to both clear the search and also re-run the
          //search without the geocoder results
          if (searching == false) {
            geocodeClearButton = document.getElementsByClassName("esri-search__clear-button")[0];
            geocodeClearClone = geocodeClearButton.cloneNode(true);
            geocodeClearButton.parentNode.removeChild(geocodeClearButton);
            document.getElementsByClassName("esri-search__form")[0].appendChild(geocodeClearClone);
            geocodeClearClone.addEventListener("click", function(){
              clearPolygon();
              var images = document.getElementsByTagName('image');
              //get rid of the weird circle the geocoder created
              if (images.length > 0) {
                for (i = 0; i < images.length; i++) {
                  images[i].parentNode.removeChild(images[i]);
                }
              }
              document.getElementById("distance_select").selectedIndex = 0;
              document.getElementById("polygonSearchTag").style.display = "none";
              for (var i = 0; i < view.graphics.length; i++) {
                view.graphics.remove(view.graphics.toArray()[i]);
              }
              view.popup.close();
              searchWidget.clear();
              filterByAll();
              searching = false;
            });
            searching = true;
          }
        });

        //the old geocoder clear button (the one we got rid of above) will reappear every time the user enters
        //new text in the geocoder search (this is an automatic ESRI function).  this checks for it and removes it
        searchWidget.on("search-focus", function(event) {
          setInterval(function(){
            var oldGeocodeClear = document.getElementsByClassName("esri-search__clear-button")[1];
            if (oldGeocodeClear) {
              oldGeocodeClear.parentNode.removeChild(oldGeocodeClear);
            }
          }, 150);
        });

        searchWidget.on("search-complete", function(data) {
          clearPolygon();
          setTimeout(function(){
            var images = document.getElementsByTagName('image');
            if (images.length > 0) {
              for (i = 0; i < images.length; i++) {
                images[i].parentNode.removeChild(images[i]);
              }
            }
          }, 50)

          if (searchByCircle == true) {
            circleButton.classList.toggle("esri-circle-button-selected");
            searchByCircle = false;
          }
          var distanceValue = document.getElementById("distance_select").value;
          var point2 = new Point(searchWidget.results[0].results[0].feature.geometry);
          circle2 = new Circle({
            center: point2,
            geodesic: false,
            radius: distanceValue,
            radiusUnit: distanceUnits
        });

          searchArea = circle2;
          var circleSymb = new SimpleFillSymbol(SimpleFillSymbol.STYLE_NULL,
            new SimpleLineSymbol(
              SimpleLineSymbol.STYLE_SHORTDASHDOTDOT,
              new Color([105, 105, 105]),2), new Color([255, 255, 0, 0.25]));
          var graphic = new Graphic(circle2, circleSymb);
          view.graphics.add(graphic);
          filterByGeometry(searchArea);
          linkRecordsToPopups();
        });

            //set the hide button to hide or show the search bar
        document.getElementById("innerSearch").style.display = "block";
        document.getElementById("hideSearch").addEventListener("click", function() {
          if (document.getElementById("innerSearch").style.display == "block") {
            document.getElementById("innerSearch").style.display = "none";
            document.getElementById("hideSearch").innerHTML = '<?php echo $show ?>';
            document.getElementById("hideSearcha").style.left = "2.5%";
            return;
          }

          if (document.getElementById("innerSearch").style.display == "none") {
            document.getElementById("innerSearch").style.display = "block";
            //document.getElementById("viewDiv").style.width = "73%";
            document.getElementById("hideSearch").innerHTML = '<?php echo $hide ?>';
            document.getElementById("hideSearcha").style.left = "94.5%";
            return;
          }
        });

            //sort the results when the sort selector option is changed
        var sortSelector = document.getElementById("sortSelect");
        sortSelector.addEventListener("change", sortByField);

        //add show all button that will show and print all records and reset all searches
        var showAll = document.getElementById("showAll");
        showAll.addEventListener("click", function() {
          document.getElementById("sortSelect").selectedIndex = 0;
          document.getElementById("polygonSearchTag").style.display = "none";
          filterByList(biblioFeatures2);
          paginate(biblioFeatures2, false);
          linkRecordsToPopups();
          biblioFeatures3 = biblioFeatures2;
        });

            //clear the selection and reset the map when the clear button is clicked
        clearButton = document.getElementById("clear-button");
        clearButton.addEventListener("click", function() {
          document.getElementById("resultsCard").click();
          resultsShowing = false;
          checkedList = [];
          checkedList2 = [];
          document.getElementById("polygonSearchTag").style.display = "none";
          var images = document.getElementsByTagName('image');
          if (images.length > 0) {
            for (i = 0; i < images.length; i++) {
              images[i].parentNode.removeChild(images[i]);
            }
          }
          clearPolygon();
          allRecords.innerHTML = "";
          document.getElementById("sortSelectDiv").style.display = "none";
          document.getElementById("paginateDiv").innerHTML = "";
          if (searchByCircle == true) {
            circleButton.classList.toggle("esri-circle-button-selected");
            deactivateDraw();
            searchByCircle = false;
          }
          if (drawing == true) {
            polygonButton.classList.toggle("esri-polygon-button-selected");
            deactivateDraw();
            drawing = false;
          }
          document.getElementById("distance_select").selectedIndex = 0;
          searchWidget.clear();
          view.popup.close();
          filterByList(biblioFeatures2);
          biblioFeatures3 = biblioFeatures2;
          zoomToLayer(listOfLayers, true);
        });

            //filter by the key word typed into the search box
        var searchBoxButton = document.getElementById("searchBoxButton");
        searchBoxButton.addEventListener("click", function() {
          filterByWord();
        });

        //clear the search by keyword box and redo the search when the x is clicked
        clearSearchButton = document.getElementById("clearSearchButton");
        clearSearchButton.addEventListener("click", function (){
          document.getElementById("searchInput").value = "";
          filterByAll();
        });

        //start drawing when the draw button is clicked
        //if a polygon or circle is already on the map, or one of the buttons is selected,
        //and the user clicks one of the buttons again, the geometry will be removed and the draw
        //or circle functions will be reset.  The user can use them again by clicking again
        polygonButton = document.getElementById("polygon-button");
        polygonButton.addEventListener("click", function() {
          drawing = !drawing;
          allRecords.innerHTML = "";
          document.getElementById("paginateDiv").innerHTML = "";
          var images = document.getElementsByTagName('image');
          if (images.length > 0) {
            for (i = 0; i < images.length; i++) {
              images[i].parentNode.removeChild(images[i]);
            }
          }
          polygonButton.classList.toggle("esri-polygon-button-selected");
          if (searchByCircle == true) {
            circleButton.classList.toggle("esri-circle-button-selected");
            searchByCircle = false;
          }
          document.getElementById("distance_select").selectedIndex = 0;
          searchWidget.clear();
          view.popup.close();

          if (document.getElementById("polygonSearchTag").style.display == "block") {
            document.getElementById("clearPolygonTag").click();
            drawConfig.isDrawActive = false;
            drawConfig.activePolygon = null;
            polygonButton.classList.toggle("esri-polygon-button-selected");
            drawing = false;
            return;
          }

          if (!drawConfig.isDrawActive) {
            activateDraw(biblioFeatures2);
          }

          else {
            deactivateDraw();
            clearPolygon();
          }
        });

        //select by circle when the user draws a line on the map
        circleButton = document.getElementById("circle-button");
        circleButton.addEventListener("click", function() {
          allRecords.innerHTML = "";
          document.getElementById("paginateDiv").innerHTML = "";
          if (drawing == true) {
            deactivateDraw();
            polygonButton.classList.toggle("esri-polygon-button-selected");
            drawing = false;
          }

          if (document.getElementById("polygonSearchTag").style.display == "block") {
            document.getElementById("clearPolygonTag").click();
            searchByCircle = false;
            return;
          }

          if (searchByCircle == false) {
            activateDraw(biblioFeatures2);
          }
          //this is the only way to get rid of the graphic created by the geosearch
          var images = document.getElementsByTagName('image');
          if (images.length > 0) {
            for (i = 0; i < images.length; i++) {
              images[i].parentNode.removeChild(images[i]);
            }
          }
          if (searchByCircle == true) {
            document.getElementById("distance_select").selectedIndex = 0;
            clearPolygon();
          }
          searchByCircle = !searchByCircle;
          circleButton.classList.toggle("esri-circle-button-selected");
          searchWidget.clear();
          view.popup.close();
        });

        //remove the geometry and redo the search when the polygon search tag
        //is cleared (thisi also clears the circle)
        var clearPolygonTag = document.getElementById("clearPolygonTag");
        clearPolygonTag.addEventListener("click", function() {
          if (searching == true) {
            geocodeClearClone.click();
            searching = false;
          }
          clearPolygon();
          document.getElementById("polygonSearchTag").style.display = "none";
          filterByAll();
        });

        //search by checklist when any checklist search button is clicked
        var filterButtons = document.getElementsByClassName("filterButton");
        for (var i = 0; i < filterButtons.length; i++) {
          filterButton = filterButtons[i];
          filterButton.addEventListener("click", function() {
            filterByAll();
          });
        }

        //register that the results are hidden when the intro or search tabs are clicked
        var introCard = document.getElementById("introCard");
        introCard.addEventListener("click", function(){
          resultsShowing = false;
        });

        var searchCard = document.getElementById("searchCard");
        searchCard.addEventListener("click", function(){
          resultsShowing = false;
        });


        //----------------------INITIALIZING THE DISPLAY ----------------------------------
        //Set an attribute "Main Title" which represents the title that is to be used for reference purposes
        //and which is the title in whatever language the text is in
        //Set the popup content in the field "PopupContent"
        for (h = 0; h < listOfLayers.length; h++) {
          layer = listOfLayers[h];
          var biblioFeaturesLayer = [];
          var biblioFeatures = layer.source.toArray();
          for (var i = 0; i < biblioFeatures.length; i++) {
            if (biblioFeatures[i].attributes[language] == "Turkish" && biblioFeatures[i].attributes[turkishTitle]){
              biblioFeatures[i].setAttribute("MainTitle", biblioFeatures[i].attributes[turkishTitle]);
            }
            if (biblioFeatures[i].attributes[language] == "English"){
              biblioFeatures[i].setAttribute("MainTitle", biblioFeatures[i].attributes[englishTitle]);
            }
            if (biblioFeatures[i].attributes[language] == "French" || biblioFeatures[i].attributes[language] == "German"){
              biblioFeatures[i].setAttribute("MainTitle", biblioFeatures[i].attributes[otherTitle]);
            }
            if (!biblioFeatures[i].attributes[language]) {
              biblioFeatures[i].setAttribute("MainTitle", <?php echo $popupTitle ?>);
            }
            else {
              biblioFeatures[i].setAttribute("MainTitle", biblioFeatures[i].attributes[englishTitle]);
            }

            var popupContent;
            popupContent = "<b>" + '<?php echo $authors ?>' + ": </b>";
            var firstAuthor = listOfAuthorFields[0];
            if (biblioFeatures[i].attributes[firstAuthor]) {
              popupContent += biblioFeatures[i].attributes[firstAuthor];
            }
            for (var j = 1; j < listOfAuthorFields.length; j++) {
              var authorField = listOfAuthorFields[j];
              if (biblioFeatures[i].attributes[authorField] && biblioFeatures[i].attributes[authorField] != " "){
                popupContent += ", " + biblioFeatures[i].attributes[authorField];
              }
            }
            popupContent += "<br/>";
            if (biblioFeatures[i].attributes[publication]) {
              popupContent += "<b>" + '<?php echo $publicationTranslate ?>' + ": </b>" + biblioFeatures[i].attributes[publication] + "<br/>";
            }

            if (biblioFeatures[i].attributes[pageStart]) {
              popupContent += '<?php echo $textStartsOnPage ?>' + biblioFeatures[i].attributes[pageStart];
            }

            biblioFeatures[i].setAttribute("PopupContent", popupContent);
            biblioFeatures2.push(biblioFeatures[i]);
            biblioFeaturesLayer.push(biblioFeatures[i]);
          }
          var popupTemplate = new PopupTemplate();
          layer.popupTemplate = popupTemplate;
          popupTemplate.title = "{MainTitle}";
          popupTemplate.content = "{PopupContent}";
          listOfBiblioFeatures2.push(biblioFeaturesLayer);
        }

        biblioFeatures3 = biblioFeatures2;

        //Popupate the desired divs in the search and browse based on div Id and field
        createBrowse(language, language, true, biblioFeatures);
        createBrowse(publication, publication, true, biblioFeatures);
        createBrowseMultiple(biblioFeatures, true);

        var checkboxes = document.getElementsByClassName("checkbox");
        var filterButtons = document.getElementsByClassName("filterButton");
        var browseDivs = document.getElementsByClassName("browse");
        var checkedList2;
        var textChecks;
        var pubChecks;

        //push checked boxes into a list when the user clicks on them
        for (var h = 0; h < browseDivs.length; h++) {
          browseDivs[h].addEventListener("click", function(e){
            checkedList2 = [];
            for (var i = 0; i < checkboxes.length; i++) {
              if (checkboxes[i].checked == true) {
                checkedList2.push(checkboxes[i]);
              }
            }
          });
        }

            //------------------------------FILTER FUNCTIONS------------------------------

            //when a search criterion is removed, re-run the search with all remaining criteria
        function filterByAll() {
          document.getElementById("sortSelect").selectedIndex = 0;
          biblioFeatures3 = biblioFeatures2;
          checkedList = [];
          var filterList = [];
          var checkboxes = document.getElementsByClassName("checkbox");
          for (var i = 0; i < checkboxes.length; i++) {
            if (checkboxes[i].checked == true) {
              checkedList.push(checkboxes[i]);
            }
          }

          //if options are checked in the checklist
          if (checkedList.length != 0) {
            filterByCheckList();
          }
          //if the geometry search is active
          if (view.graphics.length !=0) {
            filterByGeometry(searchArea);
          }
          //if the keyword search is active
          if (document.getElementById("searchInput").value.length != 0) {
            filterByWord();
          }
          //if no filters remain, show all features
          if (checkedList.length == 0 && view.graphics.length ==0 && document.getElementById("searchInput").value.length == 0){
            filterByList(biblioFeatures2);
            zoomToLayer(biblioFeatures2, false);
          }

          paginate(biblioFeatures3, false);
          linkRecordsToPopups();
          createBrowse(language, language, false, biblioFeatures3);
          createBrowse(publication, publication, false, biblioFeatures3);
          createBrowseMultiple(biblioFeatures3, false);
          recheckBoxes();
          zoomToLayer(biblioFeatures3, false);
        }

            //filter the map by all features that match the checklist criteria
        function filterByCheckList() {
          document.getElementById("sortSelect").selectedIndex = 0;
          var filterList = [];
          //if no boxes are checked, do nothing
          if (checkedList.length == 0) {
            return;
          }

          //get all the categories of search that have at least one box checked
          var nameList = [];
          for (var i = 0; i < checkedList.length; i++) {
            if (!isInArray(checkedList[i].name, nameList)){
              nameList.push(checkedList[i].name);
            }
          }
          //if a checklist search is being run on only one criteron
          if (nameList.length == 1) {
            for (var i = 0; i <biblioFeatures3.length; i++) {
              for (var j = 0; j < checkedList.length; j++) {

                var field = nameList[0];
                var value = checkedList[j].id;
                //if the criterion is author, search for currently displayed features that have a match for the selected author
                //in any of the author fields
                if (nameList[0] == "author") {

                  for (var k = 0; k < listOfAuthorFields.length; k++){
                    var field = listOfAuthorFields[k];
                    if (biblioFeatures3[i].attributes[field] == value && !isInArray(biblioFeatures3[i], filterList)){
                      filterList.push(biblioFeatures3[i]);

                    }
                  }
                }
                //otherwise, search for currently displayed features that have a match for the selected search field (which is the element name
                //of the selected checkbox)
                if (biblioFeatures3[i].attributes[field] == value && !isInArray(biblioFeatures3[i], filterList)){
                  filterList.push(biblioFeatures3[i]);
                }
              }
            }
          }

          //if more than one search criterion is checked, do the same thing as above, but create a list of lists of
          //features that match each criterion
          if (nameList.length > 1) {
            var listOfLists = [];
            for (var h = 0; h < nameList.length; h++) {
              var selectedList = [];
              for (var i = 0; i <biblioFeatures3.length; i++) {
                for (var j = 0; j < checkedList.length; j++) {
                  var field = nameList[h];
                  var value = checkedList[j].id;
                  if (field == "author") {
                    for (var k = 0; k < listOfAuthorFields.length; k++){
                      var field = listOfAuthorFields[k];
                      if (biblioFeatures3[i].attributes[field] == value && !isInArray(biblioFeatures3[i], filterList)){
                        selectedList.push(biblioFeatures3[i]);
                      }
                    }
                  }
                  else {
                    if (biblioFeatures3[i].attributes[field] == value && !isInArray(biblioFeatures3[i], selectedList)){
                      selectedList.push(biblioFeatures3[i]);
                    }
                  }
                }
              }
              listOfLists.push(selectedList);
            }
            //if there are two search criteria, select only the items in the second list that are also in the first list
            //(i.e. that match both criteria)
            if (!listOfLists[2]) {
              var list2 = listOfLists[1];
              for (i = 0; i <list2.length; i++) {
                if (isInArray(list2[i], listOfLists[0])){
                  filterList.push(list2[i]);
                }
              }
            }

            //if there are three search criteria, select only the items in the third list that are also in the first and second listOfLists
            //(i.e. that match all three criteria)
            if(listOfLists[2]) {
              var list3 = listOfLists[2];
              for (i = 0; i <list3.length; i++) {
                if (isInArray(list3[i], listOfLists[0]) && isInArray(list3[i], listOfLists[1])){
                  filterList.push(list3[i]);
                }
              }
            }
          }

          //set the currently selected features to the results of the checkList search
          biblioFeatures3 = filterList;

          //the current checklist will be remembered if non-checklist filter methods are used
          //subsequently
          checkedList2 = checkedList;
          //if no features match all of the selected criteria, add all the points back on the map;
          if (filterList == 0) {
            filterByList(biblioFeatures2);
          }
          //otherwise, put only those features which match all criteria on the map
          else{
            filterByList(filterList);
          }
          paginate(filterList, false);
          recheckBoxes();
        }//end filterByCheckList

        //filter by keyword
        function filterByWord () {
          document.getElementById("sortSelect").selectedIndex = 0;
          var searchTerm = document.getElementById("searchInput").value.toLowerCase();
          var fieldNames = [];
          for (var i = 0; i < layer.fields.length; i++){
            fieldNames.push(layer.fields[i].name);
          }
          searchList = [];
          //search for the keyword or phrase across all fields
          for (var i = 0; i < biblioFeatures3.length; i++) {
            for (var j = 0; j < fieldNames.length; j++){
              var field = fieldNames[j];
              if (biblioFeatures3[i].attributes[field]) {
                var fieldString = biblioFeatures3[i].attributes[field].toString().toLowerCase();
                if (fieldString.indexOf(searchTerm) !== -1 && !isInArray(biblioFeatures3[i], searchList)) {
                  searchList.push(biblioFeatures3[i]);
                }
              }
            }
          }
          biblioFeatures3 = searchList;
          filterByList(searchList);
          paginate(searchList, false);
        } //end fitlerByWord

        //display only selected points on the map
        function filterByList(selectedList) {
          for (h = 0; h < listOfLayers.length; h++) {
            layer = listOfLayers[h];
            biblioFeatures = [];
            var selectedListInLayer = [];
            var features = layer.source.toArray();
            var biblioFeatures22 = listOfBiblioFeatures2[h];
            for (g = 0; g < features.length; g++) {
              biblioFeatures.push(features[g]);
            }
            //match selected features with their layer of origin
            for (f = 0; f < selectedList.length; f++) {
              for (a = 0; a < biblioFeatures22.length; a++) {
                if (selectedList[f].attributes[uniqueID] == biblioFeatures22[a].attributes[uniqueID]) {
                  selectedListInLayer.push(selectedList[f]);
                }
              }
            }
            //remove all features
            for (var i = 0; i < biblioFeatures.length; i++) {
              layer.source.remove(biblioFeatures[i]);
            }
            //add features back to their layer of origin
            for (var i = 0; i < selectedListInLayer.length; i++) {
              layer.source.add(selectedListInLayer[i]);
            }
          }
          var browse = document.getElementsByClassName("browse");
          for (var i = 0; i < browse.length; i++) {
            browse[i].innerHTML = "";
          }

          createBrowse(language, language, true, biblioFeatures3);
          createBrowse(publication, publication, true, biblioFeatures3);
          createBrowseMultiple(biblioFeatures3, true);
        } //end filterByList



        //----------------------------LISTEN FOR CLICKS ON THE MAP--------------------------
        view.on("click", function(evt) {
          //open a popup when a feature is clicked
          view.hitTest(evt).then(function(response) {
            var clickedList = [response.results[0].graphic];
            if (clickedList[0].attributes[language] == "Turkish" && clickedList[0].attributes[turkishTitle]){
              clickedList[0].setAttribute("MainTitle", clickedList[0].attributes[turkishTitle]);
            }
            if (clickedList[0].attributes[language] == "English"){
              clickedList[0].setAttribute("MainTitle", clickedList[0].attributes[englishTitle]);
            }
            if (clickedList[0].attributes[language] == "French" || clickedList[0].attributes[language] == "German"){
              clickedList[0].setAttribute("MainTitle", clickedList[0].attributes[otherTitle]);
            }
            else {
              if (clickedList[0].attributes[englishTitle]) {
                clickedList[0].setAttribute("MainTitle", clickedList[0].attributes[englishTitle]);
              }
              if (clickedList[0].attributes[otherTitle]) {
                clickedList[0].setAttribute("MainTitle", clickedList[0].attributes[otherTitle]);
              }
            }
            view.popup.close();
            view.popup = new Popup({
              title: clickedList[0].attributes["MainTitle"],
              location: evt.mapPoint,
              content: clickedList[0].attributes["PopupContent"]
            });
            view.popup.open()
          }).then();

          //if searching by circle, draw a line between two clicked points
          if (searchByCircle == true) {
            clicks++;
            var searchDistance = document.getElementById("distance_select").value;
            searchWidget.clear();
            if (clicks == 1 && searchByCircle == true) {
              firstPoint = evt.mapPoint;
              line.addPath([firstPoint]);
              return;
            }
            if (clicks == 2 && searchByCircle == true) {
              completeCircle(evt.mapPoint);
            }
          } //end if searchByCircle true
        }); //end view.onClick
      });  //end view.then
  //------------------END VIEW.THEN---------------------------------------------

  //filter the points that fall within a chosen geometry
      function filterByGeometry(searchArea) {
        document.getElementById("sortSelect").selectedIndex = 0;
        var selectedList = [];
        for (h = 0; h < listOfLayers.length; h++) {
          layer = listOfLayers[h];
          biblioFeatures = layer.source.toArray();
          var includeDistance = false;
          //if the feature falls within the polygon, add it to a list and add a distance attribute
          for (var i = 0; i < biblioFeatures.length; i++){
            if (biblioFeatures[i].geometry.centroid) {
              var polygon = new Polygon(biblioFeatures[i].geometry);
              if (geometryEngine.intersects(searchArea, polygon) || geometryEngine.contains(searchArea, polygon)){
                if (searchArea.radius) {
                  var center = new Point(searchArea.center);
                  var distance = geometryEngine.distance(center, polygon, "miles").toFixed(2);
                  biblioFeatures[i].setAttribute("Distance", distance);
                }
                selectedPoints.add(biblioFeatures[i]);
                selectedList.push(biblioFeatures[i]);
              }
            }
            else {
              var point = new Point (biblioFeatures[i].geometry);
              if (geometryEngine.contains(searchArea, point)){
                selectedPoints.add(biblioFeatures[i]);
                selectedList.push(biblioFeatures[i]);
              }
            }
          }
          biblioFeatures22 = listOfBiblioFeatures2[h];
          var selectedListInLayer2 = [];
          for (f = 0; f < selectedList.length; f++) {
            for (a = 0; a < biblioFeatures22.length; a++) {
              if (selectedList[f].attributes[uniqueID] == biblioFeatures22[a].attributes[uniqueID]) {
                selectedListInLayer2.push(selectedList[f]);
              }
            }
          }
          //remove all features
          for (var i = 0; i < biblioFeatures.length; i++) {
            layer.source.remove(biblioFeatures[i]);
          }
          //add features back to their layer of origin
          for (var i = 0; i < selectedListInLayer2.length; i++) {
            layer.source.add(selectedListInLayer2[i]);
          }
        }
        createBrowse(language, language, false, selectedList);
        createBrowse(publication, publication, false, selectedList);
        createBrowseMultiple(selectedList, false);
        var checkedBoxes = document.getElementsByClassName("checkbox");
        recheckBoxes();
        biblioFeatures3 = selectedList;
        //sort results by distance
        var sortedGraphics = selectedPoints.graphics.sort(function(a, b){
          if(parseFloat(a.attributes["Distance"]) > parseFloat(b.attributes["Distance"])){
            return 1;
          }
          else if (parseFloat(a.attributes["Distance"]) < parseFloat(b.attributes["Distance"])){
            return -1;
          }
          else {
            return 0;
          }
        });
        selectedPointsGraphics = sortedGraphics.toArray();
        paginate(selectedPointsGraphics, includeDistance);
        document.getElementById("polygonSearchTag").style.display = "block";
      } //end filterByGeometry


    //----------------------RESULTS WINDOW FUNCTIONS---------------------------------------
    //for a given field name, get all values of that field, the number of records with
    //that value, and add them to the given div in the search and browse
      function createBrowse(divId, fieldName, useLayer, selectedFeatures) {

        //divId indicates the div that will be populated
        //fieldName is the name of the field used to populate the div
        //useLayer indicates whether the browse should be created using the current value
        //of layer.source.  The text browse functions modify layer.source and thus this should be true.
        //The geographic search functions select points without modifying layer.source, so this value should be false
        //and the list of selected points should go in selectedFeatures
        var browseDiv = document.getElementById(divId);
        selectedList = [];
        browseDiv.innerHTML = "<form action=''>";
        if (useLayer == true) {
          for (h = 0; h < listOfLayers.length; h++) {
            layer = listOfLayers[h];
            var features = layer.source.toArray();
            for (g = 0; g < features.length; g++) {
              selectedList.push(features[g]);
            }
          }
        }
        if (useLayer == false) {
          selectedList = selectedFeatures;
        }
        var browseList = [];
        for (var i = 0; i < selectedList.length; i++) {
          var browseAttribute = selectedList[i].attributes[fieldName];
          if (browseAttribute && isInArray(browseAttribute, browseList) == false){
            browseList.push(browseAttribute);
          }
        }
        sortedList = browseList.sort();
        for (var i = 0; i < sortedList.length; i++) {
          var numberOfRecords = 0;
          for (var j = 0; j < selectedList.length; j++) {
            if (selectedList[j].attributes[fieldName] == sortedList[i]) {
              numberOfRecords ++;
            }
          }
          var div = document.createElement("div");
          var searchCriteria;
          var languages = <?php echo json_encode($languages) ?>;
          var languagesTranslated = <?php echo json_encode($languagesTranslated) ?>;
          for (k = 0; k < languages.length; k++) {
            if (sortedList[i] == languages[k]) {
              searchCriteria = languagesTranslated[k];
            }
            else {
              searchCriteria = sortedList[i];
            }
          }
          div.innerHTML = "<input type='checkbox' name='" + browseDiv.id + "' class = 'checkbox' id='" + sortedList[i] +  "'>" + searchCriteria + " (" + numberOfRecords + ")";
          div.id = sortedList[i] + "div";
          browseDiv.appendChild(div);
        }
        browseDiv.innerHTML += "</form>";
      } //end createBrowse

      function createBrowseMultiple(selectedFeatures, useLayer) {
        var browseDiv = document.getElementById("author");
        selectedList = [];
        browseDiv.innerHTML = "<form action=''>";
        if (useLayer == true) {
          for (h = 0; h < listOfLayers.length; h++) {
            layer = listOfLayers[h];
            var features = layer.source.toArray();
            for (g = 0; g < features.length; g++) {
              selectedList.push(features[g]);
            }
          }
        }
        if (useLayer == false) {
          selectedList = selectedFeatures;
        }
        var browseList = [];
        for (var i = 0; i < selectedList.length; i++) {
          for (j = 0; j < listOfAuthorFields.length; j++) {
            var fieldName = listOfAuthorFields[j];
            var browseAttribute = selectedList[i].attributes[fieldName];
            if (browseAttribute && browseAttribute != " "){
              browseList.push(browseAttribute);
            }
          }
        }
        sortedByOccurence = orderByOccurrence(browseList);
        sortedList = sortedByOccurence.reverse();
        for (var i = 0; i < sortedList.length; i++) {
          var numberOfRecords = 0;
          var lastNameField;
          var lastName;
          var firstNameField;
          var firstName;
          for (var j = 0; j < selectedList.length; j++) {
            for (k = 0; k < listOfAuthorFields.length; k++) {
              var fieldName = listOfAuthorFields[k];
              if (selectedList[j].attributes[fieldName] == sortedList[i]) {
                lastNameField = listOfLastNameFields[k];
                firstNameField = listOfFirstNameFields[k];
                lastName = selectedList[j].attributes[lastNameField];
                firstName = selectedList[j].attributes[firstNameField];
                numberOfRecords ++;
              }
            }
          }
          var div = document.createElement("div");
          div.innerHTML = "<input type='checkbox' name='" + browseDiv.id + "' class = 'checkbox' id='" + sortedList[i] +  "'>" + lastName + ", " + firstName + " (" + numberOfRecords + ")";
          div.id = sortedList[i] + "div";
          browseDiv.appendChild(div);
        }
        browseDiv.innerHTML += "</form>";
      } //end createBrowseMultiple


      function recheckBoxes() {
        var checkedBoxes = document.getElementsByClassName("checkbox");
        //match the current checkboxes displayed with the old checkboxes that were checked before,
        //and recheck them
        for (var i = 0; i < checkedBoxes.length; i++) {
          for (var j = 0; j < checkedList.length; j++) {
            if (checkedBoxes[i].id == checkedList[j].id) {
              checkedBoxes[i].checked = true;
              textId = checkedBoxes[i].id + "div";
              document.getElementById(textId).style.color = "purple";
            }
          }
        }
      } //end recheckBoxes



    //-------------------------LEFT SIDEBAR-----------------------
    //paginate the results
    //divide the results into sets of 10 and attach click listeners to page numbers
      function paginate(recordList, includeDistance) {
        var paginateNumbers = document.getElementById("paginateDiv");
        if (recordList.length < 11) {
          paginateNumbers.innerHTML = "";
          createRecordDivs(recordList, includeDistance);
        }

        else {
          var pageList = [];
          var numberOfPages = Math.ceil(recordList.length / 10);
          for (i = 0; i < 10; i++) {
            pageList.push(recordList[i]);
          }
          createRecordDivs(pageList, includeDistance);
          paginateNumbers.innerHTML = "";
          var listOfNumbers = [];
          for (var i = 1; i < numberOfPages + 1; i++) {
            var div = document.createElement("div");
            div.tagName = i.toString();
            div.innerHTML = "<a href = #>" + i.toString() + "</a>";
            div.className = "paginate";
            paginateNumbers.appendChild(div);
            div.addEventListener("click", makeClickCallbackPaginate(i, recordList, includeDistance))
          }
        }
      } //end paginate

      function makeClickCallbackPaginate(i, recordList, includeDistance) {
        //when a page number is clicked, show the appropriate page of results
        function callbackPaginate(e) {
          var rangeStart = (i - 1) * 10;
          var rangeEnd = (i * 10);
          var pageList2 = [];
          for (j = rangeStart; j < rangeEnd; j++) {
            if (recordList[j]) {
              pageList2.push(recordList[j]);
            }
          }
          createRecordDivs(pageList2, includeDistance);
          linkRecordsToPopups();
        }
        return callbackPaginate;
      } //end makeClickCallbackPaginate

    //for each selected record, put it in a div that corresponds to its unique id number,
    //and print its field names and attributes

      function createRecordDivs(recordList, includeDistance) {
        document.getElementById("sortSelectDiv").style.display = "block";
        allRecords.innerHTML = "";
        if (recordList.length == 0) {
          allRecords.innerHTML = "<br/>" + '<?php echo $noResultsMatched ?>';
          return;
        }
        else {
          allRecords.innerHTML += '<?php echo $clickOnEntry ?>';
        }
        for (var i = 0; i < recordList.length; i++) {
          var idNo = recordList[i].attributes[uniqueID];
          var biggerDiv =  document.createElement("div");
          var div = document.createElement("div");
          if (recordList[i].attributes[uniqueID]) {
            div.id = "point" + idNo;
          }
          if (!recordList[i].attributes[uniqueID]) {
            div.id = "point0";
          }
          var outerRecordDiv =
          div.className = "record";
          biggerDiv.id = "big" + div.id;
          biggerDiv.className = "bigRecord";
          if (recordList[i].attributes[englishTitle] && recordList[i].attributes[englishTitle] != " ") {
            div.innerHTML += "<b>" +  '<?php echo $EnglishTitleTranslate ?>' + ": </b>" + recordList[i].attributes[englishTitle] + "<br/>";
          }
          if (recordList[i].attributes[turkishTitle] && recordList[i].attributes[turkishTitle] != " " && recordList[i].attributes[turkishTitle] != recordList[i].attributes[englishTitle] && recordList[i].attributes[turkishTitle] != recordList[i].attributes[otherTitle]) {
            div.innerHTML += "<b>" +  '<?php echo $TurkishTitleTranslate ?>' + ": </b>" + recordList[i].attributes[turkishTitle] + "<br/>";
          }
          if (recordList[i].attributes[otherTitle] && recordList[i].attributes[otherTitle] != " " && recordList[i].attributes[otherTitle] != recordList[i].attributes[englishTitle] ) {
            div.innerHTML += "<b>" +  '<?php echo $OtherTitleTranslate ?>' + ": </b>" + recordList[i].attributes[otherTitle] + "<br/>";
          }
          div.innerHTML += "<b>" +  '<?php echo $authors ?>' + ": </b>";
          var firstAuthor = listOfAuthorFields[0];
          if (recordList[i].attributes[firstAuthor]) {
            div.innerHTML += recordList[i].attributes[firstAuthor];
          }
          for (var j = 1; j < listOfAuthorFields.length; j++) {
            var authorField = listOfAuthorFields[j];
            if (recordList[i].attributes[authorField] && recordList[i].attributes[authorField] != " "){
              div.innerHTML += ", " + recordList[i].attributes[authorField];
            }
          }
          div.innerHTML += "<br/>";
          if (publication) {
            div.innerHTML += "<b>" +  '<?php echo $publicationTranslate ?>' + ": </b>" + recordList[i].attributes[publication] + "<br/>";
          }
          if (recordList[i].attributes["Distance"] && includeDistance == true) {
            div.innerHTML += "<b>" +  '<?php echo $distance ?>' + ": </b>"+ recordList[i].attributes["Distance"] + "<br/>";
          }

          if (pageStart) {
            div.innerHTML += '<?php echo $textStartsOnPage ?>' + " " + recordList[i].attributes[pageStart];
          }
          allRecords.appendChild(biggerDiv);
          biggerDiv.appendChild(div);

          //create citations and put them in a popup
          var allCitationDiv = document.createElement("div");
          allCitationDiv.id = "allCitation" + div.id;
          allCitationDiv.className = "allCitation";
          biggerDiv.appendChild(allCitationDiv);
          var closeDiv = document.createElement("div");
          closeDiv.innerHTML = "<a href = '#'> &#10761; </a>";
          closeDiv.className = "citationClose";
          closeDiv.id = "citationClose" + div.id;
          allCitationDiv.appendChild(closeDiv);
          var harvardDiv = document.createElement("div");
          allCitationDiv.appendChild(harvardDiv);
          var harvardTitleDiv = document.createElement("div");
          harvardTitleDiv.innerHTML = "<a href = '#'> Harvard" + "<span style = 'font-size:9px'>     &#x25BC; </span> </a>"
          harvardTitleDiv.href = "#";
          harvardTitleDiv.id = "harvard" + div.id;
          harvardDiv.appendChild(harvardTitleDiv);
          var harvardCitation = document.createElement("div");
          harvardCitation.id = "harvardCitation" + div.id;
          harvardCitation.className = "citation";
          harvardDiv.appendChild(harvardCitation);
          var mlaDiv = document.createElement("div");
          allCitationDiv.appendChild(mlaDiv);
          var mlaTitleDiv = document.createElement("div");
          mlaTitleDiv.innerHTML = "<a href = '#'> MLA" +  "<span style = 'font-size:9px'>     &#x25BC; </span> </a>"
          mlaTitleDiv.id = "MLA" + div.id;
          mlaDiv.appendChild(mlaTitleDiv);
          var mlaCitation = document.createElement("div");
          mlaCitation.id = "mlaCitation" + div.id;
          mlaCitation.className = "citation";
          mlaDiv.appendChild(mlaCitation);
          var chicagoDiv = document.createElement("div");
          allCitationDiv.appendChild(chicagoDiv);
          var chicagoTitleDiv = document.createElement("div");
          chicagoTitleDiv.innerHTML = "<a href = '#'> Chicago" +  "<span style = 'font-size:9px'>    &#x25BC; </span> </a>"
          chicagoTitleDiv.id = "Chicago" + div.id;
          chicagoDiv.appendChild(chicagoTitleDiv);
          var chicagoCitation = document.createElement("div");
          chicagoCitation.id = "chicagoCitation" + div.id;
          chicagoCitation.className = "citation";
          chicagoDiv.appendChild(chicagoCitation);
          var alaDiv = document.createElement("div");
          allCitationDiv.appendChild(alaDiv);
          var alaTitleDiv = document.createElement("div");
          alaTitleDiv.innerHTML = "<a href = '#'> ALA" +  "<span style = 'font-size:9px'>     &#x25BC; </span> </a>"
          alaTitleDiv.id = "ALA" + div.id;
          alaDiv.appendChild(alaTitleDiv);
          var alaCitation = document.createElement("div");
          alaCitation.id = "alaCitation" + div.id;
          alaCitation.className = "citation";
          alaDiv.appendChild(alaCitation);
          biggerDiv.innerHTML += "<button type='button' class = 'citeButton' tag ='" + i + "'id = 'cite" + div.id + "'>" + '<?php echo $exportCitation?>' + "</button>";
        }
      } //end createRecordDivs

      //sort records by the chosen field
      function sortByField() {
        var sortValue = document.getElementById("sortSelect").value;
        var field;
        if (sortValue == "Title") {
          field = "MainTitle"
        }
        if (sortValue == "Author") {
          field = listOfLastNameFields[0];
        }
        biblioSort = biblioFeatures3.sort(function(a, b){
          if(a.attributes[field] > b.attributes[field]){
            return 1;
          }
          else if (a.attributes[field] < b.attributes[field]){
            return -1;
          }
          else {
            return 0;
          }
        });
        paginate(biblioSort,false);
        linkRecordsToPopups();
      } //end sortByField


      //print all records, sorted by English title
      function printAllRecords() {
        biblioFeatures = layer.source.toArray();
        allRecords = document.getElementById("allRecords");
        var sortedAll = biblioFeatures.sort(function(a, b){
          if(a.attributes[englishField] > b.attributes[englishField]){
            return 1;
          }
          else if (a.attributes[englishField] < b.attributes[englishField]){
            return -1;
          }
          else {
            return 0;
          }
        });
        paginate(biblioFeatures2, false);
        linkRecordsToPopups();
      } //end printAllRecords

      //attach click listeners to divs
      function linkRecordsToPopups() {
        if (resultsShowing == false) {
          document.getElementById("resultsCard").click();
          resultsShowing = true;
        }
        zoomToLayer(biblioFeatures3, false);
        for (var i = 1; i < biblioFeatures2.length + 1; i++){
          var divName = "point" + i.toString();
          //open the corresponding popup when a result is clicked
          if (document.getElementById(divName)){
            document.getElementById(divName).addEventListener("click", makeClickCallback(i, biblioFeatures2))
          }
          //open the citation window when the button is clicked
          var buttonId = "citepoint" + i.toString();
          if (document.getElementById(buttonId)){
            document.getElementById(buttonId).addEventListener("click", makeClickCallback3(i));
          }

          //open each citation format when it's clicked
          var harvardTitle = "harvardpoint" + i.toString();
          if (document.getElementById(harvardTitle)){
            document.getElementById(harvardTitle).addEventListener("click", makeClickCallbackCitation(i, "harvard"));
          }
          var mlaTitle = "MLApoint" + i.toString();
          if (document.getElementById(mlaTitle)){
            document.getElementById(mlaTitle).addEventListener("click", makeClickCallbackCitation(i, "mla"));
          }

          var chicagoTitle = "Chicagopoint" + i.toString();
          if (document.getElementById(chicagoTitle)){
            document.getElementById(chicagoTitle).addEventListener("click", makeClickCallbackCitation(i, "chicago"));
          }
          var alaTitle = "ALApoint" + i.toString();
          if (document.getElementById(alaTitle)){
            document.getElementById(alaTitle).addEventListener("click", makeClickCallbackCitation(i, "ala"));
          }
          //close the citation window when the x is clicked
          var closeButton = "citationClosepoint" + i.toString();
          if (document.getElementById(closeButton)){
            document.getElementById(closeButton).addEventListener("click", makeClickCallbackClose(i));
          }
        }
      } //end linkRecordsToPopups


      //open popup when a record div is clicked
      function makeClickCallback(i, biblioFeatures2) {
        function callback(e){
          for (h = 0; h < listOfLayers.length; h++) {
            biblioFeatures22 = listOfBiblioFeatures2[h];
            for (k = 0; k < biblioFeatures22.length; k++) {
              if (biblioFeatures22[k].attributes[uniqueID] == i) {
                var popupTitle;
                if (biblioFeatures22[k].attributes[language] == "Turkish" && turkishTitle) {
                  popupTitle = turkishTitle;
                }
                if (biblioFeatures22[k].attributes[language] == "English") {
                  popupTitle = englishTitle;
                }
                if (biblioFeatures22[k].attributes[language] == "Other") {
                  popupTitle = otherTitle;
                }

                else {
                  if (otherTitle && otherTitle.length > 1) {
                    popupTitle = otherTitle;
                  }

                  else if (englishTitle && englishTitle.length > 1) {
                    popupTitle = englishTitle;
                  }

                }

                if (listOfLayers[h].geometryType == "polygon") {
                  var location = biblioFeatures22[k].geometry.centroid;
                }

                if (listOfLayers[h].geometryType == "point") {
                  var location = biblioFeatures22[k].geometry;
                }

                view.popup = new Popup({
                  title: biblioFeatures22[k].attributes[popupTitle],
                  location: location,
                  content: biblioFeatures22[k].attributes["PopupContent"]
                });
              }
            }
            view.popup.open()
          }
        }
        return callback;
      } //end makeClickCallback

      //display citations for individual formats
      function makeClickCallbackCitation(i, style) {
        function callbackCitation(e) {
          var citationID = style + "Citationpoint" + i;
          var citation = document.getElementById(citationID);
          if (!citation.style.display || citation.style.display == "none") {
            citation.style.display = "block";
            return;
          }
          if (citation.style.display == "block") {
            citation.style.display = "none";
            return;
          }
        }
        return callbackCitation;
      } //end makeClickCallbackCitation

      //create the citations
      function makeClickCallback3(i) {

        function callback3(e){
          var index = i - 1;
          var firstLastName = listOfLastNameFields[0];
          var firstFirstName = listOfFirstNameFields[0];
          var authorCitation = biblioFeatures2[index].attributes[firstLastName] + ", " + biblioFeatures2[index].attributes[firstFirstName];
          var secondLastName = listOfLastNameFields[1];
          var secondFirstName = listOfFirstNameFields[1];
          var thirdLastName = listOfLastNameFields[2];
          var thirdFirstName = listOfFirstNameFields[2];
          if (biblioFeatures2[index].attributes[thirdLastName] && biblioFeatures2[index].attributes[thirdLastName] != " ") {
            authorCitation += ", ";
          }

          if (biblioFeatures2[index].attributes[secondLastName] && biblioFeatures2[index].attributes[secondLastName] != " ") {
            for (var j = 1; j < listOfLastNameFields.length; j++) {
              var name = listOfAuthorFields[j];
              if (biblioFeatures2[index].attributes[name] != " " && biblioFeatures2[index].attributes[name]) {
                if (j != listOfLastNameFields.length - 1) {
                  var nextField = listOfLastNameFields[j];
                  var nextFirstName = listOfFirstNameFields[j];
                  var nextFieldPlusOne = listOfLastNameFields[j + 1];
                  if (biblioFeatures2[index].attributes[thirdLastName] == " " && biblioFeatures2[index].attributes[name]) {
                    authorCitation += " and " + biblioFeatures2[index].attributes[secondFirstName] + " " + biblioFeatures2[index].attributes[secondLastName].toProperCase();
                  }
                  else if (biblioFeatures2[index].attributes[thirdLastName] != " " && biblioFeatures2[index].attributes[nextFieldPlusOne] == " ") {
                    authorCitation += " and " +  biblioFeatures2[index].attributes[nextFirstName] + " " + biblioFeatures2[index].attributes[nextField];
                  }
                  else {
                    authorCitation += biblioFeatures2[index].attributes[nextFirstName] + " " + biblioFeatures2[index].attributes[nextField] + ", ";
                  }
                }
                if (j == listOfLastNameFields.length - 1) {
                  var nextField = listOfLastNameFields[j];
                  var nextFirstName = listOfFirstNameFields[j];
                  authorCitation += " and " + biblioFeatures2[index].attributes[nextFirstName] + " " + biblioFeatures2[index].attributes[nextField] + ".";
                }
              }
            }
          }
          var titleCitation;
          if (biblioFeatures2[index].attributes[language] == "English") {
            titleCitation = biblioFeatures2[index].attributes[englishTitle];
          }
          if (biblioFeatures2[index].attributes[language] == "Turkish" && biblioFeatures2[index].attributes[turkishTitle]) {
            titleCitation = biblioFeatures2[index].attributes[turkishTitle];
          }
          if (biblioFeatures2[index].attributes[language] != "Turkish" && biblioFeatures2[index].attributes[language] != "English") {
            if ( biblioFeatures2[index].attributes[otherTitle]) {
              titleCitation = biblioFeatures2[index].attributes[otherTitle];
            }
            else if ( biblioFeatures2[index].attributes[englishTitle]) {
              titleCitation = biblioFeatures2[index].attributes[englishTitle];
            }
          }
          var dateField = "";
          var chicagoCitation = authorCitation+ '. ' +
          '"' + titleCitation + '."' + biblioFeatures2[index].attributes[publication].italics() + ' no. ' +
          biblioFeatures2[index].attributes[volume] + ' (' + biblioFeatures2[index].attributes[dateField] + '): ' + biblioFeatures2[index].attributes[pageStart] +
          '-' + biblioFeatures2[index].attributes[pageEnd] + '.';
          var harvardCitation = authorCitation + '. ' + biblioFeatures2[index].attributes[dateField] +
          ", " + '"' + titleCitation + '", ' + biblioFeatures2[index].attributes[publication] + ", vol. " + biblioFeatures2[index].attributes[volume] +
          ", pp. " +  biblioFeatures2[index].attributes[pageStart] + '-' + biblioFeatures2[index].attributes[pageEnd] + '.'
          var mlaCitation = authorCitation + '. ' +
          '"' + titleCitation + '."' + biblioFeatures2[index].attributes[publication].italics() + ' vol. ' +
          biblioFeatures2[index].attributes[volume] + ", " + biblioFeatures2[index].attributes[dateField] + ", pp. " +  biblioFeatures2[index].attributes[pageStart] + '-' +
          biblioFeatures2[index].attributes[pageEnd] + '.';
          var apaCitation = authorCitation + '. ' +
          '(' + biblioFeatures2[index].attributes[dateField] + '). ' + titleCitation + '. ' +
          biblioFeatures2[index].attributes[publication].italics() + ', ' +  biblioFeatures2[index].attributes[volume].toString().italics() + ', ' +
          biblioFeatures2[index].attributes[pageStart] + '-' + biblioFeatures2[index].attributes[pageEnd] + '.';
          var allCitationDiv = "allCitationpoint" + i;
          var citeButtonDiv = "citepoint" + i;
          var harvardDiv = "harvardCitationpoint" + i;
          var harvardCitationDiv = document.getElementById(harvardDiv);
          harvardCitationDiv.innerHTML = "<div id = '" + harvardDiv + "text' style = 'width:100%; height:40px'>" + harvardCitation + "</div>";
          var chicagoDiv = "chicagoCitationpoint" + i;
          var chicagoCitationDiv = document.getElementById(chicagoDiv);
          chicagoCitationDiv.innerHTML = "<div id = '" + chicagoDiv + "text' style = 'width:100%; height:40px'>" + chicagoCitation + "</div>";
          var mlaDiv = "mlaCitationpoint" + i;
          var mlaCitationDiv = document.getElementById(mlaDiv);
          mlaCitationDiv.innerHTML = "<div id = '" + mlaDiv + "text' style = 'width:100%; height:40px'>" + mlaCitation + "</div>";
          var alaDiv = "alaCitationpoint" + i;
          var alaCitationDiv = document.getElementById(alaDiv);
          alaCitationDiv.innerHTML = "<div id = '" + alaDiv + "text' style = 'width:100%; height:40px'>" + apaCitation + "</div>";
          var harvardCopyDiv = document.createElement("div");
          var mlaCopyDiv = document.createElement("div");
          var chicagoCopyDiv = document.createElement("div");
          var alaCopyDiv = document.createElement("div");
          harvardCopyDiv.id = "copyDiv" + harvardCitationDiv.id;
          harvardCopyDiv.className = "copy";
          mlaCopyDiv.id = "copyDiv" + mlaCitationDiv.id;
          mlaCopyDiv.className = "copy";
          chicagoCopyDiv.id = "copyDiv" + chicagoCitationDiv.id;
          chicagoCopyDiv.className = "copy";
          alaCopyDiv.id = "copyDiv" + alaCitationDiv.id;
          alaCopyDiv.className = "copy";
          harvardCitationDiv.appendChild(harvardCopyDiv);
          mlaCitationDiv.appendChild(mlaCopyDiv);
          chicagoCitationDiv.appendChild(chicagoCopyDiv);
          alaCitationDiv.appendChild(alaCopyDiv);
          document.getElementById(allCitationDiv).style.display = "block";
          document.getElementById(citeButtonDiv).style.display = "none";
          var copyButtons = document.getElementsByClassName("copy");
          for (var j = 0; j < copyButtons.length; j++) {
            copyButtons[j].innerHTML = "<a href = '#'> Copy to clipboard </a>";
            var id = copyButtons[j].id;
            copyButtons[j].addEventListener("click", makeClickCallbackCopy(id));
          }
        }
        return callback3;
      }

      function makeClickCallbackCopy(id) {
        function callbackCopy(e) {
          var textId = id.substr(7) + "text";
          var copyText = document.getElementById(textId).innerHTML;
          copyToClip(copyText);
        }
        return callbackCopy;
      }

      //copy text to clipboard; this method preserves formatting
      function copyToClip(str) {
        function listener(e) {
          e.clipboardData.setData("text/html", str);
          e.clipboardData.setData("text/plain", str);
          e.preventDefault();
        }
        document.addEventListener("copy", listener);
        document.execCommand("copy");
        document.removeEventListener("copy", listener);
      };

      //hide the citation window when the x is clicked
      function makeClickCallbackClose(i) {
        function callbackClose(e) {
          var divID = "allCitationpoint" + i;
          var citeButtonDiv = "citepoint" + i;
          document.getElementById(divID).style.display = "none";
          document.getElementById(citeButtonDiv).style.display = "block";
        }
        return callbackClose;
      } //end makeClickCallbackClose


      //-------------------------DRAWING FUNCTIONS-----------------------------------
      function activateDraw(biblioFeatures2) {
        drawConfig.isDrawActive = true;
        clearPolygon();
        pointerDownListener = view.on("pointer-down", function(event) {
          event.stopPropagation();
          var point = createPoint(event);
          addVertex(point);
        });  //end point down

        pointerMoveListener = view.on("pointer-move", function(event) {
          if (drawConfig.activePolygon) {
            event.stopPropagation();
            var point = createPoint(event);
            updateFinalVertex(point);
          }
        }); //end point move

        //finish the shape and stop drawing on double click
        doubleClickListener = view.on("double-click", function(event) {
          if (searchByCircle == true) {
            completeCircle(event.mapPoint);
          }
          else {
            event.stopPropagation();
            searchArea = addVertex(event.mapPoint, true);
            if (!searchArea) {
              return null;
            }
            deactivateDraw();
            polygonButton.classList.toggle("esri-polygon-button-selected");
            drawing = false;
            //select the points that fall within the drawn polygon
            filterByGeometry(searchArea);
            linkRecordsToPopups();
          }
        }) //end double click
      }//end activte draw

      //stop drawing
      function deactivateDraw() {
        drawConfig.isDrawActive = false;
        pointerDownListener.remove();
        pointerMoveListener.remove();
        doubleClickListener.remove();
        drawConfig.activePolygon = null;
      } //end deactivate draw

      function createPoint(event) {
        return view.toMap(event);
      } //end createPoint

      function addVertex(point, isFinal) {
        var polygon = drawConfig.activePolygon;
        var ringLength;
        if (!polygon) {
          polygon = new Polygon({
            spatialReference: {
              wkid: 3857
            }
          });
          polygon.addRing([point, point]);
        } else {
          ringLength = polygon.rings[0].length;
          polygon.insertPoint(0, ringLength - 1, point);
        }
        drawConfig.activePolygon = polygon;
        return redrawPolygon(polygon, isFinal);
      }

      //remove the polygon or circle from the map
      function clearPolygon() {
        var polygonGraphic = view.graphics.find(function(graphic) {
          return graphic.geometry.type === "polygon";
        });
        if (polygonGraphic) {
          view.graphics.remove(polygonGraphic);
        }
        var polygonGraphic2 = view.graphics.find(function(graphic) {
          return graphic.geometry.type === "polyline";
        });
        if (polygonGraphic2) {
          view.graphics.remove(polygonGraphic2);
        }
        //clear the list of selected points
        if (selectedPointsGraphics) {
          for (var i = selectedPointsGraphics.length - 1; i>-1; i--) {
            selectedPoints.remove(selectedPointsGraphics[i]);
          }
        }
      } //end clear polygon

      function redrawPolygon(polygon, finished) {
        var geometry = finished ? geometryEngine.simplify(polygon) :
        polygon;

        if (!geometry && finished) {
          console.log(
            "Cannot finish polygon. It must be a triangle at minimum. Resume drawing..."
          );
          return null;
        }

        clearPolygon();

        var polygonGraphic = new Graphic({
          geometry: geometry,
          symbol: finished ? drawConfig.finishedSymbol : drawConfig.drawingSymbol
        });

        view.graphics.add(polygonGraphic);
        return geometry;
      } //end redraw polygon

      function updateFinalVertex(point) {
        var polygon = drawConfig.activePolygon.clone();
        var ringLength = polygon.rings[0].length;
        polygon.insertPoint(0, ringLength - 1, point);
        redrawPolygon(polygon);
      } //end update final vertex

      //create circle when done drawing
      function completeCircle(secondPoint) {
        deactivateDraw();
        clearPolygon();
        circleButton.classList.toggle("esri-circle-button-selected");
        searchByCircle = false;
        secondPoint = secondPoint;
        line.addPath([secondPoint]);
        line.spatialReference = view.spatialReference;
        view.graphics.add(new Graphic(line, lineSymbol));
        var lineDistance = geometryEngine.distance(firstPoint,secondPoint, distanceUnits);
        circle = new Circle({
          center: firstPoint,
          geodesic: false,
          radius: lineDistance,
          radiusUnit: distanceUnits
        });
        searchArea = circle;

        var circleSymb = new SimpleFillSymbol(SimpleFillSymbol.STYLE_NULL,
          new SimpleLineSymbol(
            SimpleLineSymbol.STYLE_SHORTDASHDOTDOT,
            new Color([105, 105, 105]),2), new Color([255, 255, 0, 0.25]));
            var graphic = new Graphic(circle, circleSymb);
            view.graphics.add(graphic);
            filterByGeometry(circle);
            linkRecordsToPopups();
            clicks = 0;
            return;
      } //end completeCircle
  }); //end all

  //this gets rid of the error "Bootstrap tooltips requires tether", since this script doesn't use Boostrap tooltips
  window.Tether = {};

</script>
</head>
<body class="claro">
  <div id = "header">
    <div id = "pageTitle">
      <div id = "title"> <?php echo $pageTitle ?> </div>
      <select id="translate_button" class="esri-widget-button esri-widget esri-interactive" style="float:right" onchange="location=this.value">
        <option value="init"><?php echo $viewPageIn ?></option>
        <?php $base_url = strtok($_SERVER["REQUEST_URI"], '?'); ?>
        <?php foreach($languageFiles as $transpage): ?>
          <option value="<?php echo $baseurl . '?lang=' . $transpage ?>"><?php echo $transpage ?></option>
        <?php endforeach ?>
      </select>
      <div id = "aboutDiv">
        <input type="checkbox" id="about" />
        <label for="about">
          <div id="about_button" class="esri-widget-button esri-widget esri-interactive" style = "float:right" ><?php echo $about ?></div>
        </label>
        <label for="about" class="about-bg" style = "width:100%"></label>
        <div class="about-content">
          <label for="about" class="close">
            <i class="fa fa-times" aria-hidden="true"></i>
          </label>
          <header>
            <h2><?php echo $about ?></h2>
          </header>
          <article class="about-text">
            <p><?php echo $abouttext ?></p>
            <hr />
            <p>Built by the <a href="https://github.com/upenndigitalscholarship/">Penn Libraries Digital Scholarship Team</a>. <a href="https://github.com/upenndigitalscholarship/bibmap">Get the Code</a>.</p>
          </article>
        </div>
      </div>
      <div id="searchBox" style = "margin-left:6px">
        <input id = "searchInput" type="text" class="searchTerm" placeholder= '<?php echo $enterSearchTerm ?>'>
        <button id = "searchBoxButton" type="submit" class="searchButton"> <?php echo $search ?>
        </button>
        <button id = "clearSearchButton" type="submit" class="searchButton"> <?php echo $clear ?>
        </button>
      </div>
    </div>
  </div>
  <div id = "biggestDiv">
    <div id="viewDiv">
    </div>
    <div id = "search" >
      <a id = "hideSearcha" href = "#"><div id = "hideSearch"> <?php echo $hide ?> </div></a>
      <div id = "innerSearch">
        <div id = "allButtons">
          <div id = "showAll" class = "searchButton allButton esri-widget-button esri-widget esri-interactive"> <?php echo $showAll ?> </div>
          <div id="clear-button" class="allButton esri-widget-button esri-widget esri-interactive">
            <?php echo $clearSelection ?>
          </div>
        </div>
        <div id="polygon-button" title = '<?php echo $select?> <?php echo $by ?> <?php echo $polygon ?>' class="esri-widget-button esri-widget esri-interactive">
          <span class="esri-icon-polygon"></span>
        </div>
        <div id = "distanceSelectDiv" style = "margin-bottom:10px">
          <select id="distance_select" class="selector">
            <option value="init"><?php echo $selectDistance ?></option>
          </select>
        </div>
        <div id="circle-button" title = '<?php echo $select ?> <?php echo $by ?> <?php echo $circle ?>' class="esri-widget-button esri-widget esri-interactive"><div style = "font-size:30px">&#9900;</div></div>
        <div id = "geocoder"></div>
        <div id = "polygonSearchTag">
          <span> <?php echo $geometrySearch ?> </span>
          <div id = "clearPolygonTag" style = "width:10px; height: 10px"><a href = "#" style = "color:#b9b5b5"> &#10761; </a></div>
        </div>
        <div id="accordion" role="tablist">
          <div class="card">
            <div class="card-header" role="tab" id="headingOne">
              <h5 class="mb-0">
                <a id = "introCard" data-toggle="collapse" href="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                  <?php echo $introduction ?>
                </a>
              </h5>
            </div>
            <div id="collapseOne" class="collapse show" role="tabpanel" aria-labelledby="headingOne" data-parent="#accordion">
              <div class="card-body">
                <?php echo $introText ?>
              </div>
            </div>
          </div> <!-- End intro card -->
          <div class="card">
            <div class="card-header" role="tab" id="headingTwo">
              <h5 class="mb-0">
                <a id = "searchCard" data-toggle="collapse" href="#collapseTwo" aria-expanded="true" aria-controls="collapseTwo">
                  <?php echo $search ?>
                </a>
              </h5>
            </div>
            <div id="collapseTwo" class="collapse" role="tabpanel" aria-labelledby="headingTwo" data-parent="#accordion">
              <div class="card-body">
                <a class="btn btn-primary" data-toggle="collapse" href="#multiCollapseExample1" aria-expanded="false" aria-controls="multiCollapseExample1"><?php echo $searchByLanguage ?></a>
                <div class = "border"></div>
                <div class="row">
                  <div class="col">
                    <div class="collapse multi-collapse" id="multiCollapseExample1">
                      <div class="card card-body">
                        <div id = "<?php echo $languageFieldName ?>" class = "browse"></div>
                        <button type="button" class = "filterButton" id = "filter1"><?php echo $search ?></button>
                      </div>
                    </div>
                  </div>
                </div>
                <a class="btn btn-primary" data-toggle="collapse" href="#multiCollapseExample2" aria-expanded="false" aria-controls="multiCollapseExample2"><?php echo $searchByPublication ?></a>
                <div class = "border"></div>
                <div class="row">
                  <div class="col">
                    <div class="collapse multi-collapse" id="multiCollapseExample2">
                      <div class="card card-body">
                        <div id = "<?php echo $publicationFieldName ?>" class = "browse"></div>
                        <button type="button" class = "filterButton" id = "filter2"><?php echo $search ?></button>
                      </div>
                    </div>
                  </div>
                </div>
                <a class="btn btn-primary" data-toggle="collapse" href="#multiCollapseExample3" aria-expanded="false" aria-controls="multiCollapseExample3"><?php echo $searchByAuthor?></a>
                <div class = "border"></div>
                <div class="row">
                  <div class="col">
                    <div class="collapse multi-collapse" id="multiCollapseExample3">
                      <div class="card card-body">
                        <div id = "author" class = "browse"></div>
                        <button type="button" class = "filterButton" id = "filter3"><?php echo $search ?></button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div> <!-- end search card -->
          <div class="card">
            <div class="card-header" role="tab" id="headingThree">
              <h5 class="mb-0">
                <a id = "resultsCard" data-toggle="collapse" href="#collapseThree" aria-expanded="true" aria-controls="collapseThree">
                  <?php echo $results ?>
                </a>
              </h5>
            </div>
            <div id="collapseThree" class="collapse" role="tabpanel" aria-labelledby="headingThree" data-parent="#accordion">
              <div class="card-body">

                <span></span>
                <br/>
                <div id = "sortSelectDiv">
                  <select id = "sortSelect">
                    <option value = "init"><?php echo $sortByField ?></option>
                    <option value = "Title"><?php echo $title ?></option>
                    <option value = "Author"><?php echo $author ?></option>
                    <option value = "Date"><?php echo $date ?></option>
                  </select>
                </div>
                <div id = "allRecords"></div>
                <div id = "paginateDiv"></div>
              </div>
            </div>
          </div> <!--end results card -->
        </div> <!--end accordion -->
      </div>
    </div>
  </div>
  <script src="https://code.jquery.com/jquery-3.1.1.slim.min.js" integrity="sha384-A7FZj7v+d/sdmMqp/nOQwliLvUsJfDHW+k9Omg/a/EheAdgtzNs3hpfag6Ed950n" crossorigin="anonymous"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
  <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>
</body>
<script>
</script>
</html>
