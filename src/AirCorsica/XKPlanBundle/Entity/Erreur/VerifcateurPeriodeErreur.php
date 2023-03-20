<?php
/**
 * Created by PhpStorm.
 * User: damie
 * Date: 03/04/2017
 * Time: 15:52
 */

namespace AirCorsica\XKPlanBundle\Entity\Erreur;


use AirCorsica\XKPlanBundle\Entity\PeriodeDeVol;
use AirCorsica\XKPlanBundle\Entity\Vol;

class VerifcateurPeriodeErreur extends AbstractErreur
{
    private $objetErreur;
    private $objetCorrige;

    /**
     * VerifcateurPeriodeErreur constructor.
     * @param $objetErreur PeriodeDeVol en erreur
     * @param $objetCoorige PeriodeDeVol corrigé
     */
    public function __construct($objetErreur,$objetCoorige)
    {
        $this->objetCorrige = $objetCoorige;
        $this->objetErreur = $objetErreur;
    }

    /**
     * @return mixed
     */
    public function getObjetErreur()
    {
        return $this->objetErreur;
    }

    /**
     * @param mixed $objetErreur
     */
    public function setObjetErreur($objetErreur)
    {
        $this->objetErreur = $objetErreur;
    }

    /**
     * @return mixed
     */
    public function getObjetCorrige()
    {
        return $this->objetCorrige;
    }

    /**
     * @param mixed $objetCorrige
     */
    public function setObjetCorrige($objetCorrige)
    {
        $this->objetCorrige = $objetCorrige;
    }
}