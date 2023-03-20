<?php

namespace AirCorsica\XKPlanBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
/**
 * AdresseSITA
 *
 * @ORM\Table(name="adresse_sita")
 * @ORM\Entity(repositoryClass="AirCorsica\XKPlanBundle\Repository\AdresseSITARepository")
 */
class AdresseSITA extends AbstractXKPlan
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
     * @Assert\Expression(
     *     expression="this.adresseSITA != '' || this.email != ''",
     *     message="Le champ Adresse SITA ou Email SITA est obligatoire."
     * )
     * @ORM\Column(name="adresseSITA", type="string", length=255)
     */
    public $adresseSITA;

    /**
     * @var string
     * @Assert\NotBlank(message = "Le champ Libelle est obligatoire")
     * @ORM\Column(name="libelle", type="string", length=255)
     */
    private $libelle;

    /**
     * @var string
     * @Assert\Expression(
     *     expression="this.adresseSITA == '' || this.email == ''",
     *     message="Un champ unique Adresse SITA ou Email SITA doit être saisi l'autre doit rester vide."
     * )
     * @ORM\Column(name="email", type="string", length=255)
     */
    public $email;

    /**
     * @var bool
     *
     * @ORM\Column(name="suiviDemandeSlot", type="boolean")
     */
    private $suiviDemandeSlot;

    /**
     * @var Aeroport
     * @ORM\ManyToOne(targetEntity="AirCorsica\XKPlanBundle\Entity\Aeroport")
     * @ORM\JoinColumn(nullable=true)
     */
    private $aeroportAttache;

    /**
     * @var GroupeSITA
     * @Assert\NotBlank(message = "Le champ Groupe SITA est obligatoire")
     * @ORM\ManyToOne(targetEntity="AirCorsica\XKPlanBundle\Entity\GroupeSITA", inversedBy="adresses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $groupeSITA;

    /**
     * @var Pays
     *
     * @ORM\ManyToOne(targetEntity="AirCorsica\XKPlanBundle\Entity\Pays")
     * @ORM\JoinColumn(nullable=true)
     */
    private $paysCoordinateur;

    /**
     * @var Compagnie
     *
     * @ORM\ManyToOne(targetEntity="AirCorsica\XKPlanBundle\Entity\Compagnie")
     * @ORM\JoinColumn(nullable=true)
     */
    private $compagnieAttachee;

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
     * Set adresseSITA
     *
     * @param string $adresseSITA
     *
     * @return AdresseSITA
     */
    public function setAdresseSITA($adresseSITA)
    {
        $this->adresseSITA = $adresseSITA;

        return $this;
    }

    /**
     * Get adresseSITA
     *
     * @return string
     */
    public function getAdresseSITA()
    {
        return $this->adresseSITA;
    }

    /**
     * Set libelle
     *
     * @param string $libelle
     *
     * @return AdresseSITA
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
     * Set suiviDemandeSlot
     *
     * @param boolean $suiviDemandeSlot
     *
     * @return AdresseSITA
     */
    public function setSuiviDemandeSlot($suiviDemandeSlot)
    {
        $this->suiviDemandeSlot = $suiviDemandeSlot;

        return $this;
    }

    /**
     * Get suiviDemandeSlot
     *
     * @return bool
     */
    public function getSuiviDemandeSlot()
    {
        return $this->suiviDemandeSlot;
    }

    /**
     * Set aeroportAttache
     *
     * @param \AirCorsica\XKPlanBundle\Entity\Aeroport $aeroportAttache
     *
     * @return AdresseSITA
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
     * Set paysCoordinateur
     *
     * @param \AirCorsica\XKPlanBundle\Entity\Pays $paysCoordinateur
     *
     * @return AdresseSITA
     */
    public function setPaysCoordinateur(\AirCorsica\XKPlanBundle\Entity\Pays $paysCoordinateur = null)
    {
        $this->paysCoordinateur = $paysCoordinateur;

        return $this;
    }

    /**
     * Get paysCoordinateur
     *
     * @return \AirCorsica\XKPlanBundle\Entity\Pays
     */
    public function getPaysCoordinateur()
    {
        return $this->paysCoordinateur;
    }

    /**
     * Set compagnieAttachee
     *
     * @param \AirCorsica\XKPlanBundle\Entity\Compagnie $compagnieAttachee
     *
     * @return AdresseSITA
     */
    public function setCompagnieAttachee(\AirCorsica\XKPlanBundle\Entity\Compagnie $compagnieAttachee = null)
    {
        $this->compagnieAttachee = $compagnieAttachee;

        return $this;
    }

    /**
     * Get compagnieAttachee
     *
     * @return \AirCorsica\XKPlanBundle\Entity\Compagnie
     */
    public function getCompagnieAttachee()
    {
        return $this->compagnieAttachee;
    }

    /**
     * Set groupeSITA
     *
     * @param \AirCorsica\XKPlanBundle\Entity\GroupeSITA $groupeSITA
     *
     * @return AdresseSITA
     */
    public function setGroupeSITA(\AirCorsica\XKPlanBundle\Entity\GroupeSITA $groupeSITA)
    {
        $this->groupeSITA = $groupeSITA;

        return $this;
    }

    /**
     * Get groupeSITA
     *
     * @return \AirCorsica\XKPlanBundle\Entity\GroupeSITA
     */
    public function getGroupeSITA()
    {
        return $this->groupeSITA;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = strtolower($email);

        return $this;
    }
}
