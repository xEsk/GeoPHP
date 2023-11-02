<?php

namespace GeoPHP\Adapters;

use Exception;
use GeoPHP\Geometry\Geometry;
use InvalidText;
use ZipArchive;

/*
 * Copyright (c) Patrick Hayes
 * Copyright (c) 2010-2011, Arnaud Renevier
 * Copyright (c) 2016, Beecubu
 *
 * This code is open-source and licenced under the Modified BSD License.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * PHP Geometry/KML encoder/decoder
 *
 * Mainly inspired/adapted from OpenLayers( http://www.openlayers.org )
 *   Openlayers/format/WKT.js
 *
 * @package    sfMapFishPlugin
 * @subpackage GeoJSON
 * @author     Camptocamp <info@camptocamp.com>
 */
class KMZ extends KML
{
    /**
     * Serialize geometries into a KML string.
     *
     * @param Geometry $geometry
     *
     * @return string The KML string representation of the input geometries
     */
    public function write(Geometry $geometry, $namespace = false)
    {
        $path = tempnam(sys_get_temp_dir(), 'mkmz_'.uniqid());
        // create the kmz
        $z = new \ZipArchive();
        $z->open($path, ZIPARCHIVE::CREATE);
        $z->addFromString('doc.kml', parent::write($geometry, $namespace));
        $z->close();
        // get the zip content
        $kmz = file_get_contents($path);
        // delete the temporary file
        unlink($path);
        // the zip data
        return $kmz;
    }

    public function geomFromText($text)
    {
        $path = tempnam(sys_get_temp_dir(), 'mkmz_'.uniqid());
        file_put_contents($path, $text);
        // get the compressed kml
        try
        {
            return parent::geomFromText(file_get_contents('zip://'.$path.'#doc.kml'));
        }
        catch (Exception $e)
        {
            return null;
        }
        finally // delete temporary data
        {
            @unlink($path);
        }
    }
}
