<?php
/**
 * Created by PhpStorm.
 * User: JeanJo
 * Date: 13/03/2017
 * Time: 09:45
 */

namespace AirCorsica\XKPlanBundle\Entity\Message;


/**
 * ASMSSMAction
 */
class ASMSSMAction
{

    private $messagetype;

    private $codeaction;

    private $acodesshareduvol;

    /**
     * ASMSSMAction constructor.
     * @param $codeaction
     */
    function __construct($messagetype,$codeaction,$acodesshareduvol)
    {
        // le type de message ASM ou SSM
        $this->messagetype = $messagetype;
        // le code de cette ASMSSMAction définie dans le ASMSSMHeader
        $this->codeaction = $codeaction;
        // le tableau des codes share du vol
        $this->acodesshareduvol = $acodesshareduvol;
    }


    /**
     * numero de vol
     *
     * @var string $numerovol
     */
    private $numerovol;

    /**
     * date du vol
     *
     * @var string $dateduvol (au format JJMMMYY  par ex: 13JAN17)
     */
    private $dateduvol;

    /**
     * date de début de la période de vol
     *
     * @var string $datedebutperiodedevol (au format JJMMMYY  par ex: 13JAN17)
     */
    private $datedebutperiodedevol;

    /**
     * date de fin de la période de vol
     *
     * @var string $datefinperiodedevol (au format JJMMMYY  par ex: 13JAN17)
     */
    private $datefinperiodedevol;

    /**
     * jours de validité du vol (par exemple: 134)
     *
     * @var string $joursvaliditeduvol
     */
    private $joursvaliditeduvol;

    /**
     * code service du type de vol
     *
     * @var string $codeservicetypedevol
     */
    private $codeservicetypedevol;

    /**
     * code IATA du type avion
     *
     * @var string $typeavioncodeIATA
     */
    private $typeavioncodeIATA;

    /**
     * capacite de avion
     *
     * @var string $capaciteavion
     */
    private $capaciteavion;

    /**
     * le code IATA de la compagnie
     *
     * @var string $compagniecodeIATA
     */
    private $compagniecodeIATA;

    /**
     * le code IATA de l'aeroport de départ
     *
     * @var string $aeroportdepartcodeIATA
     */
    private $aeroportdepartcodeIATA;

    /**
     * l'heure de départ du vol
     *
     * @var string $volheuredepart
     */
    private $volheuredepart;

    /**
     * le code IATA de l'aeroport d'arrivée
     *
     * @var string $aeroportarriveecodeIATA
     */
    private $aeroportarriveecodeIATA;

    /**
     * l'heure d'arrivée du vol
     *
     * @var string $volheurearrivee
     */
    private $volheurearrivee;

    /**
    * Get codeaction
    *
    * @return string $codeaction
    */
    public function getCodeAction()
    {
        return $this->codeaction;
    }

    /**
     * Set numerovol
     *
     * @param string $numerovol
     */
    public function setNumerovol($numerovol)
    {
        $this->numerovol = strtoupper($numerovol);
    }

    /**
     * Get numerovol
     *
     * @return string $numerovol
     */
    public function getNumerovol()
    {
        return $this->numerovol;
    }

    /**
     * Set dateduvol
     *
     * @param string $dateduvol
     */
    public function setDateduvol($dateduvol)
    {
        $this->dateduvol = strtoupper($dateduvol);
    }

    /**
     * Get dateduvol
     *
     * @return string $dateduvol
     */
    public function getDateduvol()
    {
        return $this->dateduvol;
    }


    /**
     * Set datedebutperiodedevol
     *
     * @param string $datedebutperiodedevol
     */
    public function setDatedebutperiodedevol($datedebutperiodedevol)
    {
        $this->datedebutperiodedevol = strtoupper($datedebutperiodedevol);
    }

    /**
     * Get datedebutperiodedevol
     *
     * @return string $datedebutperiodedevol
     */
    public function getDatedebutperiodedevol()
    {
        return $this->datedebutperiodedevol;
    }


    /**
     * Set datefinperiodedevol
     *
     * @param string $datefinperiodedevol
     */
    public function setDatefinperiodedevol($datefinperiodedevol)
    {
        $this->datefinperiodedevol = strtoupper($datefinperiodedevol);
    }

    /**
     * Get datefinperiodedevol
     *
     * @return string $datefinperiodedevol
     */
    public function getDatefinperiodedevol()
    {
        return $this->datefinperiodedevol;
    }


