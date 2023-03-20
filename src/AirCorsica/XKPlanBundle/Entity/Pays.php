<?php

namespace AirCorsica\XKPlanBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Pays
 *
 * @ORM\Table(name="pays")
 * @ORM\Entity(repositoryClass="AirCorsica\XKPlanBundle\Repository\PaysRepository")
 */
class Pays extends AbstractXKPlan
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
     * @Assert\NotBlank(message = "Le champ Libelle est obligatoire")
     * @ORM\Column(name="libelle", type="string", length=255)
     */
    private $libelle;

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
     * @ORM\Column(name="code", type="string", length=255)
     */
    private $code;


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
     * @return Pays
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
     * Set code
     *
     * @param string $code
     *
     * @return Pays
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * toString
     * @return string
     */
    public function __toString() {
        return $this->libelle;
    }
}
