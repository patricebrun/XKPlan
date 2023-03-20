<?php

namespace AirCorsica\XKPlanBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('AirCorsicaXKPlanBundle:Default:index.html.twig');
    }

}
