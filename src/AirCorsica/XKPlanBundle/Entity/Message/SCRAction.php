<?php
/**
 * Created by PhpStorm.
 * User: JeanJo
 * Date: 13/03/2017
 * Time: 09:45
 */

namespace AirCorsica\XKPlanBundle\Entity\Message;


/**
 * SCRAction
 */
class SCRAction
{
    /*
     * Les blocs de la dataLine action
     * N XY123 XY1234 01JUL 30SEP 1234500 120319 CDG0700 0750CDG JJ
     * 1   2      3     4     5      6    7a 7b   8   9   10  11 12/13
     */

//    private $codeaction;
//
//    private $typedaeroportcoordonateur;
//
//    /**
//     * ASMSSMAction constructor.
//     * @param $codeaction
//     */
//    function __construct($codeaction,$typedaeroportcoordonateur)
//    {
//        // le code de cette ASMSSMAction définie dans le ASMSSMHeader
//        $this->codeaction = $codeaction;
//        // le tableau des codes share du vol
//        $this->typedaeroportcoordonateur = $typedaeroportcoordonateur;
//    }

    /**
     * 1- code de l'action
     *
     * @var string $actioncode
     */
    private $actioncode;

    /**
     * 2- code du vol d'arrivé
     *
     * @var string $arrivalflightdesignator
     */
    private $arrivalflightdesignator;

    /**
     * 3- code du vol de départ
     *
     * @var string $departureflightdesignator
     */
    private $departureflightdesignator;

    /**
     * 4- début de période (ou jour unique)
     *
     * @var string $startofperiod
     */
    private $startofperiod;

    /**
     * 5- fin de la période (ou jour unique)
     *
     * @var string $endofperiod
     */
    private $endofperiod;

    /**
     * 6- jour(s) de la semaine ou l'action s'effectue
     *
     * @var string $weekdaysofoperation
     */
    private $weekdaysofoperation;

    /**
     * 7a- nombre de siége de l'appareil
     *
     * @var string $numberofseats
     */
    private $numberofseats;

    /**
     * 7b- code IATA du type de l'appareil
     *
     * @var string $IATAaircraftsubtype
     */
    private $IATAaircraftsubtype;

    /**
     * 8- code IATA de l'aeroport de provenance du vol
     *
     * @var string $arrivingfrom
     */
    private $arrivingfrom;

    /**
     * 9- l'heure d'arrivée au format UTC (par ex: 0700)
     *
     * @var string $arrivaltime
     */
    private $arrivaltime;

    /**
     * 10- l'heure de départ du vol au format UTC (par ex: 0700)
     *
     * @var string $departuretime
     */
    private $departuretime;

    /**
     * 11- code IATA de l'aeroport de destination du vol
     *
     * @var string $departureto
     */
    private $departureto;

    /**
     * 12- arrival service type
     *
     * @var string $arrivalservicetype
     */
    private $arrivalservicetype;

    /**
     * 13- departure service type
     *
     * @var string $departureservicetype
     */
    private $departureservicetype;

    /**
     * Set actioncode
     *
     * @param string $actioncode
     */
    public function setActioncode($actioncode)
    {
        $this->actioncode = $actioncode;
    }

    /**
     * Get actioncode
     *
     * @return string actioncode
     */
    public function getActioncode()
    {
        return $this->actioncode;
    }

    /**
     * Set arrivalflightdesignator
     *
     * @param string $arrivalflightdesignator
     */
    public function setArrivalflightdesignator($arrivalflightdesignator)
    {
        $this->arrivalflightdesignator = strtoupper($arrivalflightdesignator);
    }

    /**
     * Get arrivalflightdesignator
     *
     * @return string arrivalflightdesignator
     */
    public function getArrivalflightdesignator()
    {
        return $this->arrivalflightdesignator;
    }

    /**
     * Set departureflightdesignator
     *
     * @param string $departureflightdesignator
     */
    public function setDepartureflightdesignator($departureflightdesignator)
    {
        $this->departureflightdesignator = strtoupper($departureflightdesignator);
    }

    /**
     * Get departureflightdesignator
     *
     * @return string departureflightdesignator
     */
    public function getDepartureflightdesignator()
    {
        return $this->departureflightdesignator;
    }

    /**
     * Set startofperiod
     *
     * @param string $startofperiod
     */
    public function setStartofperiod($startofperiod)
    {
        $this->startofperiod = $startofperiod;
    }

    /**
     * Get startofperiod
     *
     * @return string startofperiod
     */
    public function getStartofperiod()
    {
        return $this->startofperiod;
    }

    /**
     * Set endofperiod
     *
     * @param string $endofperiod
     */
    public function setEndofperiod($endofperiod)
    {
        $this->endofperiod = $endofperiod;
    }

    /**
     * Get endofperiod
     *
     * @return string endofperiod
     */
    public function getEndofperiod()
    {
        return $this->endofperiod;
    }

