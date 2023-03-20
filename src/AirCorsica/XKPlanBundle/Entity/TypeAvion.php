<?php

namespace AirCorsica\XKPlanBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * TypeAvion
 *
 * @ORM\Table(name="type_avion")
 * @ORM\Entity(repositoryClass="AirCorsica\XKPlanBundle\Repository\TypeAvionRepository")
 */
class TypeAvion extends AbstractXKPlan
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
     * @Assert\NotBlank(message = "Le champ Version est obligatoire")
     * @ORM\Column(name="version", type="string", length=255)
     */
    private $version;

    /**
     * @var string
     * @Assert\NotBlank(message = "Le champ Code IATA est obligatoire")
     * @Assert\Length(
     *     min=3,
     *     max=3,
     *     minMessage = "Le champ Code IATA doit contenir {{ limit }} caractères minimum",
     *     maxMessage = "Le champ Code IATA doit contenir {{ limit }} caractères maximum",
     *     exactMessage = "Le champ Code IATA doit contenir {{ limit }} caractères"
     * )
     * @ORM\Column(name="codeIATA", type="string", length=10)
     */
    private $codeIATA;

    /**
     * @var string
     * @Assert\NotBlank(message = "Le champ Code OACI est obligatoire")
     * @Assert\Length(
     *     min=4,
     *     max=4,
     *     minMessage = "Le champ Code OACI doit contenir {{ limit }} caractères minimum",
     *     maxMessage = "Le champ Code OACI doit contenir {{ limit }} caractères maximum",
     *     exactMessage = "Le champ Code OACI doit contenir {{ limit }} caractères"
     * )
     * @ORM\Column(name="codeOACI", type="string", length=10)
     */
    private $codeOACI;

    /**
     * @var int
     * @Assert\NotBlank(message = "Le champ Capacité Siège est obligatoire")
     * @Assert\Range(
     *      min = 0,
     *      max = 9999,
     *      minMessage = "La valeur minimum Capacité Siège doit être : {{ limit }}",
     *      maxMessage = "La valeur maximum Capacité Siège doit être : {{ limit }}",
     * )
     * @ORM\Column(name="capaciteSiege", type="integer")
     */
    private $capaciteSiege;

    /**
     * @var int
     * @Assert\NotBlank(message = "Le champ Temps de demi-tour est obligatoire")
     * @Assert\Range(
     *      min = 0,
     *      max = 9999,
     *      minMessage = "La valeur minimum Temps de demi-tour doit être : {{ limit }}",
     *      maxMessage = "La valeur maximum Temps de demi-tour doit être : {{ limit }}",
     * )
     * @ORM\Column(name="tempsDemiTour", type="smallint")
     */
    private $tempsDemiTour;


    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="AirCorsica\XKPlanBundle\Entity\TempsDeVol",  mappedBy="typeAvion",orphanRemoval=true, cascade={"persist"})
     */
    private $tempsDeVol;

    /**
     * TypeAvion constructor.
     */
    public function __construct()
    {
        $this->tempsDeVol = new ArrayCollection();
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
     * Set version
     *
     * @param string $version
     *
     * @return TypeAvion
     */
    public function setVersion($version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Get version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Set codeIATA
     *
     * @param string $codeIATA
     *
     * @return TypeAvion
     */
    public function setCodeIATA($codeIATA)
    {
        $this->codeIATA = $codeIATA;

        return $this;
    }

    /**
     * Get codeIATA
     *
     * @return string
     */
    public function getCodeIATA()
    {
        return $this->codeIATA;
    }

    /**
     * Set codeOACI
     *
     * @param string $codeOACI
     *
     * @return TypeAvion
     */
    public function setCodeOACI($codeOACI)
    {
        $this->codeOACI = $codeOACI;

        return $this;
    }

    /**
     * Get codeOACI
     *
     * @return string
     */
    public function getCodeOACI()
    {
        return $this->codeOACI;
    }

    /**
     * Set capaciteSiege
     *
     * @param integer $capaciteSiege
     *
     * @return TypeAvion
     */
    public function setCapaciteSiege($capaciteSiege)
    {
        $this->capaciteSiege = $capaciteSiege;

        return $this;
    }

    /**
     * Get capaciteSiege
     *
     * @return int
     */
    public function getCapaciteSiege()
    {
        return $this->capaciteSiege;
    }

    /**
     * Set tempsDemiTour
     *
     * @param integer $tempsDemiTour
     *
     * @return TypeAvion
     */
    public function setTempsDemiTour($tempsDemiTour)
    {
        $this->tempsDemiTour = $tempsDemiTour;

        return $this;
    }

    /**
     * Get tempsDemiTour
     *
     * @return int
     */
    public function getTempsDemiTour()
    {
        return $this->tempsDemiTour;
    }

    /**
     * toString
     * @return string
     */
    public function __toString() {
        return $this->codeIATA.' => '.$this->version;
    }

    /**
     * Add tempsDeVol
     *
     * @param \AirCorsica\XKPlanBundle\Entity\TempsDeVol $tempsDeVol
     *
     * @return TypeAvion
     */
    public function addTempsDeVol(\AirCorsica\XKPlanBundle\Entity\TempsDeVol $tempsDeVol)
    {
        $this->tempsDeVol[] = $tempsDeVol;

        return $this;
    }

    /**
     * Remove tempsDeVol
     *
     * @param \AirCorsica\XKPlanBundle\Entity\TempsDeVol $tempsDeVol
     */
    public function removeTempsDeVol(\AirCorsica\XKPlanBundle\Entity\TempsDeVol $tempsDeVol)
    {
        $this->tempsDeVol->removeElement($tempsDeVol);
    }

    /**
     * Get tempsDeVol
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTempsDeVol()
    {
        return $this->tempsDeVol;
    }

    /**
     * Set createur
     *
     * @param \AirCorsica\XKPlanBundle\Entity\Utilisateur $createur
     *
     * @return TypeAvion
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
