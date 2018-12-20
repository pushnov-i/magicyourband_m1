<?php
/**
 * Innoexts
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@innoexts.com so we can send you a copy immediately.
 * 
 * @category    Innoexts
 * @package     Innoexts_Core
 * @copyright   Copyright (c) 2014 Innoexts (http://www.innoexts.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Math helper
 * 
 * @category   Innoexts
 * @package    Innoexts_Core
 * @author     Innoexts Team <developers@innoexts.com>
 */
class Innoexts_Core_Helper_Math 
    extends Mage_Core_Helper_Abstract 
{
    /**
     * Get distance units
     * 
     * @return array
     */
    public function getDistanceUnits()
    {
        return array(
            'mi' => array(
                'name'  => 'Mile', 
                'ratio' => 1
            ), 
            'nmi' => array(
                'name'  => 'Nautical Mile', 
                'ratio' => 0.8684
            ), 
            'km' => array(
                'name'  => 'Kilometer', 
                'ratio' => 1.609344
            ), 
        );
    }
    /**
     * Get distance
     * 
     * @param float $latitude1
     * @param float $longitude1
     * @param float $latitude2
     * @param float $longitude2
     * @param string $unitCode
     * 
     * @return float
     */
    public function getDistance($latitude1, $longitude1, $latitude2, $longitude2, $unitCode = 'mi') {
        $longitudeDelta = $longitude1 - $longitude2;
        $distance = 60 * 1.1515 * rad2deg(acos(
            (sin(deg2rad($latitude1)) * sin(deg2rad($latitude2))) + 
            (cos(deg2rad($latitude1)) * cos(deg2rad($latitude2)) * cos(deg2rad($longitudeDelta)))
        ));
        $distanceUnits = $this->getDistanceUnits();
        $ratio = 1;
        if (isset($distanceUnits[$unitCode])) {
            $ratio = $distanceUnits[$unitCode]['ratio'];
        }
        return $ratio * $distance;
    }
}