    /**
     * Set joursvaliditeduvol
     *
     * @param string $joursvaliditeduvol
     */
    public function setJoursvaliditeduvol($joursvaliditeduvol)
    {
        $this->joursvaliditeduvol = $joursvaliditeduvol;
    }

    /**
     * Get joursvaliditeduvol
     *
     * @return string $joursvaliditeduvol
     */
    public function getJoursvaliditeduvol()
    {
        return $this->joursvaliditeduvol;
    }

    /**
     * Set codeservicetypedevol
     *
     * @param string $codeservicetypedevol
     */
    public function setCodeservicetypedevol($codeservicetypedevol)
    {
        $this->codeservicetypedevol = $codeservicetypedevol;
    }

    /**
     * Get codeservicetypedevol
     *
     * @return string $codeservicetypedevol
     */
    public function getCodeservicetypedevol()
    {
        return $this->codeservicetypedevol;
    }

    /**
     * Set typeavioncodeIATA
     *
     * @param string $typeavioncodeIATA
     */
    public function setTypeavioncodeIATA($typeavioncodeIATA)
    {
        $this->typeavioncodeIATA = $typeavioncodeIATA;
    }

    /**
     * Get typeavioncodeIATA
     *
     * @return string $typeavioncodeIATA
     */
    public function getTypeavioncodeIATA()
    {
        return $this->typeavioncodeIATA;
    }

    /**
     * Set capaciteavion
     *
     * @param string $capaciteavion
     */
    public function setCapaciteavion($capaciteavion)
    {
        $this->capaciteavion = $capaciteavion;
    }

    /**
     * Get capaciteavion
     *
     * @return string $capaciteavion
     */
    public function getCapaciteavion()
    {
        return $this->capaciteavion;
    }

    /**
     * Set compagniecodeIATA
     *
     * @param string $compagniecodeIATA
     */
    public function setCompagniecodeIATA($compagniecodeIATA)
    {
        $this->compagniecodeIATA = $compagniecodeIATA;
    }

    /**
     * Get compagniecodeIATA
     *
     * @return string $compagniecodeIATA
     */
    public function getCompagniecodeIATA()
    {
        if($this->compagniecodeIATA != "") {
            return $this->compagniecodeIATA;
        }else{
            return "X";
        }
    }

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
     * Set volheuredepart
     *
     * @param string $volheuredepart
     */
    public function setVolheuredepart($volheuredepart)
    {
        $this->volheuredepart = $volheuredepart;
    }

