<?php


    namespace AirCorsica\XKPlanBundle\Entity\Amos;


    class AmosFlightsForDateDandAircraft
    {
        const DATA_MODE = "only_non_arrived_legs";

        private $legs;
        private $aircraftRegistration;
        private $schedDepartureDate;


        /**
         * @return mixed
         */
        public function getLegs()
        {
            return $this->legs;
        }

        /**
         * @param mixed $legs
         *
         * @return AmosFlightsForDateDandAircraft
         */
        public function setLegs($legs)
        {
            $this->legs = $legs;

            return $this;
        }


        /**
         * @param mixed $legs
         *
         * @return AmosLeg
         */
        public function addLeg(AmosLeg $leg)
        {
            $this->legs[] = $leg;

            return $this;
        }

        /**
         * @return mixed
         */
        public function getAircraftRegistration()
        {
            return $this->aircraftRegistration;
        }

        /**
         * @param mixed $aircraftRegistration
         *
         * @return AmosFlightsForDateDandAircraft
         */
        public function setAircraftRegistration($aircraftRegistration)
        {
            $this->aircraftRegistration = $aircraftRegistration;

            return $this;
        }

        /**
         * @return mixed
         */
        public function getSchedDepartureDate()
        {
            return $this->schedDepartureDate;
        }

        /**
         * @param mixed $schedDepartureDate
         *
         * @return AmosFlightsForDateDandAircraft
         */
        public function setSchedDepartureDate($schedDepartureDate)
        {
            $this->schedDepartureDate = $schedDepartureDate;

            return $this;
        }

    }