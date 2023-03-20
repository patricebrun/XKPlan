<?php

namespace AirCorsica\XKPlanBundle\Repository;

/**
 * ModeleSousPeriodeRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ModeleSousPeriodeRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * retourne les modèles en fonction d'une saison
     *
     * @param string $saison ete | hiver
     * @return mixed
     */
   public function getModeleFromSaison($saison){
       $qb = $this->createQueryBuilder('m');
       if('ete' == $saison){
           $qb->where('m.pourPeriodeEstivalle = 1');
       }else{
           $qb->where('m.pourPeriodeHivernalle = 1');
       }
       $query = $qb->getQuery();
       $result = $query->execute();

       return $result;
   }
}
