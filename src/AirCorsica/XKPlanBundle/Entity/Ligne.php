<?php

namespace AirCorsica\XKPlanBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Ligne
 *
 * @ORM\Table(name="ligne", uniqueConstraints={@ORM\UniqueConstraint(name="idx_ligne_unique", columns={"aeroport_depart_id", "aeroport_arrivee_id"})})
 * @ORM\Entity(repositoryClass="AirCorsica\XKPlanBundle\Repository\LigneRepository")
 * @UniqueEntity(fields={"aeroportDepart","aeroportArrivee"}, message="Cette Ligne est déjà enregistrée")
 */
class Ligne extends AbstractXKPlan implements InterfaceComparable
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
     * @var Aeroport
     * @Assert\NotBlank(message = "Le champ Aéroport de Départ est obligatoire")
     * @ORM\ManyToOne(targetEntity="AirCorsica\XKPlanBundle\Entity\Aeroport",fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     */
    private $aeroportDepart;

    /**
     * @var Aeroport
     * @Assert\NotBlank(message = "Le champ Aéroport d'Arrivée est obligatoire")
     * @ORM\ManyToOne(targetEntity="AirCorsica\XKPlanBundle\Entity\Aeroport",fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     */
    private $aeroportArrivee;

    /**
     * @var NatureDeVol
     *
     * @ORM\ManyToMany(targetEntity="AirCorsica\XKPlanBundle\Entity\NatureDeVol")
     */
    private $naturesDeVol;

    /**
     * @var int
     * @Assert\Range(
     *      min = 0,
     *      max = 9999,
     *      minMessage = "La valeur minimum du champ Ordre doit être : {{ limit }}",
     *      maxMessage = "La valeur maximum du champ Ordre doit être : {{ limit }}",
     * )
     * @ORM\Column(name="ordre", type="smallint")
     */
    private $ordre;

    /**
     * toString
     * @return string
     */
    public function __toString() {
        return $this->getAeroportDepart()->getCodeIATA() . "-" . $this->getAeroportArrivee()->getCodeIATA();
    }

    public function __construct()
    {
        $this->naturesDeVol = new ArrayCollection();
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
     * Set aeroportDepart
     *
     * @param \AirCorsica\XKPlanBundle\Entity\Aeroport $aeroportDepart
     *
     * @return Ligne
     */
    public function setAeroportDepart(\AirCorsica\XKPlanBundle\Entity\Aeroport $aeroportDepart)
    {
        $this->aeroportDepart = $aeroportDepart;

        return $this;
    }

    /**
     * Get aeroportDepart
     *
     * @return \AirCorsica\XKPlanBundle\Entity\Aeroport
     */
    public function getAeroportDepart()
    {
        return $this->aeroportDepart;
    }

    /**
     * Set aeroportArrivee
     *
     * @param \AirCorsica\XKPlanBundle\Entity\Aeroport $aeroportArrivee
     *
     * @return Ligne
     */
    public function setAeroportArrivee(\AirCorsica\XKPlanBundle\Entity\Aeroport $aeroportArrivee)
    {
        $this->aeroportArrivee = $aeroportArrivee;

        return $this;
    }

    /**
     * Get aeroportArrivee
     *
     * @return \AirCorsica\XKPlanBundle\Entity\Aeroport
     */
    public function getAeroportArrivee()
    {
        return $this->aeroportArrivee;
    }

    /**
     * Add naturesDeVol
     *
     * @param \AirCorsica\XKPlanBundle\Entity\NatureDeVol $naturesDeVol
     *
     * @return Ligne
     */
    public function addNaturesDeVol(\AirCorsica\XKPlanBundle\Entity\NatureDeVol $naturesDeVol)
    {
        $this->naturesDeVol[] = $naturesDeVol;

        return $this;
    }

    /**
     * Remove naturesDeVol
     *
     * @param \AirCorsica\XKPlanBundle\Entity\NatureDeVol $naturesDeVol
     */
    public function removeNaturesDeVol(\AirCorsica\XKPlanBundle\Entity\NatureDeVol $naturesDeVol)
    {
        $this->naturesDeVol->removeElement($naturesDeVol);
    }

    /**
     * Get naturesDeVol
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getNaturesDeVol()
    {
        return $this->naturesDeVol;
    }

    /**
     * Set ordre
     *
     * @param integer $ordre
     *
     * @return Ligne
     */
    public function setOrdre($ordre)
    {
        $this->ordre = $ordre;

        return $this;
    }

    /**
     * Get ordre
     *
     * @return int
     */
    public function getOrdre()
    {
        return $this->ordre;
    }

    /**
     * compare 2 lignes en fonction de leur methode toString
     * @param  InterfaceComparable $subject
     * @return bool
     */
    public function compare(InterfaceComparable $subject) {
        return $this->__toString() === $subject->__toString();
    }

}
