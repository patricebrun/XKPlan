<?php

namespace AirCorsica\XKPlanBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Aeroport
 *
 * @ORM\Table(name="aeroport")
 * @ORM\Entity(repositoryClass="AirCorsica\XKPlanBundle\Repository\AeroportRepository")
 */
class Aeroport extends AbstractXKPlan
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
     * @var int
     *
     * @ORM\Column(name="terminal", type="string", length=4, nullable=true)
     */
    private $terminal;

    /**
     * @var bool
     *
     * @ORM\Column(name="coordonne", type="boolean")
     */
    private $coordonne;

    /**
     * @var string
     *
     * @ORM\Column(name="notes", type="text", nullable=true)
     */
    private $notes;

    /**
     * @Assert\NotNull(message = "Le champ Pays est obligatoire")
     * @ORM\ManyToOne(targetEntity="AirCorsica\XKPlanBundle\Entity\Pays")
     * @ORM\JoinColumn(nullable=false)
     */
    private $pays;

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
     * toString
     * @return string
     */
    public function __toString() {
        return $this->getNom()." (" . $this->getCodeIATA() . ")";
    }

    /**
     * Set nom
     *
     * @param string $nom
     *
     * @return Aeroport
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
     * @return Aeroport
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
     * Set tempsDemiTour
     *
     * @param integer $tempsDemiTour
     *
     * @return Aeroport
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
     * Set terminal
     *
     * @param string $terminal
     *
     * @return Aeroport
     */
    public function setTerminal($terminal)
    {
        $this->terminal = $terminal;

        return $this;
    }

    /**
     * Get terminal
     *
     * @return string
     */
    public function getTerminal()
    {
        if($this->terminal == '0') {
            return " ";
        }
        return $this->terminal;
    }

    /**
     * Set coordonne
     *
     * @param boolean $coordonne
     *
     * @return Aeroport
     */
    public function setCoordonne($coordonne)
    {
        $this->coordonne = $coordonne;

        return $this;
    }

    /**
     * Get coordonne
     *
     * @return bool
     */
    public function getCoordonne()
    {
        return $this->coordonne;
    }

    /**
     * Set notes
     *
     * @param string $notes
     *
     * @return Aeroport
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get notes
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set pays
     *
     * @param \AirCorsica\XKPlanBundle\Entity\Pays $pays
     *
     * @return Aeroport
     */
    public function setPays(\AirCorsica\XKPlanBundle\Entity\Pays $pays)
    {
        $this->pays = $pays;

        return $this;
    }

    /**
     * Get pays
     *
     * @return \AirCorsica\XKPlanBundle\Entity\Pays
     */
    public function getPays()
    {
        return $this->pays;
    }
}
