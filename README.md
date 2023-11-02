[geophp.net](https://geophp.net "GeoPHP homepage")

About this fork
---------------

The primary objective of this fork is to modernize the source code by transitioning to a contemporary version of PHP, incorporating namespaces and introducing new features.


GeoPHP README
-------------

GeoPHP is a open-source native PHP library for doing geometry operations. It is written entirely in PHP and 
can therefore run on shared hosts. It can read and write a wide variety of formats: WKT (including EWKT), WKB (including EWKB), GeoJSON, 
KML, GPX, and GeoRSS. It works with all Simple-Feature geometries (Point, LineString, Polygon, GeometryCollection etc.)
and can be used to get centroids, bounding-boxes, area, and a wide variety of other useful information. 

geoPHP also helpfully wraps the GEOS php extension so that applications can get a transparent performance 
increase when GEOS is installed on the server. When GEOS is installed, geoPHP also becomes
fully compliant with the OpenGIS® Implementation Standard for Geographic information. With GEOS you get the 
full-set of openGIS functions in PHP like Union, IsWithin, Touches etc. This means that applications
get a useful "core-set" of geometry operations that work in all environments, and an "extended-set"of operations 
for environments that have GEOS installed. 

See the 'getting started' section below for references and examples of everything that geoPHP can do.

This project is currently looking for co-maintainers. If you think you can help out, please send me a 
message. Forks are also welcome, please issue pull requests and I will merge them into the main branch.

Credit
-------------------------------------------------

GeoPHPwithFeatures Maintainer: Yermo Lamers <https://github.com/yermo> 

GeoPHP Maintainer: Patrick Hayes

Additional Contributors:

 * GeoMemes Research (<http://www.geomemes.com>)
 * HighWire Press (<http://www.highwire.org>) and GeoScienceWorld (<http://www.geoscienceworld.org>)
 * Arnaud Renevier (gisconverter.php) <https://github.com/arenevier/gisconverter.php>
 * Dave Tarc <https://github.com/dtarc>
 * Elliott Hunston (documentation) <https://github.com/ejh>

This library is open-source and dual-licensed under both the Modified BSD License and GPLv2. Either license may be used at your option.           
