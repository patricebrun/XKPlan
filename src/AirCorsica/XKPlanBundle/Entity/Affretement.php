<?php

namespace AirCorsica\XKPlanBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Affretement
 *
 * @ORM\Table(name="affretement")
 * @ORM\Entity(repositoryClass="AirCorsica\XKPlanBundle\Repository\AffretementRepository")
 */
class Affretement extends AbstractXKPlan
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
     * @ORM\Column(name="verrouille", type="boolean")
     */
    private $verrouille;

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
     * @return Affretement
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
     * Set verrouille
     *
     * @param boolean $verrouille
     *
     * @return Affretement
     */
    public function setVerrouille($verrouille)
    {
        $this->verrouille = $verrouille;

        return $this;
    }

    /**
     * Get verrouille
     *
     * @return bool
     */
    public function getVerrouille()
    {
        return $this->verrouille;
    }
}

