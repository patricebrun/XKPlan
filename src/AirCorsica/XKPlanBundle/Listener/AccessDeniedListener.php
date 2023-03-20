<?php
/**
 * Created by PhpStorm.
 * User: patriceb
 * Date: 01/03/2017
 * Time: 15:07
 */

namespace AirCorsica\XKPlanBundle\Listener;


use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent,
    Symfony\Component\Security\Core\Exception\AccessDeniedException,
    Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException,
    Symfony\Component\HttpFoundation\RequestStack,
    Symfony\Component\HttpFoundation\RedirectResponse,
    Symfony\Component\HttpFoundation\Session\Session,
    Symfony\Component\Routing\Router;

class AccessDeniedListener
{
    protected $_session;
    protected $_router;
    protected $_request;
    public function __construct(Session $session, Router $router, RequestStack $request)
    {
        $this->_session = $session;
        $this->_router = $router;
        $this->_request = $request->getCurrentRequest();
    }
    public function onAccessDeniedException(GetResponseForExceptionEvent $event)
    {
        if ($event->getException() instanceof AccessDeniedHttpException)
        {
            $this->_session->getFlashBag()->add('error', 'Accès refusé. Vous n\'avez pas la permission d\'accéder à cette page.');

            if ($this->_request->headers->get('referer'))
            {
                $route = $this->_request->headers->get('referer');
            } else {
                $route = $this->_router->generate('air_corsica_xk_plan_homepage');
            }

            $event->setResponse(new RedirectResponse($route));
        }
    }
}