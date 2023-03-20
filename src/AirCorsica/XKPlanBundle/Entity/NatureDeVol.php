<?php

namespace AirCorsica\XKPlanBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * NatureDeVol
 *
 * @ORM\Table(name="nature_de_vol")
 * @ORM\Entity(repositoryClass="AirCorsica\XKPlanBundle\Repository\NatureDeVolRepository")
 */
class NatureDeVol extends AbstractXKPlan
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
     * @var int
     * @Assert\Range(
     *      min = 0,
     *      max = 9999,
     *      minMessage = "La valeur minimum du champ Ordre doit être : {{ limit }}",
     *      maxMessage = "La valeur maximum du champ Ordre doit être : {{ limit }}",
     * )
     * @ORM\Column(name="ordre", type="smallint", nullable=true)
     */
    private $ordre;

    /**
     * toString
     * @return string
     */
    public function __toString() {
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
     * @return NatureDeVol
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
     * Set ordre
     *
     * @param integer $ordre
     *
     * @return NatureDeVol
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
}

