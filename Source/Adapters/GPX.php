<?php

namespace GeoPHP\Adapters;

use DOMDocument;
use Exception;
use GeoPHP\Geometry\Geometry;
use GeoPHP\Geometry\GeometryCollection;
use GeoPHP\Geometry\LineString;
use GeoPHP\Geometry\Point;
use GeoPHP\GeoPHP;
use InvalidText;

/*
 * Copyright (c) Patrick Hayes
 *
 * This code is open-source and licenced under the Modified BSD License.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * PHP Geometry/GPX encoder/decoder
 */
class GPX extends GeoAdapter
{
    private $namespace = false;
    private $nss       = ''; // Name-space string. eg 'georss:'

    /**
     * Read GPX string into geometry objects
     *
     * @param string $gpx A GPX string
     *
     * @return Geometry|GeometryCollection
     */
    public function read($gpx)
    {
        return $this->geomFromText($gpx);
    }

    /**
     * Serialize geometries into a GPX string.
     *
     * @param Geometry $geometry
     *
     * @return string The GPX string representation of the input geometries
     */
    public function write(Geometry $geometry, $namespace = false)
    {
        if ($geometry->isEmpty()) return null;
        if ($namespace)
        {
            $this->namespace = $namespace;
            $this->nss = $namespace.':';
        }
        return '<'.$this->nss.'gpx creator="geoPHP" version="1.0">'.$this->geometryToGPX($geometry).'</'.$this->nss.'gpx>';
    }

    public function geomFromText($text)
    {
        // Change to lower-case and strip all CDATA
        $text = preg_replace_callback("/(<\/?[^!][^>]+)/", function ($matches)
        {
            return strtolower($matches[1]);
        }, $text);
        $text = preg_replace('/<!\[cdata\[(.*?)\]\]>/s', '', $text);

        // Load into DOMDocument
        $xmlobj = new DOMDocument();
        @$xmlobj->loadXML($text);
        if ($xmlobj === false)
        {
            throw new Exception("Invalid GPX: ".$text);
        }

        $this->xmlobj = $xmlobj;
        try
        {
            $geom = $this->geomFromXML();
        }
        catch (InvalidText $e)
        {
            throw new Exception("Cannot Read Geometry From GPX: ".$text);
        }
        catch (Exception $e)
        {
            throw $e;
        }

        return $geom;
    }

    protected function geomFromXML()
    {
        $geometries = [];
        $geometries = array_merge($geometries, $this->parseWaypoints());
        $geometries = array_merge($geometries, $this->parseTracks());
        $geometries = array_merge($geometries, $this->parseRoutes());

        if (empty($geometries))
        {
            throw new Exception("Invalid / Empty GPX");
        }

        return geoPHP::geometryReduce($geometries);
    }

    protected function childElements($xml, $nodename = '')
    {
        $children = [];
        foreach ($xml->childNodes as $child)
        {
            if ($child->nodeName == $nodename)
            {
                $children[] = $child;
            }
        }
        return $children;
    }

    protected function parseWaypoints()
    {
        $points = [];
        $wpt_elements = $this->xmlobj->getElementsByTagName('wpt');
        foreach ($wpt_elements as $wpt)
        {
            $lat = $wpt->attributes->getNamedItem("lat")->nodeValue;
            $lon = $wpt->attributes->getNamedItem("lon")->nodeValue;
            // create the point
            $point = new Point($lon, $lat);
            // get the wpt name (if exists)
            $name = $wpt->getElementsByTagName('name');
            if ($name->length > 0)
            {
                $point->name = $name->item(0)->nodeValue;
            }
            // add this point
            $points[] = $point;
        }
        return $points;
    }

    protected function parseTracks()
    {
        $lines = [];
        $trk_elements = $this->xmlobj->getElementsByTagName('trk');
        foreach ($trk_elements as $trk)
        {
            $components = [];
            foreach ($this->childElements($trk, 'trkseg') as $trkseg)
            {
                foreach ($this->childElements($trkseg, 'trkpt') as $trkpt)
                {
                    $lat = $trkpt->attributes->getNamedItem("lat")->nodeValue;
                    $lon = $trkpt->attributes->getNamedItem("lon")->nodeValue;
                    // get elevation (altitude)
                    $ele = $trkpt->getElementsByTagName('ele');
                    $ele = $ele->length > 0 ? (float)$ele->item(0)->nodeValue : null;
                    // create the track point
                    $components[] = new Point($lon, $lat, $ele);
                }
            }
            if ($components)
            {
                $lines[] = new LineString($components);
            }
        }
        return $lines;
    }

    protected function parseRoutes()
    {
        $lines = [];
        $rte_elements = $this->xmlobj->getElementsByTagName('rte');
        foreach ($rte_elements as $rte)
        {
            $components = [];
            foreach ($this->childElements($rte, 'rtept') as $rtept)
            {
                $lat = $rtept->attributes->getNamedItem("lat")->nodeValue;
                $lon = $rtept->attributes->getNamedItem("lon")->nodeValue;
                $components[] = new Point($lon, $lat);
            }
            $lines[] = new LineString($components);
        }
        return $lines;
    }

    protected function geometryToGPX($geom)
    {
        $type = strtolower($geom->getGeomType());
        switch ($type)
        {
            case 'point':
                return $this->pointToGPX($geom);
                break;
            case 'linestring':
                return $this->linestringToGPX($geom);
                break;
            case 'polygon':
            case 'multipoint':
            case 'multilinestring':
            case 'multipolygon':
            case 'geometrycollection':
                return $this->collectionToGPX($geom);
                break;
        }
    }

    private function pointToGPX($geom)
    {
        return '<'.$this->nss.'wpt lat="'.$geom->getY().'" lon="'.$geom->getX().'" />';
    }

    private function linestringToGPX($geom)
    {
        $gpx = '<'.$this->nss.'trk><'.$this->nss.'trkseg>';

        foreach ($geom->getComponents() as $comp)
        {
            $gpx .= '<'.$this->nss.'trkpt lat="'.$comp->getY().'" lon="'.$comp->getX().'" />';
        }

        $gpx .= '</'.$this->nss.'trkseg></'.$this->nss.'trk>';

        return $gpx;
    }

    public function collectionToGPX($geom)
    {
        $gpx = '';
        $components = $geom->getComponents();
        foreach ($geom->getComponents() as $comp)
        {
            $gpx .= $this->geometryToGPX($comp);
        }

        return $gpx;
    }

}
