# Mapped Bibliography

This project creates a simple interface that can be applied on top of a ArcGIS webmap which contains polygons which spatially represent a publication. The necessary structure of the webmap's attribute table is described below. The interface includes built in functionality to display the map in several different languages (our project developed this for Turkish and English).

### Prerequisites

- ArcGIS Online account
- LAMP set-up with a location to host and serve project code (with LAMP, it can be unzipped directly in the `html` folder or in any subfolder). _Note that for this project, MySQL is actually not necessary since the data lives in ArcGIS Online._
- Shapefiles containing spatial bibliography (see next section to prep your data).
- git (if cloning)

#### _Prepping Your Data_
This code gets data from a webmap in ArcGIS online, which serves as the database.

1. All layers should be generated in ArcGIS for Desktop as shapefiles. ???It is recommended that your features be drawn as polygons.??? The attribute table should contain the following fields:
  - Field 1
  - Field 2
2. Compress all shapefiles into a zip file.
3. In your ArcGIS Online account, under the My Content tab, select "Add Item". and upload your zipped shapefiles into a WebMap. _For the map to be viewed by the public, all layers and the map itself must be made public using the sharing settings in AGOL._  

## Getting Started

1. Upload zipped Github repo to the location where you would like to serve the files OR `cd` to folder where you would like to serve the map and `git clone https://github.com/upenndigitalscholarship/bibmap.git`.
2. Structure of Project:
  - `map.php` - map interface
  - `en.php` - English dictionary of text appearing on interface
  - `tr.php` - Turkish dictionary of text appearing on interface
  - `style.css` - styles
To link to a map, the webmap ID, which can be found at the end of the URL in AGOL, needs to be referenced as the portalItem when a new webmap is created (line 114). All layers that are set as visible in AGOL will appear on the map as soon as you link to the webmap as a portal item, but the individual layers must also be input using the variable $layerIDList described above.

There is a limited amount of editing, such as adding features or modifying existing features, that can be done in AGOL.  Other changes such as adding fields needs to be done in ArcGIS for Desktop. You will then need to re upload the layers, which will mean they will have new IDs that need to be changed in the code.


Starting on line 308, the fields are defined by field name or index.  If there are multiple author fields, they need to go in an array.  All of the fields used in the popups or the citations should also be defined here.  You can get the index and name of each field by uncommenting lines 301-303, or by looking at the table in AGOL.  However, the API adds a couple fields to the front of the table, so indices in the script will not match the indices of the fields in AGOL.

## Getting Started

There are two additional variables in the dictionaries.  $polygonZoomLevel is the zoom level at which the points change to polygons (the sites are polygons, but will be represented as points when the map is zoomed out past a certain point).  The points change to polygons by changing the layer's renderer between two different symbols.  

$layerIDList is the array of layers that the map is using to search and display.  Shapefiles uploaded to AGOL are limited to 1000 features.  If a dataset has more than that, those features will need to be split into multiple shapefiles.  This script is designed to take as many layers as are in the array.  It doesn't matter how the features are split.  All the processes will be done each layer in the list, and to the user, it will appear as though all of these features are a single layer.  The layer ids that need to go in the array come from uncommenting the lines:

for (var i = 0; i < allLayers.length; i++) {
console.log(allLayers[i].id);
}  

The layer ids that need to go in the array will contain the name of the layer in AGOL, but will also contain additional numbers.  

### Prerequisites

This code gets data from a webmap in ArcGIS online, which serves as the database.  All layers should be generated in ArcGIS for Desktop as shapefiles, compressed into zip files, and loaded into a map in the user's account on AGOL.  For the map to be viewed by the public, all layers and the map itself must be made public using the sharing settings in AGOL.  

To link to a map, the webmap ID, which can be found at the end of the URL in AGOL, needs to be referenced as the portalItem when a new webmap is created (line 114). All layers that are set as visible in AGOL will appear on the map as soon as you link to the webmap as a portal item, but the individual layers must also be input using the variable $layerIDList described above.

There is a limited amount of editing, such as adding features or modifying existing features, that can be done in AGOL.  Other changes such as adding fields needs to be done in ArcGIS for Desktop. You will then need to re upload the layers, which will mean they will have new IDs that need to be changed in the code.


Starting on line 308, the fields are defined by field name or index.  If there are multiple author fields, they need to go in an array.  All of the fields used in the popups or the citations should also be defined here.  You can get the index and name of each field by uncommenting lines 301-303, or by looking at the table in AGOL.  However, the API adds a couple fields to the front of the table, so indices in the script will not match the indices of the fields in AGOL.
=======
To get the code on your machine, download a copy and link to the server.  There are three files.  maptest.html is a complete copy of the code for the site in English.  map-tr.html is (or will be) the exact same thing but in Turkish.  style.css contains all the style information.  

