<?php

namespace AirCorsica\XKPlanBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * CodeInterne
 *
 * @ORM\Table(name="code_interne")
 * @ORM\Entity(repositoryClass="AirCorsica\XKPlanBundle\Repository\CodeInterneRepository") *
 */
class CodeInterne extends AbstractXKPlan
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
     * @ORM\OneToMany(targetEntity="CodeSharePrecharge", mappedBy="codeInterne", orphanRemoval=true, cascade={"persist"})
     */
    private $codesShares;


    public function __construct()
    {
        $this->codesShares = new ArrayCollection();
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
     * Set libelle
     *
     * @param string $libelle
     *
     * @return CodeInterne
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
     * Add codesShare
     *
     * @param \AirCorsica\XKPlanBundle\Entity\CodeSharePrecharge $codesShare
     *
     * @return CodeInterne
     */
    public function addCodesShare(\AirCorsica\XKPlanBundle\Entity\CodeSharePrecharge $codesShare)
    {
        $this->codesShares[] = $codesShare;

        return $this;
    }

    /**
     * Remove codesShare
     *
     * @param \AirCorsica\XKPlanBundle\Entity\CodeSharePrecharge $codesShare
     */
    public function removeCodesShare(\AirCorsica\XKPlanBundle\Entity\CodeSharePrecharge $codesShare)
    {
        $this->codesShares->removeElement($codesShare);
    }

    /**
     * Get codesShares
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCodesShares()
    {
        return $this->codesShares;
    }

    /**
     * toString
     * @return string
     */
    public function __toString() {
        return $this->getLibelle();
    }

    /**
     * Set createur
     *
     * @param \AirCorsica\XKPlanBundle\Entity\Utilisateur $createur
     *
     * @return CodeInterne
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
