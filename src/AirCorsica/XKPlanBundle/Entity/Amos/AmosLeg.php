<?php


    namespace AirCorsica\XKPlanBundle\Entity\Amos;


    class AmosLeg
    {
        private $carrier;
        private $flightNumber;
        private $scheduledDepartureDateTime;
        private $scheduledDepartureAirport;
        private $scheduledArrivalDateTime;
        private $scheduledArrivalAirport;
        private $estimatedLegDuration;
        private $legCycles;

        /**
         * AmosLeg constructor.
         *
         * @param $carrier
         * @param $flightNumber
         * @param $scheduledDepartureDateTime
         * @param $scheduledDepartureAirport
         * @param $scheduledArrivalDateTime
         * @param $scheduledArrivalAirport
         * @param $estimatedLegDuration
         * @param $legCycles
         */
        public function __construct(
            $carrier,
            $flightNumber,
            $scheduledDepartureDateTime,
            $scheduledDepartureAirport,
            $scheduledArrivalDateTime,
            $scheduledArrivalAirport,
            $estimatedLegDuration,
            $legCycles
        ) {
            $this->carrier                    = $carrier;
            $this->flightNumber               = $flightNumber;
            $this->scheduledDepartureDateTime = $scheduledDepartureDateTime;
            $this->scheduledDepartureAirport  = $scheduledDepartureAirport;
            $this->scheduledArrivalDateTime   = $scheduledArrivalDateTime;
            $this->scheduledArrivalAirport    = $scheduledArrivalAirport;
            $this->estimatedLegDuration       = $estimatedLegDuration;
            $this->legCycles                  = $legCycles;
        }

        /**
         * @return mixed
         */
        public function getCarrier()
        {
            return $this->carrier;
        }

        /**
         * @param mixed $carrier
         *
         * @return AmosLeg
         */
        public function setCarrier($carrier)
        {
            $this->carrier = $carrier;

            return $this;
        }

        /**
         * @return mixed
         */
        public function getFlightNumber()
        {
            return $this->flightNumber;
        }

        /**
         * @param mixed $flightNumber
         *
         * @return AmosLeg
         */
        public function setFlightNumber($flightNumber)
        {
            $this->flightNumber = $flightNumber;

            return $this;
        }

        /**
         * @return mixed
         */
        public function getScheduledDepartureDateTime()
        {
            return $this->scheduledDepartureDateTime;
        }

        /**
         * @param mixed $scheduledDepartureDateTime
         *
         * @return AmosLeg
         */
        public function setScheduledDepartureDateTime($scheduledDepartureDateTime)
        {
            $this->scheduledDepartureDateTime = $scheduledDepartureDateTime;

            return $this;
        }

        /**
         * @return mixed
         */
        public function getScheduledDepartureAirport()
        {
            return $this->scheduledDepartureAirport;
        }

        /**
         * @param mixed $scheduledDepartureAirport
         *
         * @return AmosLeg
         */
        public function setScheduledDepartureAirport($scheduledDepartureAirport)
        {
            $this->scheduledDepartureAirport = $scheduledDepartureAirport;

            return $this;
        }

        /**
         * @return mixed
         */
        public function getScheduledArrivalDateTime()
        {
            return $this->scheduledArrivalDateTime;
        }

        /**
         * @param mixed $scheduledArrivalDateTime
         *
         * @return AmosLeg
         */
        public function setScheduledArrivalDateTime($scheduledArrivalDateTime)
        {
            $this->scheduledArrivalDateTime = $scheduledArrivalDateTime;

            return $this;
        }

        /**
         * @return mixed
         */
        public function getScheduledArrivalAirport()
        {
            return $this->scheduledArrivalAirport;
        }

        /**
         * @param mixed $scheduledArrivalAirport
         *
         * @return AmosLeg
         */
        public function setScheduledArrivalAirport($scheduledArrivalAirport)
        {
            $this->scheduledArrivalAirport = $scheduledArrivalAirport;

            return $this;
        }

        /**
         * @return mixed
         */
        public function getEstimatedLegDuration()
        {
            return $this->estimatedLegDuration;
        }

        /**
         * @param mixed $estimatedLegDuration
         *
         * @return AmosLeg
         */
        public function setEstimatedLegDuration($estimatedLegDuration)
        {
            $this->estimatedLegDuration = $estimatedLegDuration;

            return $this;
        }

        /**
         * @return mixed
         */
        public function getLegCycles()
        {
            return $this->legCycles;
        }

        /**
         * @param mixed $legCycles
         *
         * @return AmosLeg
         */
        public function setLegCycles($legCycles)
        {
            $this->legCycles = $legCycles;

            return $this;
        }




    }