<?php


    namespace AirCorsica\XKPlanBundle\Entity\Amos;


    class FluxAmos
    {
        const USEDDATEFORMAT = "amos_standard";
        const AMOSXSDLAYOUTVERSION = "2012_01";

        private $flightsForDateAndAircraft;
        private $publishedAt;

        /**
         * FluxAmos constructor.
         *
         */
        public function __construct()
        {
            $this->publishedAt = new \DateTime();
        }

        /**
         * @return mixed
         */
        public function getFlightsForDateAndAircraft()
        {
            return $this->flightsForDateAndAircraft;
        }

        /**
         * @param mixed $flightsForDateAndAircraft
         *
         * @return FluxAmos
         */
        public function setFlightsForDateAndAircraft($flightsForDateAndAircraft)
        {
            $this->flightsForDateAndAircraft = $flightsForDateAndAircraft;

            return $this;
        }

        /**
         * @param mixed $flightsForDateAndAircraft
         *
         * @return FluxAmos
         */
        public function addFlightsForDateAndAircraft($flightsForDateAndAircraft)
        {
            $this->flightsForDateAndAircraft[] = $flightsForDateAndAircraft;

            return $this;
        }

        /**
         * @return mixed
         */
        public function getPublishedAt()
        {
            return $this->publishedAt;
        }

        /**
         * @param mixed $publishedAt
         *
         * @return FluxAmos
         */
        public function setPublishedAt($publishedAt)
        {
            $this->publishedAt = $publishedAt;

            return $this;
        }

        public function toXML()
        {
            $dom = new \DOMDocument();

            $dom->encoding = 'utf-8';

            $dom->xmlVersion = '1.0';

            $dom->formatOutput = true;

            $root = $dom->createElement('future_flights');
            $root->setAttribute('used_date_format', 'amos_standard');
            $root->setAttribute('amos_xsd_layout_version', '2012_01');
            $publishedAt = $this->publishedAt->format('Y-m-d H:i:s').".000";
            $root->setAttribute('published_at',$publishedAt);


            foreach ($this->getFlightsForDateAndAircraft() as $aflightsForDateAndAircraft){
                /** @var AmosFlightsForDateDandAircraft $flightsForDateAndAircraft */
                foreach ( $aflightsForDateAndAircraft as $flightsForDateAndAircraft){
                    $flights_for_date_and_aircraft_Node = $dom->createElement('flights_for_date_and_aircraft');

                    $sched_departure_date = $dom->createElement('sched_departure_date',$flightsForDateAndAircraft->getSchedDepartureDate()->format('Y-m-d'));
                    $sched_departure_date->setAttribute('data_mode', 'only_non_arrived_legs');
                    $flights_for_date_and_aircraft_Node->appendChild($sched_departure_date);

                    $aircraft_registration = $dom->createElement('aircraft_registration',$flightsForDateAndAircraft->getAircraftRegistration());
                    $flights_for_date_and_aircraft_Node->appendChild($aircraft_registration);

                    /** @var AmosLeg $leg */
                    foreach ($flightsForDateAndAircraft->getLegs() as $leg){
                        $leg_node = $dom->createElement('leg');

                        $carrier_node = $dom->createElement('carrier',$leg->getCarrier());
                        $leg_node->appendChild($carrier_node);

                        $flightNumber_node = $dom->createElement('flightNumber',$leg->getFlightNumber());
                        $leg_node->appendChild($flightNumber_node);

                        $scheduledDepartureDateTime_node = $dom->createElement('scheduledDepartureDateTime',$leg->getScheduledDepartureDateTime()->format('Y-m-d\TH:i'));
                        $leg_node->appendChild($scheduledDepartureDateTime_node);

                        $scheduledDepartureAirport_node = $dom->createElement('scheduledDepartureAirport',$leg->getScheduledDepartureAirport());
                        $leg_node->appendChild($scheduledDepartureAirport_node);

                        $scheduledArrivalDateTime_node = $dom->createElement('scheduledArrivalDateTime',$leg->getScheduledArrivalDateTime()->format('Y-m-d\TH:i'));
                        $leg_node->appendChild($scheduledArrivalDateTime_node);

                        $scheduledArrivalAirport_node = $dom->createElement('scheduledArrivalAirport',$leg->getScheduledArrivalAirport());
                        $leg_node->appendChild($scheduledArrivalAirport_node);

                        $estimatedLegDuration_node = $dom->createElement('estimatedLegDuration',$leg->getEstimatedLegDuration());
                        $leg_node->appendChild($estimatedLegDuration_node);

                        $legCycles_node = $dom->createElement('legCycles',1);
                        $leg_node->appendChild($legCycles_node);

                        $flights_for_date_and_aircraft_Node->appendChild($leg_node);
                    }
                    $root->appendChild($flights_for_date_and_aircraft_Node);
                }
            }

            $dom->appendChild($root);

            return  $dom;
        }

    }