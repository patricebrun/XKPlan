<?php
/**
 * Created by PhpStorm.
 * User: JeanJo
 * Date: 13/03/2017
 * Time: 09:45
 */

namespace AirCorsica\XKPlanBundle\Entity\Message;


/**
 * ASMSSMFooter
 */
class ASMSSMFooter
{
    /**
     * code IATA de l'aeroport de départ
     *
     * @var string $aeroportdepartcodeIATA
     */
    private $aeroportdepartcodeIATA;

    /**
     * code IATA de l'aeroport d'arrivée
     *
     * @var string $aeroportarriveecodeIATA
     */
    private $aeroportarriveecodeIATA;

    /**
     * terminal de l'aeroport de départ
     *
     * @var string $terminalaeroportdepart
     */
    private $terminalaeroportdepart;

    /**
     * terminal de l'aeroport d'arrivée
     *
     * @var string $terminalaeroportarrivee
     */
    private $terminalaeroportarrivee;

    /**
     * type de vol (REG régulier, SUP supplementaire...)
     *
     * @var string $typedevol
     */
    private $typedevol;


    /**
     * Set aeroportdepartcodeIATA
     *
     * @param string $aeroportdepartcodeIATA
     */
    public function setAeroportdepartcodeIATA($aeroportdepartcodeIATA)
    {
        $this->aeroportdepartcodeIATA = $aeroportdepartcodeIATA;
    }

    /**
     * Get aeroportdepartcodeIATA
     *
     * @return string $aeroportdepartcodeIATA
     */
    public function getAeroportdepartcodeIATA()
    {
        return $this->aeroportdepartcodeIATA;
    }

    /**
     * Set aeroportarriveecodeIATA
     *
     * @param string $aeroportarriveecodeIATA
     */
    public function setAeroportarriveecodeIATA($aeroportarriveecodeIATA)
    {
        $this->aeroportarriveecodeIATA = $aeroportarriveecodeIATA;
    }

    /**
     * Get aeroportarriveecodeIATA
     *
     * @return string $aeroportarriveecodeIATA
     */
    public function getAeroportarriveecodeIATA()
    {
        return $this->aeroportarriveecodeIATA;
    }

    /**
     * Set terminalaeroportdepart
     *
     * @param string $terminalaeroportdepart
     */
    public function setTerminalaeroportdepart($terminalaeroportdepart)
    {
        $this->terminalaeroportdepart = $terminalaeroportdepart;
    }

    /**
     * Get terminalaeroportdepart
     *
     * @return string $terminalaeroportdepart
     */
    public function getTerminalaeroportdepart()
    {
        return $this->terminalaeroportdepart;
    }

    /**
     * Set terminalaeroportarrivee
     *
     * @param string $terminalaeroportarrivee
     */
    public function setTerminalaeroportarrivee($terminalaeroportarrivee)
    {
        $this->terminalaeroportarrivee = $terminalaeroportarrivee;
    }

    /**
     * Get terminalaeroportarrivee
     *
     * @return string $terminalaeroportarrivee
     */
    public function getTerminalaeroportarrivee()
    {
        return $this->terminalaeroportarrivee;
    }

    /**
     * Set typedevol
     *
     * @param string $typedevol
     */
    public function setTypedevol($typedevol)
    {
        $this->typedevol = $typedevol;
    }

    /**
     * Get typedevol
     *
     * @return string $typedevol
     */
    public function getTypedevol()
    {
        return $this->typedevol;
    }


    /**
     * Render the footer to formated text
     *
     * @return string
     */
    public function render()
    {
        $renderingResult = "";
        if(($this->terminalaeroportdepart!=" ")&&($this->terminalaeroportdepart!="")&&($this->terminalaeroportdepart!=null)&&($this->terminalaeroportdepart!="0")){//le terminal de l'aeroport de départ existe
            $renderingResult .= $this->aeroportdepartcodeIATA.$this->aeroportarriveecodeIATA." 99/".$this->terminalaeroportdepart;
            $renderingResult .= "\n";
        }
        if(($this->terminalaeroportarrivee!=" ")&&($this->terminalaeroportarrivee!="")&&($this->terminalaeroportarrivee!=null)&&($this->terminalaeroportarrivee!="0")){//le terminal de l'aeroport d'arrivee existe
            $renderingResult .= $this->aeroportdepartcodeIATA.$this->aeroportarriveecodeIATA." 98/".$this->terminalaeroportarrivee;
            $renderingResult .= "\n";
        }
        if( ($this->typedevol == "REG") || ($this->typedevol == "SUP") ){
            $renderingResult .= $this->aeroportdepartcodeIATA.$this->aeroportarriveecodeIATA." 503/9"."\n";
            $renderingResult .= $this->aeroportdepartcodeIATA.$this->aeroportarriveecodeIATA." 505/ET";
        }
        return $renderingResult;
    }

}