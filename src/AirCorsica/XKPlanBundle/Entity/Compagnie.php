<?php

namespace AirCorsica\XKPlanBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Compagnie
 *
 * @ORM\Table(name="compagnie")
 * @ORM\Entity(repositoryClass="AirCorsica\XKPlanBundle\Repository\CompagnieRepository")
 */
class Compagnie extends AbstractXKPlan
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
     * @var string
     * @Assert\NotBlank(message = "Le champ Code IATA est obligatoire")
     * @Assert\Length(
     *     min=2,
     *     max=2,
     *     minMessage = "Le champ Code IATA doit contenir {{ limit }} caractères minimum",
     *     maxMessage = "Le champ Code IATA doit contenir {{ limit }} caractères maximum",
     *     exactMessage = "Le champ Code IATA doit contenir {{ limit }} caractères"
     * )
     * @ORM\Column(name="codeIATA", type="string", length=10)
     */
    private $codeIATA;

    /**
     * @var string
     *
     * @ORM\Column(name="codeAlternatif", type="string", length=255, nullable=true)
     */
    private $codeAlternatif;

    /**
     * @var string
     * @Assert\NotBlank(message = "Le champ Code OACI est obligatoire")
     * @Assert\Length(
     *     min=3,
     *     max=3,
     *     minMessage = "Le champ Code OACI doit contenir {{ limit }} caractères minimum",
     *     maxMessage = "Le champ Code OACI doit contenir {{ limit }} caractères maximum",
     *     exactMessage = "Le champ Code OACI doit contenir {{ limit }} caractères"
     * )
     * @ORM\Column(name="codeOACI", type="string", length=10, nullable=true)
     */
    private $codeOACI;

    /**
     * toString
     * @return string
     */
    public function __toString() {
        return $this->getCodeIATA().' : '.$this->getNom();
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
     * @return Compagnie
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
     * Set codeIATA
     *
     * @param string $codeIATA
     *
     * @return Compagnie
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
     * @return Compagnie
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
     * Set codeAlternatif
     *
     * @param string $codeAlternatif
     *
     * @return Compagnie
     */
    public function setCodeAlternatif($codeAlternatif)
    {
        $this->codeAlternatif = $codeAlternatif;

        return $this;
    }

    /**
     * Get codeAlternatif
     *
     * @return string
     */
    public function getCodeAlternatif()
    {
        return $this->codeAlternatif;
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