### Prerequisites

This project uses ArcGIS API for Javascript version 4.4, as well as the accompanying stylesheet.  Current documentation available on ESRI's website is for version 4.5 but there appear to have been few changes between 4.4 and 4.5.  You should NOT use anything from version 3.22 as it is very different from 4.4.

This code gets data from a webmap in ArcGIS online, which serves as the database.  All layers should be generated in ArcGIS for Desktop as shapefiles, compressed into zip files, and loaded into a map in the user's account on AGOL.  For the map to be viewed by the public, all layers and the map itself must be made public using the sharing settings in AGOL.  Styling should also be done in AGOL.

To link to a map, the webmap ID, which can be found at the end of the URL in AGOL, needs to be referenced as the portalItem when a new webmap is created (line 114).  The map's layers also need to be referenced using findLayerById (staring on line 283.  All layers that are set as visible in AGOL will appear on the map as soon as you link to the webmap as a portal item, but the individual layers must also be input.  To get the IDs of the layers, uncomment lines 277-279.  A layer's ID will contain its name in AGOL but will also contain words and/or additional numbers that aren't necessarily evident in AGOL.  

There is a limited amount of editing, such as adding features or modifying existing features, that can be done in AGOL.  Other changes such as adding fields needs to be done in ArcGIS for Desktop. You will then need to re upload the layers, which will mean they will have new IDs that need to be changed in the code.

Because AGOL limits shapefiles to 1000 features, this code is designed to take as input a dataset that is split across multiple layers.  There can be as many or as few layers as the user desires.  Each layer needs to be identified by its ID and then pushed into the array of layers.  If there is only one layer, just push the one layer.  

For the sake of readability, the map represents polygons as points until the user zooms in to a certain point.  However the polygons are divided up among layers, their centroids (generated in ArcGIS) should be similarly divided.  The number of centroid layers, and the number of features in each centroid layer, should be the same as the polygon layers.  The centroid layers need to be loaded into AGOL, identified by ID in the code and pushed into the array of centroid layers.

The visibility of the polygon and centroid layers, and the level at which they switch, is controlled in AGOL using Set Visibility Range, which can be accessed by clicking on the ... next to the layer.  ESRI suggests scales or you can set your own.  Currently the centroids are set to display from the "Continent" level to the "County" level and the polygons are set to display from the "County" level to the "Room" level.   

Starting on line 308, the fields are defined by field name or index.  If there are multiple author fields, they need to go in an array.  All of the fields used in the popups or the citations should also be defined here.  You can get the index and name of each field by uncommenting lines 301-303, or by looking at the table in AGOL.  However, the API adds a couple fields to the front of the table, so indices in the script will not match the indices of the fields in AGOL.

The text that displays in the right sidebar, which explains a bit about the map and how to use it, can be set on line 77.

map-tr.html contains a complete copy of the site in Turkish, which is a separate page.  All English words can be replaced with Turkish translations here.  A button on both sites allows you to switch back and forth.

The ids of the divs that contain the browsable checkboxes in the right sidebar need to match the name of the field that will be searched for those terms.  So for example the id of the div that contains the checkboxes for title, should be the name of the field that contains the title.  When the div id is "author", the browse will search across all author fields.  


## Built With

This site was built using ArcGIS API for Javascript version 4.4.

This project also uses bootstrap 4.0 to create the accordion search menus.  Instructions for setting up bootstrap can e found here: https://getbootstrap.com/docs/4.0/getting-started/introduction/.  

## Authors

* **Rachel Cohen** - *Project Developer*

## Acknowledgments

The ArcGIS API for Javascript 4.4 API Reference website was essential for the creation of this site:
https://developers.arcgis.com/javascript/latest/api-reference/index.html


The code for drawing a spatial query was taken from the ArcGIS Javascript for API Sandbox:
https://developers.arcgis.com/javascript/latest/sample-code/sandbox/index.html?sample=draw-spatial-query

The code for watching for changes in extent was also taken from the ArcGIS Javascript for API Sandbox:
https://developers.arcgis.com/javascript/latest/sample-code/sandbox/index.html?sample=watch-for-changes

The citation formats come from Purdue's Online Writing Lab:
https://owl.english.purdue.edu/owl/

This boostrap code was used to create the accordion search bar:
https://getbootstrap.com/docs/4.0/components/collapse/

The code for copying to clipboard can be found here:
https://jsfiddle.net/jdhenckel/km7prgv4/3/
