<?php
/**
 * Created by PhpStorm.
 * User: damie
 * Date: 03/04/2017
 * Time: 15:45
 */

namespace AirCorsica\XKPlanBundle\Entity\Erreur;


use Doctrine\Common\Collections\ArrayCollection;

class AbstractErreur
{
    private $recommandations;

    private $problemes;

    public function __construct()
    {
        $this->recommandations = new ArrayCollection();
        $this->problemes = new ArrayCollection();
    }

    /**
     * Add recommandation
     *
     * @param string $recommandation
     *
     * @return array
     */
    public function addRecommandation($recommandation)
    {
        $this->recommandations[] = $recommandation;

        return $this;
    }

    /**
     * Remove recommandation
     *
     * @param string $recommandation
     */
    public function removeRecommandation($recommandation)
    {
        $this->recommandations->removeElement($recommandation);
    }

    /**
     * Add probleme
     *
     * @param string $probleme
     *
     * @return array
     */
    public function addProbleme($recommandation)
    {
        $this->problemes[] = $recommandation;

        return $this;
    }

    /**
     * Remove probleme
     *
     * @param string $probleme
     */
    public function removeProbleme($recommandation)
    {
        $this->problemes->removeElement($recommandation);
    }

    /**
     * @return ArrayCollection
     */
    public function getRecommandations()
    {
        return $this->recommandations;
    }

    /**
     * @param ArrayCollection $recommandations
     */
    public function setRecommandations($recommandations)
    {
        $this->recommandations = $recommandations;
    }

    /**
     * @return ArrayCollection
     */
    public function getProblemes()
    {
        return $this->problemes;
    }

    /**
     * @param ArrayCollection $problemes
     */
    public function setProblemes($problemes)
    {
        $this->problemes = $problemes;
    }


}