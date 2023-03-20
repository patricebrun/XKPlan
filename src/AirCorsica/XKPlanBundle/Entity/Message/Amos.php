<?php


    namespace AirCorsica\XKPlanBundle\Entity\Message;


    use AirCorsica\XKPlanBundle\Entity\Amos\AmosFlightsForDateDandAircraft;
    use AirCorsica\XKPlanBundle\Entity\Amos\AmosLeg;
    use AirCorsica\XKPlanBundle\Entity\Amos\FluxAmos;
    use AirCorsica\XKPlanBundle\Entity\Periode;
    use AirCorsica\XKPlanBundle\Entity\Template;
    use AirCorsica\XKPlanBundle\Entity\Vol;
    use AirCorsica\XKPlanBundle\Repository\VolRepository;
    use League\Period\Period;
    use Symfony\Component\HttpFoundation\Request;

    class Amos
    {
        private $em;

        /**
         * Amos constructor.
         *
         * @param $em
         */
        public function __construct($em)
        {
            $this->em = $em;
        }

        /**
         * @param Period $periode
         * @return FluxAmos
         */
        public function generateFlux(Period $periode)
        {
            ini_set('max_execution_time', 0);

            $fluxamos = new FluxAmos();

            foreach ($periode->getDatePeriod('1 DAY') as  $day){
                $amosFlightsForDateDandAircraft = $this->generateFlightsForDateAndAircraft($day);
                $fluxamos->addFlightsForDateAndAircraft($amosFlightsForDateDandAircraft);
            }

            return $fluxamos;
        }

        /**
         * @param \DateTimeInterface $day
         */
        public function generateFlightsForDateAndAircraft(\DateTimeInterface $day)
        {
            /** @var VolRepository $repoVol */
            $repoVol = $this->em->getRepository('AirCorsicaXKPlanBundle:Vol');
            $orderParPeriode = "ASC";

            $repoTemplate = $this->em->getRepository('AirCorsicaXKPlanBundle:Template');
            /** @var Template $templateCurrent */
            $templateCurrent = $repoTemplate->find(1);

            $request = new Request();
            $request->query->set('date_debut',$day->format("d-m-Y"));
            $request->query->set('date_fin',$day->format("d-m-Y"));
            $request->query->set('jours',array($day->format('N')));
            $vols = $repoVol->getVolsFilter($request,$templateCurrent);

            $listeFlightsForDateAndAircraft = array();
            /** @var Vol $vol */
            foreach ($vols as $vol){
                $aircraftRegistration = $vol->getAvion()->getNom();
                $key = $day->format("d-m-Y")."-".$aircraftRegistration;
                if(!array_key_exists($key,$listeFlightsForDateAndAircraft)){
                    $flightsForDateAndAircraft = new AmosFlightsForDateDandAircraft();
                    $flightsForDateAndAircraft->setAircraftRegistration($aircraftRegistration);
                    $flightsForDateAndAircraft->setSchedDepartureDate($day);
                    $listeFlightsForDateAndAircraft[$key] = $flightsForDateAndAircraft;
                }

                $scheduledDepartureDateTime = clone $day;
                $scheduledDepartureDateTimeMutable = $scheduledDepartureDateTime->setTime($vol->getPeriodeDeVol()->getDecollage()->format("H"),$vol->getPeriodeDeVol()->getDecollage()->format("i"));
                $scheduledArrivalDateTime = clone $day;
                $scheduledArrivalDateTimeMutable = $scheduledArrivalDateTime->setTime($vol->getPeriodeDeVol()->getAtterissage()->format("H"),$vol->getPeriodeDeVol()->getAtterissage()->format("i"));
                if($vol->getPeriodeDeVol()->getDecollage() > $vol->getPeriodeDeVol()->getAtterissage()){
                    $corscheduledArrivalDateTimeMutable = $scheduledArrivalDateTimeMutable->add(new \DateInterval('P1D'));
                }else{
                    $corscheduledArrivalDateTimeMutable = $scheduledArrivalDateTimeMutable;
                }

                $leg = new AmosLeg(
                    substr($vol->getNumero(),0,2),
                    substr($vol->getNumero(),2),
                    $scheduledDepartureDateTimeMutable,
                    $vol->getLigne()->getAeroportDepart()->getCodeIATA(),
                    $corscheduledArrivalDateTimeMutable,
                    $vol->getLigne()->getAeroportArrivee()->getCodeIATA(),
                    $vol->getTempsDeVol("total_minutes"),
                    1);

                $listeFlightsForDateAndAircraft[$key]->addLeg($leg);
            }

            return $listeFlightsForDateAndAircraft;
        }
    }