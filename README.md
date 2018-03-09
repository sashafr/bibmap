# Mapped Bibliography

This project creates a simple interface that can be applied on top of a ArcGIS webmap which contains polygons which spatially represent a publication. The necessary structure of the webmap's attribute table is described below. The interface includes built in functionality to display the map in several different languages (our project developed this for Turkish and English).

_At present, this webmap is not optimized for mobile access_

### Prerequisites

- ArcGIS Online account
- LAMP set-up with a location to host and serve project code (with LAMP, it can be unzipped directly in the `html` folder or in any subfolder). _Note that for this project, MySQL is actually not necessary since the data lives in ArcGIS Online._
- Shapefiles containing spatial bibliography (see next section to prep your data)
- git (if cloning)

#### _Prepping Your Data_
This code gets data from a webmap in ArcGIS online, which serves as the database.

1. All layers should be generated in ArcGIS for Desktop as shapefiles. AGOL can only accept shapefiles of 1000 features or less, so files with more features than that need to be split into sets of 1000.  The application is designed to accommodate as many layers as you need and users won't be able to tell.  Split the shapefile numerically by unique ID (i.e. numbers 1-1000, 1001-2000, etc.).  The layers can be either points or polygons, or a mix of the two. The attribute table should contain the following fields:
    - OID or some other number that starts at 1 and that is not reset when you split the layer (i.e. don't use FID because each layer will restart the FID values at 0, so there will be more than one feature with the same FID).  If you want to do both point and polygon layers together, ensure that all of them have a unique value in this field.
    - At least one field with the author's full name (if there are multiple authors, one field per author)
    - At least one field with the author's first name (if there are multiple authors, one field per author)
    - At least one field with the author's last name (if there are multiple authors, one field per author)
    - English title
    - Turkish title
    - Other language title
    - Publication title
    - Language the article is in
    - Page on which the article starts
    - Page on which the article ends
    - Volume of the publication
    - Number of the publication volume (if not applicable, leave blank)
    - Date of publication
2. Compress all shapefiles into a zip file.
3. In your ArcGIS Online account, create a new WebMap and add a layer "from a File", then upload the zipped shapefiles.  Do NOT select the default, which is to generalize features for web display; select the option to keep original features.  _For the map to be viewed by the public, all layers and the map itself must be made public using the sharing settings in AGOL._  

## Getting Started

1. Upload zipped Github repo to the location where you would like to serve the files OR `cd` to folder where you would like to serve the map and `git clone https://github.com/upenndigitalscholarship/bibmap.git`.
2. Structure of Project:
    - `map.php` - map interface
    - `en.php` - English dictionary of text appearing on interface (THIS FILE IS MANDATORY, loaded in map.php if no language given)
    - `tr.php` - Turkish dictionary of text appearing on interface (additional language files could be added, see below)
    - `config.php` - general site configurations
    - `style.css` - styles
3. To link to a map, the webmap ID, which can be found at the end of the URL in AGOL, needs to be referenced as the portalItem when a new webmap is created. All layers that are set as visible in AGOL will appear on the map as soon as you link to the webmap as a portal item, but the individual layers must also be input using the variable $layerIDList described above.
4. To add additional language files, copy en.php and rename to _xLANGUAGE_.php, translate all text fields, and then put *file name only* WITHOUT .php file extension in config file.

There is a limited amount of editing, such as adding features or modifying existing features, that can be done in AGOL.  Other changes such as adding fields needs to be done in ArcGIS for Desktop. You will then need to re upload the layers, which will mean they will have new IDs that need to be changed in the code.


Indices of the relevant fields should be input into config.php.  Indices of the fields can be found in the attribute table in ArcGIS for Desktop, in which case they are accurate (i.e. the first field is index 0).  In the attribute table in AGOL the apparent index of a field is 2 less than its actual index because AGOL doesn't show the FID field or the Shape field, which are indices 0 and 1--so the first field in AGOL, which looks like index 0, is actually index 2.  


There are two additional variables in the dictionaries.  $polygonZoomLevel is the zoom level at which the points change to polygons (the sites are polygons, but will be represented as points when the map is zoomed out past a certain point).  The points change to polygons by changing the layer's renderer between two different symbols.  If all features are points, ignore this value.

$layerIDList is the array of layers that the map is using to search and display.  A layer's ID contains its name in AGOL but it also contains additional numbers that appear to be random.  The layer ids that need to go in the array come from uncommenting the lines:

for (var i = 0; i < allLayers.length; i++) {
console.log(allLayers[i].id);
}  

The ids of the divs that contain the browsable checkboxes in the right sidebar need to match the name of the field that will be searched for those terms.  So for example the id of the div that contains the checkboxes for title, should be the name of the field that contains the title.  When the div id is "author", the browse will search across all author fields (regardless of what those fields are called, as long as they're entered in the array of author field indices).  


## Built With

- [ArcGIS API for Javascript version 4.4](https://developers.arcgis.com/javascript/latest/guide/)
- [Bootstrap 4.0.0](https://getbootstrap.com/)

## Authors

* **Rachel Cohen** - *Project Developer*
* **Sasha Renninger** - *Project Supervisor*

## Acknowledgments

The ArcGIS API for Javascript 4.4 API Reference website was essential for the creation of this site:
https://developers.arcgis.com/javascript/latest/api-reference/index.html

The code for drawing a spatial query was taken from the ArcGIS Javascript for API Sandbox:
https://developers.arcgis.com/javascript/latest/sample-code/sandbox/index.html?sample=draw-spatial-query

The code for watching for changes in extent was also taken from the ArcGIS Javascript for API Sandbox:
https://developers.arcgis.com/javascript/latest/sample-code/sandbox/index.html?sample=watch-for-changes

The citation formats come from Purdue's Online Writing Lab:
https://owl.english.purdue.edu/owl/

This bootstrap code that was used to create the accordion search bar:
https://getbootstrap.com/docs/4.0/components/collapse/

The code for copying to clipboard can be found here:
https://jsfiddle.net/jdhenckel/km7prgv4/3/
