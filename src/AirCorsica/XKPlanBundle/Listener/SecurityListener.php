<?php
/**
 * Created by PhpStorm.
 * User: patriceb
 * Date: 23/03/2017
 * Time: 17:34
 */

namespace AirCorsica\XKPlanBundle\Listener;


use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class SecurityListener
{

//    public function __construct(SecurityContextInterface $security, Session $session)
//    {
//        $this->security = $security;
//        $this->session = $session;
//    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $session = new Session();
        if(!$session->has('template')){
            $session->set('template',1);
        }
    }

}