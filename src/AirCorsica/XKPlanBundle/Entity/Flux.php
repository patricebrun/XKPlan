<?php

namespace AirCorsica\XKPlanBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Flux
 *
 * @ORM\Table(name="flux")
 * @ORM\Entity(repositoryClass="AirCorsica\XKPlanBundle\Repository\FluxRepository")
 */
class Flux extends AbstractXKPlan
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
     *
     * @ORM\Column(name="pathSMB", type="string", length=255)
     */
    private $pathSMB;

    /**
     * @var string
     *
     * @ORM\Column(name="loginSMB", type="string", length=255)
     */
    private $loginSMB;

    /**
     * @var string
     *
     * @ORM\Column(name="passwordSMB", type="string", length=255)
     */
    private $passwordSMB;

    /**
     * @var string
     *
     * @ORM\Column(name="emailCible", type="string", length=255)
     */
    private $emailCible;

    /**
     * @var bool
     *
     * @ORM\Column(name="fluxActif", type="boolean")
     */
    private $fluxActif = false;

    /**
     * @var string
     *
     * @ORM\Column(name="pathCopieLocale", type="string", length=255)
     */
    private $pathCopieLocale;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var Template
     *
     * @ORM\ManyToOne(targetEntity="AirCorsica\XKPlanBundle\Entity\Template", inversedBy="flux")
     * @ORM\JoinColumn(nullable=true)
     */
    private $template;

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
     * Set pathSMB
     *
     * @param string $pathSMB
     *
     * @return Flux
     */
    public function setpathSMB($pathSMB)
    {
        $this->pathSMB = $pathSMB;

        return $this;
    }

    /**
     * Get pathSMB
     *
     * @return string
     */
    public function getpathSMB()
    {
        return $this->pathSMB;
    }

    /**
     * Set loginSMB
     *
     * @param string $loginSMB
     *
     * @return Flux
     */
    public function setLoginSMB($loginSMB)
    {
        $this->loginSMB = $loginSMB;

        return $this;
    }

    /**
     * Get loginSMB
     *
     * @return string
     */
    public function getLoginSMB()
    {
        return $this->loginSMB;
    }

    /**
     * Set passwordSMB
     *
     * @param string $passwordSMB
     *
     * @return Flux
     */
    public function setPasswordSMB($passwordSMB)
    {
        $this->passwordSMB = $passwordSMB;

        return $this;
    }

    /**
     * Get passwordSMB
     *
     * @return string
     */
    public function getPasswordSMB()
    {
        return $this->passwordSMB;
    }

    /**
     * Set emailCible
     *
     * @param string $emailCible
     *
     * @return Flux
     */
    public function setEmailCible($emailCible)
    {
        $this->emailCible = $emailCible;

        return $this;
    }

    /**
     * Get emailCible
     *
     * @return string
     */
    public function getEmailCible()
    {
        return $this->emailCible;
    }

    /**
     * Set fluxActif
     *
     * @param boolean $fluxActif
     *
     * @return Flux
     */
    public function setFluxActif($fluxActif)
    {
        $this->fluxActif = $fluxActif;

        return $this;
    }

    /**
     * Get fluxActif
     *
     * @return bool
     */
    public function getFluxActif()
    {
        return $this->fluxActif;
    }

    /**
     * Set pathCopieLocale
     *
     * @param string $pathCopieLocale
     *
     * @return Flux
     */
    public function setPathCopieLocale($pathCopieLocale)
    {
        $this->pathCopieLocale = $pathCopieLocale;

        return $this;
    }

    /**
     * Get pathCopieLocale
     *
     * @return string
     */
    public function getPathCopieLocale()
    {
        return $this->pathCopieLocale;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Flux
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set template
     *
     * @param \AirCorsica\XKPlanBundle\Entity\Template $template
     *
     * @return Flux
     */
    public function setTemplate(\AirCorsica\XKPlanBundle\Entity\Template $template = null)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * Get template
     *
     * @return \AirCorsica\XKPlanBundle\Entity\Template
     */
    public function getTemplate()
    {
        return $this->template;
    }
}
