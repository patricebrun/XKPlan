<?php

    namespace AirCorsica\XKPlanBundle\Controller;

    use AirCorsica\XKPlanBundle\Entity\Message\Amos;
    use League\Period\Period;
    use Symfony\Bundle\FrameworkBundle\Controller\Controller;

    class AmosController extends Controller
    {

        public function indexAction($name)
        {
            return $this->render('', array('name' => $name));
        }

        public function generateAction()
        {
            $amosManager = new Amos($this->getDoctrine()->getManager());
            $periode = new Period(new \DateTime('2019-01-01T00:00:00.000000Z'), new \DateTime('2019-02-01T00:00:00.000000Z'));
            $amosFlux = $amosManager->generateFlux($periode);
            $xml = $amosFlux->toXML();
            $path = __DIR__.'/../Resources/AmosXml/amos.xml';

            $xml->save($path);
        }
    }
