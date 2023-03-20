<?php

namespace AirCorsica\XKPlanBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JsonSerializable;

/**
 * CodeShareVol
 *
 * @ORM\Table(name="code_share_vol")
 * @ORM\Entity(repositoryClass="AirCorsica\XKPlanBundle\Repository\CodeShareVolRepository")
 */
class CodeShareVol extends AbstractCodeShare implements JsonSerializable
{

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="AirCorsica\XKPlanBundle\Entity\Vol", mappedBy="codesShareVol", orphanRemoval=true, cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $vols;

    /**
     * Set createur
     *
     * @param \AirCorsica\XKPlanBundle\Entity\Utilisateur $createur
     *
     * @return CodeShareVol
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
     * Constructor
     */
    public function __construct()
    {
        $this->vols = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getLibelle();
    }

    public function jsonSerialize()
    {
        return array(
            'libelle' => $this->getLibelle(),
            'id'=> $this->getId(),
        );
    }


    /**
     * Add vol
     *
     * @param \AirCorsica\XKPlanBundle\Entity\Vol $vol
     *
     * @return CodeShareVol
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
}
