<?php

namespace AirCorsica\XKPlanBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PeriodeImmobilisation
 *
 * @ORM\Table(name="periode_immobilisation")
 * @ORM\Entity(repositoryClass="AirCorsica\XKPlanBundle\Repository\PeriodeImmobilisationRepository")
 */
class PeriodeImmobilisation extends Periode
{

    /**
     * @var Avion
     *
     * @ORM\ManyToOne(targetEntity="AirCorsica\XKPlanBundle\Entity\Avion", inversedBy="periodesImmobilsation")
     * @ORM\JoinColumn(nullable=false)
     */
    private $avion;

    /**
     * @var Template
     *
     * @ORM\ManyToOne(targetEntity="AirCorsica\XKPlanBundle\Entity\Template", inversedBy="avions")
     * @ORM\JoinColumn(nullable=true)
     */
    protected $template;


    /**
     * Periode constructor.
     */
    function __construct($sDateDebut = null, $sDateFin = null)
    {
        if( null != $sDateDebut && null != $sDateFin){
            $oDateDebut = new \DateTime($sDateDebut);
            $oDateFin = new \DateTime($sDateFin);
            $this->setDateDebut($oDateDebut);
            $this->setDateFin($oDateFin);
        }

        return $this;
    }

    /**
     * Set avion
     *
     * @param  $avion null | \AirCorsica\XKPlanBundle\Entity\Avion
     *
     * @return PeriodeImmobilisation
     */
    public function setAvion(\AirCorsica\XKPlanBundle\Entity\Avion $avion = null)
    {
        $this->avion = $avion;

        return $this;
    }

    /**
     * Get avion
     *
     * @return \AirCorsica\XKPlanBundle\Entity\Avion
     */
    public function getAvion()
    {
        return $this->avion;
    }

    /**
     * Set createur
     *
     * @param \AirCorsica\XKPlanBundle\Entity\Utilisateur $createur
     *
     * @return PeriodeImmobilisation
     */
    public function setCreateur(\AirCorsica\XKPlanBundle\Entity\Utilisateur $createur)
    {
        $this->createur = $createur;

        return $this;
    }

    /**
     * Get createur
     *
     * @return \AirCorsica\XKPlanBundle\Entity\Utilisateur
     */
    public function getCreateur()
    {
        return $this->createur;
    }

    /**
     * @return Template
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param Template $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }
}
