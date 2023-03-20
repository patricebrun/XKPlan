<?php
/**
 * Created by PhpStorm.
 * User: patriceb
 * Date: 12/01/2017
 * Time: 10:49
 */

namespace AirCorsica\XKPlanBundle\Entity;


interface InterfaceComparable
{
    public function compare(InterfaceComparable $subject);
}