<?php

namespace AirCorsica\XKPlanBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * TypeDeVol
 *
 * @ORM\Table(name="type_de_vol")
 * @ORM\Entity(repositoryClass="AirCorsica\XKPlanBundle\Repository\TypeDeVolRepository")
 */
class TypeDeVol extends AbstractXKPlan
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
     * @Assert\NotBlank(message = "Le champ Code Type est obligatoire")
     * @Assert\Length(
     *     min=1,
     *     max=3,
     *     minMessage = "Le champ Code Type doit contenir {{ limit }} caractères minimum",
     *     maxMessage = "Le champ Code Type doit contenir {{ limit }} caractères maximum",
     *     exactMessage = "Le champ Code Type doit contenir {{ limit }} caractères"
     * )
     * @ORM\Column(name="codeType", type="string", length=10)
     */
    private $codeType;

    /**
     * @var string
     * @Assert\NotBlank(message = "Le champ Code Service est obligatoire")
     * @Assert\Length(
     *     min=1,
     *     max=1,
     *     minMessage = "Le champ Code Service doit contenir {{ limit }} caractères minimum",
     *     maxMessage = "Le champ Code Service doit contenir {{ limit }} caractères maximum",
     *     exactMessage = "Le champ Code Service doit contenir {{ limit }} caractères"
     * )
     * @ORM\Column(name="codeService", type="string", length=10)
     */
    private $codeService;

    /**
     * @var string
     * @Assert\NotBlank(message = "Le champ Code Couleur est obligatoire")
     * @Assert\Regex(
     *     pattern="/^\#[A-Za-z0-9]{6}$/",
     *     match=true,
     *     message="Le champ Code Couleur n'a pas le bon format"
     * )
     * @ORM\Column(name="codeCouleur", type="string", length=9)
     */
    private $codeCouleur;

    /**
     * toString
     * @return string
     */
    public function __toString() {
        return $this->getCodeType().' '.$this->getCodeService();
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
     * @return TypeDeVol
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
     * Set codeType
     *
     * @param string $codeType
     *
     * @return TypeDeVol
     */
    public function setCodeType($codeType)
    {
        $this->codeType = $codeType;

        return $this;
    }

    /**
     * Get codeType
     *
     * @return string
     */
    public function getCodeType()
    {
        return $this->codeType;
    }

    /**
     * Set codeService
     *
     * @param string $codeService
     *
     * @return TypeDeVol
     */
    public function setCodeService($codeService)
    {
        $this->codeService = $codeService;

        return $this;
    }

    /**
     * Get codeService
     *
     * @return string
     */
    public function getCodeService()
    {
        return $this->codeService;
    }

    /**
     * Set codeCouleur
     *
     * @param string $codeCouleur
     *
     * @return TypeDeVol
     */
    public function setCodeCouleur($codeCouleur)
    {
        $this->codeCouleur = $codeCouleur;

        return $this;
    }

    /**
     * Get codeCouleur
     *
     * @return string
     */
    public function getCodeCouleur()
    {
        return $this->codeCouleur;
    }
}

