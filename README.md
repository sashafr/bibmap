# Mapped Bibliography

**This project is actively in development. 10/3/2017**



## Getting Started


To get the code on your machine, download a copy and link to the server.  There are four files.  map.php contains all information for the map.  To generate translated copies of the page, this site uses a dictionary model, where variables are defined with English and Turkish text (and this can be expanded to whatever language you want).  en.php has all the variables in English; tr.php has the same variables in Turkish.  style.css contains all the style information.

There are two additional variables in the dictionaries.  $polygonZoomLevel is the zoom level at which the points change to polygons (the sites are polygons, but will be represented as points when the map is zoomed out past a certain point).  The points change to polygons by changing the layer's renderer between two different symbols.  

$layerIDList is the array of layers that the map is using to search and display.  Shapefiles uploaded to AGOL are limited to 1000 features.  If a dataset has more than that, those features will need to be split into multiple shapefiles.  This script is designed to take as many layers as are in the array.  It doesn't matter how the features are split.  All the processes will be done each layer in the list, and to the user, it will appear as though all of these features are a single layer.  The layer ids that need to go in the array come from uncommenting the lines:

for (var i = 0; i < allLayers.length; i++) {
console.log(allLayers[i].id);
}  

The layer ids that need to go in the array will contain the name of the layer in AGOL, but will also contain additional numbers.  

### Prerequisites

This project uses ArcGIS API for Javascript version 4.4, as well as the accompanying stylesheet.  Current documentation available on ESRI's website is for version 4.5 but there appear to have been few changes between 4.4 and 4.5.  You should NOT use anything from version 3.22 as it is very different from 4.4.

This code gets data from a webmap in ArcGIS online, which serves as the database.  All layers should be generated in ArcGIS for Desktop as shapefiles, compressed into zip files, and loaded into a map in the user's account on AGOL.  For the map to be viewed by the public, all layers and the map itself must be made public using the sharing settings in AGOL.  

To link to a map, the webmap ID, which can be found at the end of the URL in AGOL, needs to be referenced as the portalItem when a new webmap is created (line 114). All layers that are set as visible in AGOL will appear on the map as soon as you link to the webmap as a portal item, but the individual layers must also be input using the variable $layerIDList described above.

There is a limited amount of editing, such as adding features or modifying existing features, that can be done in AGOL.  Other changes such as adding fields needs to be done in ArcGIS for Desktop. You will then need to re upload the layers, which will mean they will have new IDs that need to be changed in the code.


Starting on line 308, the fields are defined by field name or index.  If there are multiple author fields, they need to go in an array.  All of the fields used in the popups or the citations should also be defined here.  You can get the index and name of each field by uncommenting lines 301-303, or by looking at the table in AGOL.  However, the API adds a couple fields to the front of the table, so indices in the script will not match the indices of the fields in AGOL.

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
