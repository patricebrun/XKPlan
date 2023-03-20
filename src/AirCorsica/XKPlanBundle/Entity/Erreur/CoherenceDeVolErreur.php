<?php
/**
 * Created by PhpStorm.
 * User: damie
 * Date: 03/04/2017
 * Time: 15:52
 */

namespace AirCorsica\XKPlanBundle\Entity\Erreur;


use AirCorsica\XKPlanBundle\Entity\Vol;
use Doctrine\Common\Collections\ArrayCollection;

class CoherenceDeVolErreur
{
    private $vol;

    private $aVolsErreurs;

    CONST superposition = "Supperposition de vol rencontrée, vérifiez les heures de départ:arrivée du vol";
    CONST sens = "L'aéroport de départ %depart% ne correspond pas à l'aéroport d'arrivée %arrivee";
    CONST unique = "Numéro de vol en doublon sur la période de validité pour certains jours d'opérations";
    CONST demiTour = "Heures du vol incohérentes au vu du temps de demi tour du type d'avion";
    CONST heureDuVol = "Heures du vol de base incohérentes: heure d'arrivée <= heure de départ";

    public function __construct()
    {
        $this->aVolsErreurs = new ArrayCollection();
    }

    /**
     * @return Vol
     */
    public function getVol()
    {
        return $this->vol;
    }

    /**
     * @param Vol
     */
    public function setVol($vol)
    {
        $this->vol = $vol;
    }

    /**
     * @return ArrayCollection
     */
    public function getAVolsErreurs()
    {
        return $this->aVolsErreurs;
    }

    /**
     * Add Vol en erreur
     *
     * @param VolErreurCoherence $vol
     *
     * @return CoherenceDeVolErreur
     */
    public function addVolsErreurs($volErreurCoherence)
    {
        $this->aVolsErreurs->add($volErreurCoherence);

        return $this;
    }

    /**
     * Remove Vol en erreur
     *
     * @param $object
     */
    public function removeVolsErreurs($volErreurCoherence)
    {
        $this->aVolsErreurs->removeElement($volErreurCoherence);
    }
}