<?php

namespace AirCorsica\XKPlanBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class AirCorsicaXKPlanBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
