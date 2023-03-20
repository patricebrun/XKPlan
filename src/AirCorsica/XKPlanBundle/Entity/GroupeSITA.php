<?php

namespace AirCorsica\XKPlanBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * GroupeSITA
 *
 * @ORM\Table(name="groupe_sita")
 * @ORM\Entity(repositoryClass="AirCorsica\XKPlanBundle\Repository\GroupeSITARepository")
 */
class GroupeSITA extends AbstractXKPlan
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
     * @Assert\NotBlank(message = "Le champ Nom est obligatoire")
     * @ORM\Column(name="nom", type="string", length=255)
     */
    private $nom;

    /**
     * @var bool
     *
     * @ORM\Column(name="groupeGenerique", type="boolean")
     */
    private $groupeGenerique;

    /**
     * @var Aeroport
     *
     * @ORM\ManyToOne(targetEntity="AirCorsica\XKPlanBundle\Entity\Aeroport")
     * @ORM\JoinColumn(nullable=true)
     */
    private $aeroportAttache;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AirCorsica\XKPlanBundle\Entity\AdresseSITA", mappedBy="groupeSITA", cascade={"remove", "persist"})
     */
    private $adresses;

    public function __construct()
    {
        $this->adresses = new ArrayCollection();
    }

    public function __toString()
    {
        return $this->getNom();
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
     * Set nom
     *
     * @param string $nom
     *
     * @return GroupeSITA
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
     * Set groupeGenerique
     *
     * @param boolean $groupeGenerique
     *
     * @return GroupeSITA
     */
    public function setGroupeGenerique($groupeGenerique)
    {
        $this->groupeGenerique = $groupeGenerique;

        return $this;
    }

    /**
     * Get groupeGenerique
     *
     * @return bool
     */
    public function getGroupeGenerique()
    {
        return $this->groupeGenerique;
    }

    /**
     * Set aeroportAttache
     *
     * @param \AirCorsica\XKPlanBundle\Entity\Aeroport $aeroportAttache
     *
     * @return GroupeSITA
     */
    public function setAeroportAttache(\AirCorsica\XKPlanBundle\Entity\Aeroport $aeroportAttache)
    {
        $this->aeroportAttache = $aeroportAttache;

        return $this;
    }

    /**
     * Get aeroportAttache
     *
     * @return \AirCorsica\XKPlanBundle\Entity\Aeroport
     */
    public function getAeroportAttache()
    {
        return $this->aeroportAttache;
    }

    /**
     * Add adress
     *
     * @param \AirCorsica\XKPlanBundle\Entity\AdresseSITA $adress
     *
     * @return GroupeSITA
     */
    public function addAdress(\AirCorsica\XKPlanBundle\Entity\AdresseSITA $adress)
    {
        $this->adresses[] = $adress;

        return $this;
    }

    /**
     * Remove adress
     *
     * @param \AirCorsica\XKPlanBundle\Entity\AdresseSITA $adress
     */
    public function removeAdress(\AirCorsica\XKPlanBundle\Entity\AdresseSITA $adress)
    {
        $this->adresses->removeElement($adress);
    }

    /**
     * Get adresses
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAdresses()
    {
        return $this->adresses;
    }

    /**
     * Set createur
     *
     * @param \AirCorsica\XKPlanBundle\Entity\Utilisateur $createur
     *
     * @return GroupeSITA
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
}
