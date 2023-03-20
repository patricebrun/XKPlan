<?php

namespace AirCorsica\XKPlanBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;


/**
 * PeriodeSaison
 *
 * @ORM\Table(name="periode_saison")
 * @ORM\Entity(repositoryClass="AirCorsica\XKPlanBundle\Repository\PeriodeSaisonRepository")
 */
class PeriodeSaison extends Periode
{

    /**
     * @var Saison
     *
     * @ORM\ManyToOne(targetEntity="AirCorsica\XKPlanBundle\Entity\Saison", inversedBy="periodesSaison")
     * @ORM\JoinColumn(nullable=true,onDelete="SET NULL")
     */
    private $saison;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AirCorsica\XKPlanBundle\Entity\TempsDeVol", mappedBy="saison", orphanRemoval=true, cascade={"persist"})
     */
    private $tempsDevol;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=255)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="descriptif", type="text", nullable=true)
     */
    private $descriptif;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_iata", type="boolean")
     */
    private $isIATA = false;


    /**
     * @var bool
     *
     * @ORM\Column(name="is_visible", type="boolean")
     */
    private $isVisible = false;


    public function __construct($sDateDebut, $sDateFin)
    {
        parent::__construct($sDateDebut, $sDateFin);
        $this->tempsDevol = new ArrayCollection();
    }
    /**
     * Set saison
     *
     * @param \AirCorsica\XKPlanBundle\Entity\Saison $saison
     *
     * @return PeriodeSaison
     */
    public function setSaison(\AirCorsica\XKPlanBundle\Entity\Saison $saison)
    {
        $this->saison = $saison;

        return $this;
    }

    /**
     * Get saison
     *
     * @return \AirCorsica\XKPlanBundle\Entity\Saison
     */
    public function getSaison()
    {
        return $this->saison;
    }

    /**
     * Set nom
     *
     * @param string $nom
     *
     * @return Periode
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set descriptif
     *
     * @param string $descriptif
     *
     * @return Periode
     */
    public function setdescriptif($descriptif)
    {
        $this->descriptif = $descriptif;

        return $this;
    }

    /**
     * Get descriptif
     *
     * @return string
     */
    public function getdescriptif()
    {
        return $this->descriptif;
    }

    /**
     * Set createur
     *
     * @param \AirCorsica\XKPlanBundle\Entity\Utilisateur $createur
     *
     * @return PeriodeSaison
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
     * Set isIATA
     *
     * @param boolean $isIATA
     *
     * @return PeriodeSaison
     */
    public function setIsIATA($isIATA)
    {
        $this->isIATA = $isIATA;

        return $this;
    }

    /**
     * Get isIATA
     *
     * @return boolean
     */
    public function getIsIATA()
    {
        return $this->isIATA;
    }

    /**
     * Add tempsDevol
     *
     * @param \AirCorsica\XKPlanBundle\Entity\TempsDeVol $tempsDevol
     *
     * @return PeriodeSaison
     */
    public function addTempsDevol(\AirCorsica\XKPlanBundle\Entity\TempsDeVol $tempsDevol)
    {
        $this->tempsDevol[] = $tempsDevol;

        return $this;
    }

    /**
     * Remove tempsDevol
     *
     * @param \AirCorsica\XKPlanBundle\Entity\TempsDeVol $tempsDevol
     */
    public function removeTempsDevol(\AirCorsica\XKPlanBundle\Entity\TempsDeVol $tempsDevol)
    {
        $this->tempsDevol->removeElement($tempsDevol);
    }

    /**
     * Get tempsDevol
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTempsDevol()
    {
        return $this->tempsDevol;
    }

    /**
     * Set isVisible
     *
     * @param boolean $isVisible
     *
     * @return PeriodeSaison
     */
    public function setIsVisible($isVisible)
    {
        $this->isVisible = $isVisible;

        return $this;
    }

    /**
     * Get isVisible
     *
     * @return boolean
     */
    public function getIsVisible()
    {
        return $this->isVisible;
    }


    public function getPositionInSaison(){
        $saison = $this->getSaison();

    }
}
