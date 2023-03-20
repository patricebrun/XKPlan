<?php
/**
 * Created by PhpStorm.
 * User: JeanJo
 * Date: 13/03/2017
 * Time: 09:45
 */

namespace AirCorsica\XKPlanBundle\Entity\Message;


/**
 * SCRFooter
 */
class SCRFooter
{

    /*
    * Les lignes d'informations du footer; cad SI suivit de GI sont saisi à la main par aircorsica dans XKPlan en consultant le message avant l'envoi
    *
    * SI= supplementary information (in connection with content of SCR message) GI=general information (e.g. greeting)
    * par exemple: SI IF NOT AVBL PLS GIVE NEAREST POSSIBLE
    * GI=g eneral information (e.g. greeting)
    * par exemple: GI BRGDS.....
    * It is compulsory that any additional text following the data lines starts either with "SI" or with "GI"!
    */

    /**
     * informations suplémentaire
     *
     * @var string $supplementaryinformation
     */
    private $supplementaryinformation;

    /**
     * informations générale (remerciement, formule de politesse.. etc)
     *
     * @var string $generalinformation
     */
    private $generalinformation;

    /**
     * Set supplementaryinformation
     *
     * @param string $supplementaryinformation
     */
    public function setSupplementaryinformation($supplementaryinformation)
    {
        $this->supplementaryinformation = $supplementaryinformation;
    }

    /**
     * Get supplementaryinformation
     *
     * @return string supplementaryinformation
     */
    public function getSupplementaryinformation()
    {
        return $this->supplementaryinformation;
    }

    /**
     * Set generalinformation
     *
     * @param string $generalinformation
     */
    public function setGeneralinformation($generalinformation)
    {
        $this->generalinformation = $generalinformation;
    }

    /**
     * Get generalinformation
     *
     * @return string generalinformation
     */
    public function getGeneralinformation()
    {
        return $this->generalinformation;
    }

    /**
     * Render the footer to formated text
     *
     * @return string
     */
    public function render()
    {
        return implode(PHP_EOL,array_filter(array($this->supplementaryinformation,$this->generalinformation)));
    }

}