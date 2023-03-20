<?php

namespace AirCorsica\XKPlanBundle\Repository;
use DateTime;

/**
 * SaisonRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class SaisonRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param null $orderParPeriode
     * @return mixed
     */
    public function getSaisonIATA($dateDebut, $dateFin){
        $qb = $this->createQueryBuilder('s');
        $qb->join('s.periodesSaison','ps');

        $dateMysql = DateTime::createFromFormat('d-m-Y',$dateDebut)->format('Y-m-d');
        $qb->andWhere('ps.dateDebut = :date_debut');
        $qb->setParameter('date_debut',$dateMysql);

        $dateMysql = DateTime::createFromFormat('d-m-Y',$dateFin)->format('Y-m-d');
        $qb->andWhere('ps.dateFin = :date_fin');
        $qb->setParameter('date_fin',$dateMysql);

        $query = $qb->getQuery();
        $result = $query->execute();

        return $result;
    }
}
