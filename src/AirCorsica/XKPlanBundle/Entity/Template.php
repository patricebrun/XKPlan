<?php

namespace AirCorsica\XKPlanBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Template
 *
 * @ORM\Table(name="template")
 * @ORM\Entity(repositoryClass="AirCorsica\XKPlanBundle\Repository\TemplateRepository")
 */
class Template extends AbstractXKPlan
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="libelle", type="string", length=255)
     */
    private $libelle;

    /**
     * @var bool
     *
     * @ORM\Column(name="production", type="boolean")
     */
    private $production;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AirCorsica\XKPlanBundle\Entity\Flux", mappedBy="template", orphanRemoval=true, cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $flux;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AirCorsica\XKPlanBundle\Entity\Vol", mappedBy="template", orphanRemoval=true, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $vols;


	/**
	 * @var bool
	 *
	 * @ORM\Column(name="inactif", type="boolean",options={"default":"0"})
	 */
	private $inactif = 0;


    /**
     * Template constructor.
     */
    public function __construct()
    {
        $this->flux = new ArrayCollection();
        $this->vols = new ArrayCollection();
    }


    public function __toString()
    {
        return $this->libelle;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set libelle
     *
     * @param string $libelle
     *
     * @return Template
     */
    public function setLibelle($libelle)
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Get libelle
     *
     * @return string
     */
    public function getLibelle()
    {
        return $this->libelle;
    }

    /**
     * Set production
     *
     * @param boolean $production
     *
     * @return Template
     */
    public function setProduction($production)
    {
        $this->production = $production;

        return $this;
    }

    /**
     * Get production
     *
     * @return bool
     */
    public function getProduction()
    {
        return $this->production;
    }

    /**
     * Add flux
     *
     * @param \AirCorsica\XKPlanBundle\Entity\Flux $flux
     *
     * @return Template
     */
    public function addFlux(\AirCorsica\XKPlanBundle\Entity\Flux $flux)
    {
        $flux->setTemplate($this);
        $this->flux[] = $flux;

        return $this;
    }

    /**
     * Remove flux
     *
     * @param \AirCorsica\XKPlanBundle\Entity\Flux $flux
     */
    public function removeFlux(\AirCorsica\XKPlanBundle\Entity\Flux $flux)
    {
        $this->flux->removeElement($flux);
    }

    /**
     * Get flux
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getFlux()
    {
        return $this->flux;
    }

    /**
     * Add vol
     *
     * @param \AirCorsica\XKPlanBundle\Entity\Vol $vol
     *
     * @return Template
     */
    public function addVol(\AirCorsica\XKPlanBundle\Entity\Vol $vol)
    {
        $this->vols[] = $vol;

        return $this;
    }

    /**
     * Remove vol
     *
     * @param \AirCorsica\XKPlanBundle\Entity\Vol $vol
     */
    public function removeVol(\AirCorsica\XKPlanBundle\Entity\Vol $vol)
    {
        $this->vols->removeElement($vol);
    }

    /**
     * Get vols
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getVols()
    {
        return $this->vols;
    }

    /**
     * Set createur
     *
     * @param \AirCorsica\XKPlanBundle\Entity\Utilisateur $createur
     *
     * @return Template
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
	 * @return bool
	 */
	public function isInactif() {
		return $this->inactif;
	}

	/**
	 * @param bool $inactif
	 */
	public function setInactif( $inactif ) {
		$this->inactif = $inactif;
	}




	/**
	 * @param  $em entity manager
	 * @param $vols tableau des vols qui doivent être purgés
	 */
    public function purge($em,$vols){
        //$this->getVols()->clear()
	    foreach ($vols as $vol){
	    	$vol->setTemplate(null);
			$em->persist($vol);
	    }
    }
}
