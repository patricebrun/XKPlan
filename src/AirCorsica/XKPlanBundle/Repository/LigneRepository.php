<?php

namespace AirCorsica\XKPlanBundle\Repository;

use AirCorsica\XKPlanBundle\Entity;
use Doctrine\DBAL\Exception\DatabaseObjectExistsException;
use Doctrine\ORM\Query\QueryException;

/**
 * LigneRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class LigneRepository extends \Doctrine\ORM\EntityRepository
{

    /**
     * Retourne sous forme de tableau une ligne en
     * fonction de son aéroport de départ et d'arrivée
     *
     * @param Entity\Aeroport $aeroport_depart
     * @param Entity\Aeroport $aeroport_arrivee
     * @return Entity\Ligne
     * @throws \Exception
     */
    public function findLigneExistante(Entity\Aeroport $aeroport_depart, Entity\Aeroport $aeroport_arrivee)
    {
        $qb = $this->createQueryBuilder('ligne');
        $qb->join('ligne.aeroportDepart','depart');
        $qb->join('ligne.aeroportArrivee','arrivee');
        $qb->where('depart = '.$aeroport_depart->getId());
        $qb->andWhere('arrivee = '.$aeroport_arrivee->getId());
        $qb->setMaxResults(1);
        $query = $qb->getQuery();
        $result = $query->execute();
        if(sizeof($result) > 1 ){
            /** @var Entity\Ligne $ligne */
            foreach ($result as $ligne){
                $iderrors[] = $ligne->getId();
            }
            throw new \LogicException("Erreur base de données : il n'est pas possible d'avoir des lignes identiques : ".implode(',',$iderrors));
        }
        if(sizeof($result) == 0){
            return null;
        }

        return current($result);
    }

    /**
     * Retourne sous forme de tableau toutes les lignes en
     * fonction de son aéroport d'arrivée
     *
     * @param Entity\Aeroport $aeroport_arrivee
     * @return array
     * @throws \Exception
     */
    public function findLigneWithArrivee(Entity\Aeroport $aeroport_arrivee)
    {
        $qb = $this->createQueryBuilder('ligne');
        $qb->join('ligne.aeroportArrivee','arrivee');
        $qb->where('arrivee = '.$aeroport_arrivee->getId());
        $qb->orderBy('arrivee.codeIATA',"ASC");
        $query = $qb->getQuery();
        $result = $query->execute();

        if(sizeof($result) == 0){
            return null;
        }

        return $result;
    }

    /**
     * Retourne sous forme de tableau toutes les lignes en
     * fonction de leur ordre (si ordre = 0 les lignes ne sont pas retournées)
     *
     * @return array
     */
    public function findAllByOrdre()
    {
        $qb = $this->createQueryBuilder('ligne');
        $qb->where('ligne.ordre <> 0');
        $qb->orderBy('ligne.ordre',"ASC");
        $query = $qb->getQuery();
        $result = $query->execute();

        return $result;
    }

    /**
     * Retourne sous forme de tableau toutes les lignes en
     * fonction qui n'ont pas d'ordre (ordre = 0
     *
     * @return array
     */
    public function findAllNonOrdonnee($aeroportDepart = array(), $inclus = "IN")
    {
        $qb = $this->createQueryBuilder('ligne');
        $qb->join('ligne.aeroportArrivee','arrivee');
        $qb->where('ligne.ordre = 0');
        $qb->orderBy('arrivee.codeIATA');

        if(sizeof($aeroportDepart)){
            $qb->join('ligne.aeroportDepart','depart');
            $qb->andWhere('depart.codeIATA  '.$inclus.' (:aDepart)');
            $qb->addOrderBy('depart.codeIATA');
            $qb->setParameter('aDepart',$aeroportDepart);
        }

        $query = $qb->getQuery();
        $result = $query->execute();

        return $result;
    }
}