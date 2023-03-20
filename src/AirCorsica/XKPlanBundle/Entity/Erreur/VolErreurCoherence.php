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

class VolErreurCoherence extends AbstractErreur
{
    private $vol;

    CONST superposition = "Supperposition de vol rencontrée, vérifiez les heures de départ:arrivée du vol";
    CONST sens = "L'aéroport de départ %s ne correspond pas à l'aéroport d'arrivée %s";
    CONST sensPeriodePrecedente = "L'aéroport de départ %s ne correspond pas à l'aéroport d'arrivée %s";
//    CONST sensPeriodePrecedente = "L'aéroport de départ %s ne correspond pas à l'aéroport d'arrivée %s entre le %s et le %s";
//    CONST sensPeriodeSuivante = "L'aéroport de départ %s ne correspond pas à l'aéroport d'arrivée %s entre le %s et le %s";
    CONST sensPeriodeSuivante = "L'aéroport de départ %s ne correspond pas à l'aéroport d'arrivée %s";
//    CONST sensPrecedent = "L'aéroport de départ %s ne correspond pas à l'aéroport d'arrivée %s entre le jour %s et le jour %s";
    CONST sensPrecedent = "L'aéroport de départ %s ne correspond pas à l'aéroport d'arrivée %s";
//    CONST sensSuivant = "L'aéroport de départ %s ne correspond pas à l'aéroport d'arrivée %s entre le jour %s et le jour %s";
    CONST sensSuivant = "L'aéroport de départ %s ne correspond pas à l'aéroport d'arrivée %s";
    CONST unique = "Numéro de vol en doublon sur la période de validité pour certains jours d'opérations";
    CONST demiTour = "Heures du vol incohérentes au vu du temps de demi tour du type d'avion";
    CONST heureDuVol = "Heures du vol de base incohérentes: heure d'arrivée <= heure de départ";

    /**
     * @return Vol
     */
    public function getVol()
    {
        return $this->vol;
    }

    /**
     * @param Vol $vol
     */
    public function setVol($vol)
    {
        $this->vol = $vol;
    }
}