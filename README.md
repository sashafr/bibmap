# Mapped Bibliography

**This project is actively in development. 10/3/2017**



## Getting Started


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
