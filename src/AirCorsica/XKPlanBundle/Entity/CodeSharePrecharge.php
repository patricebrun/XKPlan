<?php

namespace AirCorsica\XKPlanBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * CodeShare
 * @ORM\Table(name="code_share_precharge")
 * @ORM\Entity(repositoryClass="AirCorsica\XKPlanBundle\Repository\CodeSharePrechargeRepository")
 * @UniqueEntity(fields={"libelle"}, message="Ce Code Share {{ value }} est déjà enregistré.")
 */
class CodeSharePrecharge extends AbstractCodeShare
{

    /**
     * @var CodeInterne
     * @Assert\NotBlank(message = "Le champ Code Interne est obligatoire")
     * @ORM\ManyToOne(targetEntity="AirCorsica\XKPlanBundle\Entity\CodeInterne", inversedBy="codesShares", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $codeInterne;

    /**
     * toString
     * @return string
     */
    public function __toString() {
        return $this->getLibelle();
    }

    /**
     * Set codeInterne
     *
     * @param \AirCorsica\XKPlanBundle\Entity\CodeInterne $codeInterne
     *
     * @return CodeSharePrecharge
     */
    public function setCodeInterne(\AirCorsica\XKPlanBundle\Entity\CodeInterne $codeInterne)
    {
        $this->codeInterne = $codeInterne;

        return $this;
    }

    /**
     * Get codeInterne
     *
     * @return \AirCorsica\XKPlanBundle\Entity\CodeInterne
     */
    public function getCodeInterne()
    {
        return $this->codeInterne;
    }

    /**
     * Set createur
     *
     * @param \AirCorsica\XKPlanBundle\Entity\Utilisateur $createur
     *
     * @return CodeSharePrecharge
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
