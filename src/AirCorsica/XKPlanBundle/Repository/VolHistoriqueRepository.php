<?php

namespace AirCorsica\XKPlanBundle\Repository;

use AirCorsica\XKPlanBundle\Entity\Vol;
use DateTime;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use AirCorsica\XKPlanBundle\Entity;
use Symfony\Component\HttpFoundation\Request;

/**
 * VolHistoriqueRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class VolHistoriqueRepository extends NestedTreeRepository
{

    public function getRootVolHistorique(Vol $vol){

        $qb = $this->createQueryBuilder('vh');
        $qb->where('vh.vol = :vol');
        $qb->andWhere('vh.lvl = :level');
        $qb->join("vh.vol",'v');
        $qb->setParameter('level',0);
        $qb->setParameter('vol',$vol);
        $query = $qb->getQuery();
        $result = $query->execute();

        return $result;
    }

    /**
     *
     * @param Request $request
     * @return mixed
     */
    public function getVolsFilter(Request $request,Entity\Template $template = null){
        $qb = $this->createQueryBuilder('v');
        $qb->where($qb->expr()->isInstanceOf('v',Entity\VolHistorique::class));
        $qb->join('v.template','t');
        $qb->andWhere('t.id = :template');
        $qb->setParameter('template',$template);
        if ($request->get('numerodevol')){
            if ($request->query->has('exact')){
                $qb->andWhere('v.numero = :numerodevol');
                $qb->setParameter('numerodevol',$request->get('numerodevol'));
            }else{
                $qb->andWhere('v.numero lIKE :numerodevol');
                $qb->setParameter('numerodevol','%'.$request->get('numerodevol').'%');
            }
        }

        if($request->get('aeroport_aller') || $request->get('aeroport_retour') || $request->get('ligne')){
            $qb->join('v.ligne','l');
        }

        if($request->get('aller') && !$request->get('retour')){
            if ($request->get('aeroport_aller') && $request->get('aeroport_retour')){
                $qb->join('l.aeroportDepart','ad');
                $qb->andWhere('ad.codeIATA = :aeroport_aller');
                $qb->setParameter('aeroport_aller',$request->get('aeroport_aller'));
                $qb->join('l.aeroportArrivee','aa');
                $qb->andWhere('aa.codeIATA = :aeroport_retour');
                $qb->setParameter('aeroport_retour',$request->get('aeroport_retour'));
            }else{
                if ($request->get('aeroport_aller')){
                    $qb->join('l.aeroportDepart','ad');
                    $qb->andWhere('ad.codeIATA = :aeroport_aller');
                    $qb->setParameter('aeroport_aller',$request->get('aeroport_aller'));
                }
                if ($request->get('aeroport_retour')){
                    $qb->join('l.aeroportArrivee','aa');
                    $qb->andWhere('aa.codeIATA = :aeroport_retour');
                    $qb->setParameter('aeroport_retour',$request->get('aeroport_retour'));
                }
            }
        }

        if($request->get('retour') && !$request->get('aller')){
            if ($request->get('aeroport_aller') && $request->get('aeroport_retour')){
                $qb->join('l.aeroportDepart','ad');
                $qb->andWhere('ad.codeIATA = :aeroport_aller');
                $qb->setParameter('aeroport_aller',$request->get('aeroport_retour'));
                $qb->join('l.aeroportArrivee','aa');
                $qb->andWhere('aa.codeIATA = :aeroport_retour');
                $qb->setParameter('aeroport_retour',$request->get('aeroport_aller'));
            }else{
                if ($request->get('aeroport_aller')){
                    $qb->join('l.aeroportArrivee','aa');
                    $qb->andWhere('aa.codeIATA = :aeroport_retour');
                    $qb->setParameter('aeroport_retour',$request->get('aeroport_aller'));
                }
                if ($request->get('aeroport_retour')){
                    $qb->join('l.aeroportDepart','ad');
                    $qb->andWhere('ad.codeIATA = :aeroport_aller');
                    $qb->setParameter('aeroport_aller',$request->get('aeroport_retour'));
                }
            }

        }

        if($request->get('retour') && $request->get('aller')){
            if ($request->get('aeroport_aller') && $request->get('aeroport_retour')){
                $qb->join('l.aeroportDepart','ad');
                $qb->join('l.aeroportArrivee','aa');
                $where = "(";
                $where .= "(ad.codeIATA ='".$request->get('aeroport_aller')."' AND aa.codeIATA ='".$request->get('aeroport_retour')."')";
                $where .= " OR (ad.codeIATA ='".$request->get('aeroport_retour')."' AND aa.codeIATA ='".$request->get('aeroport_aller')."')";
                $where .= ")";
                $qb->andWhere($where);
            }else{
                if ($request->get('aeroport_aller')){
                    $qb->join('l.aeroportDepart','ad');
                    $qb->join('l.aeroportArrivee','aa');
                    $where = "(";
                    $where .= "(ad.codeIATA ='".$request->get('aeroport_aller')."')";
                    $where .= " OR (aa.codeIATA ='".$request->get('aeroport_aller')."')";
                    $where .= ")";
                    $qb->andWhere($where);
                }

                if ($request->get('aeroport_retour')){
                    $qb->join('l.aeroportDepart','ad');
                    $qb->join('l.aeroportArrivee','aa');
                    $where = "(";
                    $where .= "(ad.codeIATA ='".$request->get('aeroport_retour')."')";
                    $where .= " OR (aa.codeIATA ='".$request->get('aeroport_retour')."')";
                    $where .= ")";
                    $qb->andWhere($where);
                }
            }
        }

        if ($request->get('ligne') && !is_array($request->get('ligne'))){
            $qb->andWhere('v.ligne = :ligne');
            $qb->setParameter('ligne',$request->get('ligne'));
        }
        if ($request->get('ligne') && is_array($request->get('ligne'))){
            $qb->andWhere('v.ligne IN (:ligne)');
            $qb->setParameter('ligne',$request->get('ligne'));
        }

        if ($request->get('avion')){
            $qb->andWhere('v.avion = :idavion');
            $qb->setParameter('idavion',$request->get('avion'));
        }

        if ($request->get('modif') && $request->get('date_debut_modif') && $request->get('date_fin_modif')){

            $dateFinMysql =DateTime::createFromFormat('d-m-Y',$request->get('date_fin_modif'))->format('Y-m-d');
            $qb->andWhere('v.dateTimeModification <= :date_fin_modif');
            $qb->setParameter('date_fin_modif',$dateFinMysql);

            $dateDebutMysql =DateTime::createFromFormat('d-m-Y',$request->get('date_debut_modif'))->format('Y-m-d');
            $qb->andWhere('v.dateTimeModification >= :date_debut_modif');
            $qb->setParameter('date_debut_modif',$dateDebutMysql);
        }

        if($request->query->has('msg_envoye') || $request->query->has('msg_non_envoye')){
            $qb->join('v.periodeDeVol','pv');
        }

        if ($request->query->has('msg_envoye') && !$request->query->has('msg_non_envoye') ){
            $qb->andWhere('pv.etat NOT LIKE :etat');
            $qb->setParameter('etat','%ending%');
        }

        if ($request->query->has('msg_non_envoye') && !$request->query->has('msg_envoye')){
            $qb->andWhere('pv.etat LIKE :etat');
            $qb->setParameter('etat','%ending%');
        }

        if ($request->get('type_vol')){
            $qb->join('v.typeDeVol','tv');
            $qb->andWhere('v.typeDeVol IN (:type_vol)')
                ->setParameter('type_vol', $request->get('type_vol'));
        }

        if ($request->get('affretement')){
            $qb->join('v.affretement','af');
            $qb->andWhere('v.affretement IN (:affretement)')
                ->setParameter('affretement', $request->get('affretement'));
        }


        if ($request->get('type_avion')){
            $qb->join('v.avion','av');
            $qb->join('av.typeAvion','ta');
            $qb->andWhere('ta.id = :type_avion');
            $qb->setParameter('type_avion',$request->get('type_avion'));
        }

        if ($request->get('compagnie')){
            $qb->join('v.compagnie','cp');
            $qb->andWhere('cp.id = :compagnie');
            $qb->setParameter('compagnie',$request->get('compagnie'));
        }

        if ($request->get('nature_vol')){
            $qb->join('v.naturesDeVol','nv');
            $qb->andWhere('nv.id = :nature_vol');
            $qb->setParameter('nature_vol',$request->get('nature_vol'));
        }

        if ($request->get('jours')){
            if(!in_array('pv',$qb->getAllAliases())){
                $qb->join('v.periodeDeVol','pv');
            }
            $where = "(";
            foreach ($request->get('jours') as $key=>$jour){
                if($jour!= "-")
                {
                    $where .= 'pv.joursDeValidite LIKE \'%'.$jour.'%\' OR ';
                }
            }
            $where = substr($where,0,-3);
            $where .= ")";
            $qb->andWhere($where);
        }

        if ($request->get('date_debut')){
            if(!in_array('pv',$qb->getAllAliases())){
                $qb->join('v.periodeDeVol','pv');
            }
            $dateMysql =DateTime::createFromFormat('d-m-Y',$request->get('date_debut'))->format('Y-m-d');
            $qb->andWhere('pv.dateFin >= :date_debut');
            $qb->setParameter('date_debut',$dateMysql);
        }

        if ($request->get('date_fin')){
            if(!in_array('pv',$qb->getAllAliases())){
                $qb->join('v.periodeDeVol','pv');
            }
            $dateMysql =DateTime::createFromFormat('d-m-Y',$request->get('date_fin'))->format('Y-m-d');
            $qb->andWhere('pv.dateDebut <= :date_fin');
            $qb->setParameter('date_fin',$dateMysql);
        }

        if ($request->get('deleste') !== null && ($request->get('deleste') == 0 || $request->get('deleste') == 1)){
            if(!in_array('pv',$qb->getAllAliases())){
                $qb->join('v.periodeDeVol','pv');
            }
            $qb->andWhere('pv.deleste = :deleste');
            $qb->setParameter('deleste',$request->get('deleste'));
        }

        $qb->orderBy('v.root, v.lft', 'ASC');

        $query = $qb->getQuery();
        $result = $query->getArrayResult();

        return $result;
    }
}