    /**
     * Get volheuredepart
     *
     * @return string $volheuredepart
     */
    public function getVolheuredepart()
    {
        return $this->volheuredepart;
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
     * Set volheurearrivee
     *
     * @param string $volheurearrivee
     */
    public function setVolheurearrivee($volheurearrivee)
    {
        $this->volheurearrivee = $volheurearrivee;
    }

    /**
     * Get volheurearrivee
     *
     * @return string $volheurearrivee
     */
    public function getVolheurearrivee()
    {
        return $this->volheurearrivee;
    }




    /**
     * Render this action to formated text
     *
     * @return string
     */
    public function render()
    {
        $renderingResult = "";

        if($this->messagetype == "ASM") { //pour lzs ASM

            switch ($this->codeaction) {

                case "CNL":
                    //ligne 1
                    $renderingResult .= $this->numerovol."/".$this->dateduvol;
                    break;

                case "TIM":
                    //ligne 1
                    $renderingResult .= $this->numerovol."/".$this->dateduvol."\n";
                    //ligne 2
                    $renderingResult .= $this->aeroportdepartcodeIATA.$this->volheuredepart." ".$this->aeroportarriveecodeIATA.$this->volheurearrivee;
                    //ligne suivantes dans le cas de codes share lié à ce vol
                    foreach ($this->acodesshareduvol as $uncodeshare) {
                        $renderingResult .= "\n".$this->aeroportdepartcodeIATA.$this->aeroportarriveecodeIATA." 10/".$uncodeshare;
                    }
                    break;

                case "NEW":
                case "EQT":
                case "ADM":
                case "RPL":
                    //falling through: EQT ou RPL (NEW est identique ???)
                    //ligne 1
                    $renderingResult .= $this->numerovol."/".$this->dateduvol."\n";
                    //ligne 2
                    $renderingResult .= $this->codeservicetypedevol." ".$this->typeavioncodeIATA." Y".$this->capaciteavion." 3/".$this->compagniecodeIATA." 4/".$this->compagniecodeIATA." 5/".$this->compagniecodeIATA."\n";
                    //ligne 3
                    $renderingResult .= $this->aeroportdepartcodeIATA.$this->volheuredepart." ".$this->aeroportarriveecodeIATA.$this->volheurearrivee;
                    //ligne suivantes dans le cas de codes share lié à ce vol
                    foreach ($this->acodesshareduvol as $uncodeshare) {
                        $renderingResult .= "\n".$this->aeroportdepartcodeIATA.$this->aeroportarriveecodeIATA." 10/".$uncodeshare;
                    }
                    break;

                /* Demande AirCorsica suite demande partenaire HOP transformer ADM en RPL */
//                case "ADM":
//                    //ligne 1
//                    $renderingResult .= $this->numerovol."/".$this->dateduvol." 3/".$this->compagniecodeIATA." 4/".$this->compagniecodeIATA." 5/".$this->compagniecodeIATA;
//                    //ligne suivantes dans le cas de codes share lié à ce vol
//                    foreach ($this->acodesshareduvol as $uncodeshare) {
//                        $renderingResult .= "\n".$this->aeroportdepartcodeIATA.$this->aeroportarriveecodeIATA." 10/".$uncodeshare;
//                    }
//                    break;
            }

        }else{ //pour les SSM

            switch ($this->codeaction) {

                case "CNL":
                    //ligne 1
                    $renderingResult .= $this->numerovol."\n";
                    //ligne 2
                    $renderingResult .= $this->datedebutperiodedevol." ".$this->datefinperiodedevol." ".$this->joursvaliditeduvol;
                    break;

                case "TIM":
                    //ligne 1
                    $renderingResult .= $this->numerovol."\n";
                    //ligne 2
                    $renderingResult .= $this->datedebutperiodedevol." ".$this->datefinperiodedevol." ".$this->joursvaliditeduvol."\n";
                    //ligne 3
                    $renderingResult .= $this->aeroportdepartcodeIATA.$this->volheuredepart." ".$this->aeroportarriveecodeIATA.$this->volheurearrivee;
                    //ligne suivantes dans le cas de codes share lié à ce vol
                    foreach ($this->acodesshareduvol as $uncodeshare) {
                        $renderingResult .= "\n".$this->aeroportdepartcodeIATA.$this->aeroportarriveecodeIATA." 10/".$uncodeshare;
                    }
                    break;

                /* Demande AirCorsica suite demande partenaire HOP transformer ADM en RPL */
                // décommenter l'instruction au dessous
                //case "ADM":
                case "NEW":
                case "EQT":
                case "RPL":
                    //ligne 1
                    $renderingResult .= $this->numerovol." 3/".$this->compagniecodeIATA." 4/".$this->compagniecodeIATA." 5/".$this->compagniecodeIATA."\n";
                    //ligne 2
                    $renderingResult .= $this->datedebutperiodedevol." ".$this->datefinperiodedevol." ".$this->joursvaliditeduvol."\n";
                    //ligne 3
                    $renderingResult .= $this->codeservicetypedevol." ".$this->typeavioncodeIATA." Y".$this->capaciteavion."\n";
                    //ligne 4
                    $renderingResult .= $this->aeroportdepartcodeIATA.$this->volheuredepart." ".$this->aeroportarriveecodeIATA.$this->volheurearrivee;
                    //ligne suivantes dans le cas de codes share lié à ce vol
                    foreach ($this->acodesshareduvol as $uncodeshare) {
                        $renderingResult .= "\n".$this->aeroportdepartcodeIATA.$this->aeroportarriveecodeIATA." 10/".$uncodeshare;
                    }
                    break;

                /* Demande AirCorsica suite demande partenaire HOP transformer ADM en RPL */
                // commenter le bloc au dessous
                case "ADM":
                    //ligne 1
                    $renderingResult .= $this->numerovol."\n";
                    //ligne 2
                    $renderingResult .= $this->datedebutperiodedevol." ".$this->datefinperiodedevol." ".$this->joursvaliditeduvol;
                    //ligne suivantes dans le cas de codes share lié à ce vol
                    foreach ($this->acodesshareduvol as $uncodeshare) {
                        $renderingResult .= "\n".$this->aeroportdepartcodeIATA.$this->aeroportarriveecodeIATA." 10/".$uncodeshare;
                    }
                    break;
            }

        }

        return $renderingResult;
    }

}