    /**
     * Set weekdaysofoperation
     *
     * @param string $weekdaysofoperation
     */
    public function setWeekdaysofoperation($weekdaysofoperation)
    {
        $this->weekdaysofoperation = $weekdaysofoperation;
    }

    /**
     * Get weekdaysofoperation
     *
     * @return string weekdaysofoperation
     */
    public function getWeekdaysofoperation()
    {
        return $this->weekdaysofoperation;
    }

    /**
     * Set numberofseats
     *
     * @param string $numberofseats
     */
    public function setNumberofseats($numberofseats)
    {
        $this->numberofseats = $numberofseats;
    }

    /**
     * Get numberofseats
     *
     * @return string numberofseats
     */
    public function getNumberofseats()
    {
        return $this->numberofseats;
    }

    /**
     * Set IATAaircraftsubtype
     *
     * @param string $IATAaircraftsubtype
     */
    public function setIATAaircraftsubtype($IATAaircraftsubtype)
    {
        $this->IATAaircraftsubtype = $IATAaircraftsubtype;
    }

    /**
     * Get IATAaircraftsubtype
     *
     * @return string IATAaircraftsubtype
     */
    public function getIATAaircraftsubtype()
    {
        return $this->IATAaircraftsubtype;
    }

    /**
     * Set arrivingfrom
     *
     * @param string $arrivingfrom
     */
    public function setArrivingfrom($arrivingfrom)
    {
        $this->arrivingfrom = $arrivingfrom;
    }

    /**
     * Get arrivingfrom
     *
     * @return string arrivingfrom
     */
    public function getArrivingfrom()
    {
        return $this->arrivingfrom;
    }

    /**
     * Set arrivaltime
     *
     * @param string $arrivaltime
     */
    public function setArrivaltime($arrivaltime)
    {
        $this->arrivaltime = $arrivaltime;
    }

    /**
     * Get arrivaltime
     *
     * @return string arrivaltime
     */
    public function getArrivaltime()
    {
        return $this->arrivaltime;
    }

    /**
     * Set departuretime
     *
     * @param string $departuretime
     */
    public function setDeparturetime($departuretime)
    {
        $this->departuretime = $departuretime;
    }

    /**
     * Get departuretime
     *
     * @return string departuretime
     */
    public function getDeparturetime()
    {
        return $this->departuretime;
    }

    /**
     * Set departureto
     *
     * @param string $departureto
     */
    public function setDepartureto($departureto)
    {
        $this->departureto = $departureto;
    }

    /**
     * Get departureto
     *
     * @return string departureto
     */
    public function getDepartureto()
    {
        return $this->departureto;
    }

    /**
     * Set arrivalservicetype
     *
     * @param string $arrivalservicetype
     */
    public function setArrivalservicetype($arrivalservicetype)
    {
        $this->arrivalservicetype = $arrivalservicetype;
    }

    /**
     * Get arrivalservicetype
     *
     * @return string arrivalservicetype
     */
    public function getArrivalservicetype()
    {
        return $this->arrivalservicetype;
    }

    /**
     * Set departureservicetype
     *
     * @param string $departureservicetype
     */
    public function setDepartureservicetype($departureservicetype)
    {
        $this->departureservicetype = $departureservicetype;
    }

    /**
     * Get departureservicetype
     *
     * @return string departureservicetype
     */
    public function getDepartureservicetype()
    {
        return $this->departureservicetype;
    }

    /**
     * Render the action to formated text
     *
     * @return string
     */
    public function render()
    {
        $temp = "";
        $temp .= $this->actioncode;
        if($this->arrivalflightdesignator != ""){
            $temp .= $this->arrivalflightdesignator." ";
        }
        if($this->departureflightdesignator != ""){
            $temp .= $this->departureflightdesignator." ";
        }
        $temp .= $this->startofperiod;
        $temp .= $this->endofperiod." ";
        $temp .= $this->weekdaysofoperation." ";
        $temp .= $this->numberofseats;
        $temp .= $this->IATAaircraftsubtype." ";
        if($this->arrivingfrom != ""){
            $temp .= $this->arrivingfrom;
            $temp .= $this->arrivaltime." ";
        }
        if($this->departureto != ""){
            $temp .= $this->departuretime;
            $temp .= $this->departureto." ";
        }
        if($this->arrivingfrom != ""){
            $temp .= $this->arrivalservicetype;
        }
        if($this->departureto != ""){
            $temp .= $this->departureservicetype;
        }

        return $temp;
    }

//    /**
//     * Render the action to formated text
//     *
//     * @return string
//     */
//    public function render2()
//    {
//        $renderingResult = "";
//
//        switch ($this->codeaction) {
//
//            case "D":
//            case "N":
//                //TODO à partir de la méthode: createActionDeleteOrNewRequest(...) de Entity/Message/SCR.php
//                break;
//
//            case "CR":
//                //TODO à partir de la méthode: addActionsDataLineCancelAndReplaceRequestToMessage(...) de Entity/Message/SCR.php
//                break;
//
//        }
//
//        return $renderingResult;
//
//    }

}