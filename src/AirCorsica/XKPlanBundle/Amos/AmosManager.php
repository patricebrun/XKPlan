<?php

    namespace AirCorsica\XKPlanBundle\Amos;

    use AirCorsica\XKPlanBundle\Entity\Message\Amos;
    use AirCorsica\XKPlanBundle\Utils\XmlValidator;
    use League\Period\Period;
    use Doctrine\ORM\EntityManager;

    class AmosManager
    {
        private $em;
        private $validator;

        /**
         * AmosManager constructor.
         *
         * @param EntityManager $em
         * @param XmlValidator  $validator
         */
        public function __construct(EntityManager $em,XmlValidator $validator)
        {
            $this->em = $em;
            $this->validator = $validator;

        }


        /**
         * @param $dateDebut
         * @param $dateFin
         *
         * @return int
         */
        public function generateXml($dateDebut, $dateFin)
        {
            $amosManager = new Amos($this->em);
            $datimeDebut = \DateTime::createFromFormat("d-m-Y",$dateDebut);
            $datimeFin = \DateTime::createFromFormat("d-m-Y",$dateFin);
            $periode = new Period($datimeDebut, $datimeFin);
            $amosFlux = $amosManager->generateFlux($periode);
            $xml = $amosFlux->toXML();
            $currentDate = new \DateTime();
            $path = $_SERVER['DOCUMENT_ROOT'].'/AMOS/amos_'.$currentDate->format('d-m-Y H:i:s').'.xml';
            $xml->save($path);
            if(false == $this->validator->validateFeeds($path)){
                unlink($path);

                return false;
            }
            return true;
        }

        /**
         * @param $dir
         * @param $limit
         *
         * @return array|bool
         */
        public function getListeXmlAmos($dir, $limit){
            $ignored = array('.', '..', '.svn', '.htaccess');

            $files = array();
            foreach (scandir($dir) as $file) {
                if (in_array($file, $ignored)) continue;
                $files[$file] = filemtime($dir . '/' . $file);
            }

            arsort($files);
            $files = array_keys($files);

            return ($files) ? array_slice($files,0,$limit) : false;
        }
    }