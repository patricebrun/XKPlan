<?php

namespace AirCorsica\XKPlanBundle\Controller;

use AirCorsica\XKPlanBundle\Entity\TempsDeVol;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Tempsdevol controller.
 *
 */
class TempsDeVolController extends Controller
{
    /**
     * Lists all tempsDeVol entities.
     *
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $tempsDeVols = $em->getRepository('AirCorsicaXKPlanBundle:TempsDeVol')->findAll();

        return $this->render('tempsdevol/index.html.twig', array(
            'tempsDeVols' => $tempsDeVols,
        ));
    }

    /**
     * Creates a new tempsDeVol entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function newAction(Request $request)
    {
        $tempsDeVol = new Tempsdevol();
        $form = $this->createForm('AirCorsica\XKPlanBundle\Form\TempsDeVolType', $tempsDeVol);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($tempsDeVol);
            $em->flush($tempsDeVol);

            return $this->redirectToRoute('tempsdevol_show', array('id' => $tempsDeVol->getId()));
        }

        return $this->render('tempsdevol/new.html.twig', array(
            'tempsDeVol' => $tempsDeVol,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a tempsDeVol entity.
     *
     */
    public function showAction(TempsDeVol $tempsDeVol)
    {
        $deleteForm = $this->createDeleteForm($tempsDeVol);

        return $this->render('tempsdevol/show.html.twig', array(
            'tempsDeVol' => $tempsDeVol,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing tempsDeVol entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, TempsDeVol $tempsDeVol)
    {
            $duree = $request->query->get('minute') +  ($request->query->get('heure') * 60);
            $tempsDeVol->setDuree( $duree );
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('typeavion_edit', array('id' => $tempsDeVol->getTypeAvion()->getId()));
    }

    /**
     * Deletes a tempsDeVol entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, TempsDeVol $tempsDeVol)
    {
        $idTypeAvion = $tempsDeVol->getTypeAvion()->getId();
        $em = $this->getDoctrine()->getManager();
        $em->remove($tempsDeVol);
        $em->flush($tempsDeVol);

        return $this->redirectToRoute('typeavion_edit',array('id' => $idTypeAvion));
    }

    /**
     * Initialisation des valeurs
     * @param Request $request
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function initAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $idTypeAvion = $request->request->get('id_type');
        $repoLigne =$em->getRepository("AirCorsicaXKPlanBundle:Ligne");
        $repoAeroport =$em->getRepository("AirCorsicaXKPlanBundle:Aeroport");

        $aeroportAller = $repoAeroport->find($request->request->get('aeroport_aller'));
        $aeroportRetour = $repoAeroport->find($request->request->get('aeroport_retour'));

        $ligne = $repoLigne->findLigneExistante($aeroportAller,$aeroportRetour);

        $saisonIATA = $em->find("AirCorsicaXKPlanBundle:Saison",$request->request->get('saison'));
        $typeAvion = $em->find("AirCorsicaXKPlanBundle:TypeAvion",$idTypeAvion);


        foreach ($saisonIATA->getPeriodesSaison() as $periodeSaison){
            $repoTDVL = $em->getRepository("AirCorsicaXKPlanBundle:TempsDeVol");
            $removeAller = $repoTDVL->findBy(
                array(
                    'saison' => $periodeSaison,
                    'ligne'  => $ligne,
                    'typeAvion' => $typeAvion
                )
            );

            foreach ($removeAller as $tdvlRemove){
                $typeAvion->removeTempsDeVol($tdvlRemove);

            }
            $tempsDeVol = new TempsDeVol();
            $tempsDeVol->setLigne($ligne);
            $tempsDeVol->setSaison($periodeSaison);
            $tempsDeVol->setTypeAvion($typeAvion);
            $dureeDefaut = $request->request->get('tempsDeVolDefaut');
            $aHeureMinute = explode(":",$dureeDefaut);
            $tempsDeVol->setDuree(($aHeureMinute[0] * 60) + $aHeureMinute[1]);

            if($request->request->has('retour')){
                $repoLigne = $em->getRepository("AirCorsicaXKPlanBundle:Ligne");
                $ligneInverse = $repoLigne->findLigneExistante($ligne->getAeroportArrivee(),$ligne->getAeroportDepart());

                if(null ==! $ligneInverse && $ligneInverse != $ligne){
                    $removeRetour = $repoTDVL->findBy(
                        array(
                            'saison' => $periodeSaison,
                            'ligne'  => $ligneInverse,
                            'typeAvion' => $typeAvion
                        )
                    );

                    foreach ($removeRetour as $tdvlRemove){
                        $typeAvion->removeTempsDeVol($tdvlRemove);

                    }

                    /** @var TempsDeVol $tempsDeVolRetour */
                    $tempsDeVolRetour = clone $tempsDeVol;
                    $tempsDeVolRetour->setLigne($ligneInverse);
                    $em->persist($tempsDeVolRetour);
                }
            }
            $em->persist($tempsDeVol);
        }

        $em->persist($typeAvion);

        $em->flush();

        return $this->redirectToRoute("typeavion_edit",array('id' => $idTypeAvion));
    }

    /**
     * Creates a form to delete a tempsDeVol entity.
     *
     * @param TempsDeVol $tempsDeVol The tempsDeVol entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(TempsDeVol $tempsDeVol)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('tempsdevol_delete', array('id' => $tempsDeVol->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
