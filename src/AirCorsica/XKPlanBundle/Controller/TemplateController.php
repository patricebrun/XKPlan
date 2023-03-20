<?php

namespace AirCorsica\XKPlanBundle\Controller;

use AirCorsica\XKPlanBundle\Entity\PeriodeSaison;
use AirCorsica\XKPlanBundle\Entity\Flux;
use AirCorsica\XKPlanBundle\Entity\Saison;
use AirCorsica\XKPlanBundle\Entity\Template;
use AirCorsica\XKPlanBundle\Entity\Vol;
use AirCorsica\XKPlanBundle\Entity\VolHistorique;
use AirCorsica\XKPlanBundle\Repository\VolRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * Template controller.
 *
 */
class TemplateController extends Controller
{
    /**
     * * Creates a new template entity.
     * @Security("has_role('ROLE_ADMIN')")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction(Request $request,Template $templateOrigine = null)
    {

        $em = $this->getDoctrine()->getManager();

        //$templates = $em->getRepository('AirCorsicaXKPlanBundle:Template')->findAll();
        //On cherche uniquement les templates actifs (cad inactif = 0)
        $criteria = array('inactif'=>'0');
        $templates = $em->getRepository('AirCorsicaXKPlanBundle:Template')->findBy($criteria);

        $templateNew = new Template();

        if($request->get('id')){
            $currentTemplate = $em->getRepository('AirCorsicaXKPlanBundle:Template')->find($request->get('id'));
            $formTemplate = $currentTemplate;
        }else{
            $templateSession = $this->get('session')->get('template');
            $currentTemplate = $em->getRepository('AirCorsicaXKPlanBundle:Template')->find($templateSession);
            $formTemplate = $templateNew;
        }

        $saisons = $em->getRepository('AirCorsicaXKPlanBundle:Saison')->findAll();


        $form = $this->createForm('AirCorsica\XKPlanBundle\Form\TemplateType', $formTemplate,array(
            'attr' => array(
                'id'     => 'form_template_edit',
            )
        ));


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($templateNew);
            $em->flush($templateNew);

            return $this->redirectToRoute('template_index', array('id' => $templateNew->getId()));
        }

        if($this->get('session')->get('errors')){
            $errors = $this->get('session')->get('errors');
            $this->get('session')->remove('errors');

            $errorsDates = $this->get('session')->get('errorsDates');
        }else{
            $errors = array();
            $errorsDates = array();
        }

        return $this->render('AirCorsicaXKPlanBundle:Template:gestion.html.twig', array(
            'templates' => $templates,
            'saisons'   => $saisons,
            'request'   => $request,
            'errors'   => $errors,
            'errorsDates'   => $errorsDates,
            'template' => $templateNew,
            'currentTemplate' => $currentTemplate,
            'form' => $form->createView(),
        ));
    }

    /**
     * Creates a new template entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function newAction(Request $request)
    {
        $template = new Template();

        //seul le template 1 a la valeur true
        // donc non affiché dans le form de création
        $template->setProduction(false);

        $template_production = $template->getProduction();

        if(!$request->isMethod("post") && $template_production != 0){
            $fluxRocade = new Flux();
            $fluxRocade->setType('rocade');
            $fluxAltea = new Flux();
            $fluxAltea->setType('altea');
            $template->addFlux($fluxRocade);
            $template->addFlux($fluxAltea);
        }

        $form = $this->createForm('AirCorsica\XKPlanBundle\Form\TemplateType', $template);

        if ($template_production == 0) {
            $form->remove('flux');
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($template_production != 0) {
                $fluxx = $template->getFlux();
                $fluxx[0]->setType('rocade');
                $fluxx[1]->setType('altea');
            }
            $em = $this->getDoctrine()->getManager();
            $em->persist($template);
            $em->flush($template);

            //return $this->redirectToRoute('template_index');

            return $this->redirect(
                $this->generateUrl('template_index') . '?id='.$template->getId()
            );
        }

        return $this->render('AirCorsicaXKPlanBundle:Template:new.html.twig', array(
            'template' => $template,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a template entity.
     *
     */
    public function showAction(Template $template)
    {
        $deleteForm = $this->createDeleteForm($template);

        return $this->render('AirCorsicaXKPlanBundle:Template:show.html.twig', array(
            'template' => $template,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing template entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function editAction(Request $request, Template $template)
    {
        $deleteForm = $this->createDeleteForm($template);

        /* Recup statut template*/
        $template_production = $template->getProduction();

        $editForm = $this->createForm('AirCorsica\XKPlanBundle\Form\TemplateType', $template);
        if ($template_production == 0) {
            $editForm->remove('flux');
        }

        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirect(
                $this->generateUrl('template_index') . '?id='.$template->getId()
            );

            //return $this->redirectToRoute('template_edit', array('id' => $template->getId()));
        }

        return $this->render('AirCorsicaXKPlanBundle:Template:edit.html.twig', array(
            'template' => $template,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a template entity.
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function deleteAction(Request $request, Template $template)
    {
        /*
        $form = $this->createDeleteForm($template);
        $form->handleRequest($request);

        //if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($template);
            $em->flush($template);
        //}
        */
        //on met le template à inactif
        $template->setInactif(1);
        $em = $this->getDoctrine()->getManager();
        $em->flush($template);

        $session = $request->getSession();
        //return $this->redirectToRoute('template_index');

        //Programme REEL
        return $this->redirect(
            $this->generateUrl('template_index') . '?id=1'
        );
    }

    /**
     * Changement de template
     *
     * @param Template $template
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function switchAction(Template $template){
        $session = $this->get('session');
        $session->set('template',$template->getId());

        return $this->redirectToRoute('vol_liste');
    }

    /**
     * Affichage nom du template
     */
    public function displayTemplateNameAction($id){

        $em = $this->getDoctrine()->getManager();
        $repoTemplate = $em->getRepository('AirCorsicaXKPlanBundle:Template');
        $template = $repoTemplate->find($id);

        return new Response($template->getLibelle());
    }

    /**
     * Purge d'un template
     *
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function purgeAction($id){

        $em = $this->getDoctrine()->getManager();
        $repoTemplate = $em->getRepository('AirCorsicaXKPlanBundle:Template');
        $template = $repoTemplate->find($id);
	    $vols = $template->getVols();
	    $template->purge($em,$vols);
        $em->flush();
        return $this->redirectToRoute('vol_liste');
    }

    /**
     * injecte la date de début et fin de la période cible dans tous les vols
     *
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function garderAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $repoPeriode = $em->getRepository('AirCorsicaXKPlanBundle:PeriodeSaison');
        $periodesaison = $repoPeriode->find($request->get('id_periode_cible'));
        $repoVol = $em->getRepository('AirCorsicaXKPlanBundle:Vol');
        foreach ($request->get('id') as $idVol){
            $vol = $repoVol->find($idVol);
            $periodeDeVol = $vol->getPeriodeDeVol();
            $periodeDeVol->setDateDebut($periodesaison->getDateDebut());
            $periodeDeVol->setDateFin($periodesaison->getDateFin());
        }

        $em->flush();
        //$request->getSession()->getFlashBag()->add('copie_template', 'Dates des vols modifiées avec succès');

        return $this->redirectToRoute('template_index');
    }

    /**
     * injecte les dates choisie dans les vols
     *
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function corrigerAction(Request $request){
        $datesDebut = $request->get('dateDebut');
        $datesFin = $request->get('dateFin');
        $em = $this->getDoctrine()->getManager();
        $repoVol = $em->getRepository('AirCorsicaXKPlanBundle:Vol');
        foreach ($request->get('id') as $idVol){
            $dateDebut = \DateTime::createFromFormat('j-m-Y',$datesDebut[$idVol]);
            $dateFin = \DateTime::createFromFormat('j-m-Y',$datesFin[$idVol]);
            $vol = $repoVol->find($idVol);
            $periodeDeVol = $vol->getPeriodeDeVol();
            $periodeDeVol->setDateDebut($dateDebut);
            $periodeDeVol->setDateFin($dateFin);
        }

        $em->flush();
        //$request->getSession()->getFlashBag()->add('copie_template', 'Dates des vols modifiées avec succès');
        return $this->redirect(
            $this->generateUrl('template_index') . '?id='.$this->get('session')->get('template').'#results'
        );
    }

    /*
     * Copie des templates
     *
     *
     * @Security("has_role('ROLE_ADMIN')")
     *
     */
    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function copieAction(Request $request){
        ini_set('max_execution_time', 0);
        $idUnique = array();
        $em = $this->getDoctrine()->getManager();
        $repoTemplate = $em->getRepository('AirCorsicaXKPlanBundle:Template');
        $templateCible = $repoTemplate->find($request->get('templateCible'));
        $templateSource = $repoTemplate->find($request->get('templateSource'));
        //$isADuplicationAction = boolval( $repoTemplate->find($request->get('duplicationTemplateAction')) );

        if ($templateCible->getId() == $templateSource->getId()){
            //throw new \Exception('Les templates cible et source doivent être différents.');
            $request->getSession()->getFlashBag()->add('copie_template_errors','Les templates cible et source doivent être différents');
            return $this->redirect(
                $this->generateUrl('template_index') . '?id='.$templateSource->getId().'#results'
            );
        }

        $repoSaison = $em->getRepository('AirCorsicaXKPlanBundle:Saison');
        $repoPeriodeSaison = $em->getRepository('AirCorsicaXKPlanBundle:PeriodeSaison');
        /** @var Saison $saisonSource */
        $saisonSource = $repoSaison->find($request->get('saisonSource'));
        /** @var Saison $saisonCible */
        $saisonCible = $repoSaison->find($request->get('saisonCible'));
        $equalSaison = $saisonSource->getId() == $saisonCible->getId();

        if(1 == $templateCible->getProduction() && !$equalSaison){
            //throw new \Exception('Si le template cible est le template de production, la saison cible doit être la même que la source.');
            $request->getSession()->getFlashBag()->add('copie_template_errors','Erreur de copie : si le template cible est le template de production, la saison cible doit être la même que la source.');
            return $this->redirect(
                $this->generateUrl('template_index') . '?id='.$templateSource->getId().'#results'
            );
        }
	    /** @var VolRepository $repoVol */
        $repoVol = $em->getRepository('AirCorsicaXKPlanBundle:Vol');
	    $volsCible = $repoVol->getVolsInOrIntersectSaison($templateCible,$saisonCible);
	    $volsHistoriqueCible = $repoVol->getVolsHistoriqueInOrIntersectSaison($templateCible,$saisonCible);
        if($equalSaison){
	        $vols = $repoVol->getVolsInOrIntersectSaison($templateSource,$saisonSource);
            $templateCible->purge($em,$volsCible);
	        $em->flush();
            $templateCible->purge($em,$volsHistoriqueCible);
	        $em->flush();
            /** @var Vol $vol */
            foreach ($vols as $vol){
            	$stamp = $vol->toStamp();
            	if(in_array($stamp,$idUnique)){
            		continue;
	            }
                $volNew = clone $vol;
                $volNew->setId('');
                $volNew->setTemplate($templateCible);
                $volHistoriqueNew = VolHistorique::createFromVol($volNew);
                Vol::saveVolInHistorique($volHistoriqueNew,$em);
                $volNew->setVolHistoriqueParentable($volHistoriqueNew);
                $volHistoriqueNew->setVolHistorique($volNew);
                $em->persist($volNew);
	            $idUnique[] = $stamp;
            }
            $em->flush();
            $request->getSession()->getFlashBag()->add('copie_template', 'Recopie des templates effectuée avec succès');
            return $this->redirect(
                $this->generateUrl('template_index') . '?id='.$templateSource->getId().'#results'
            );
        }else{
            $errors = array();
            $errorsDates = array();

            if($saisonSource->getNom()[0] !== $saisonCible->getNom()[0]){
                $request->getSession()->getFlashBag()->add('copie_template_errors','Erreur de copie : vous de devez copier une saison W vers W ou S vers S.');
                return $this->redirect(
                    $this->generateUrl('template_index') . '?id='.$templateSource->getId().'#results'
                );
            }

            if($saisonSource->getPeriodesSaison()->count() != $saisonCible->getPeriodesSaison()->count()){
                $request->getSession()->getFlashBag()->add('copie_template_errors','Erreur de copie : la saison cible doit avoir le même nombre de période que la saison source');
                return $this->redirect(
                    $this->generateUrl('template_index') . '?id='.$templateSource->getId().'#results'
                );
            }

            $templateCible->purge($em,$volsCible);
	        $em->flush();
            $orderedPeriodesSaisonSource = $repoPeriodeSaison->getOdererdPeriodesSaison($saisonSource);
            if(0 === sizeof($orderedPeriodesSaisonSource)){
                $orderedPeriodesSaisonSource = $repoPeriodeSaison->getOdererdPeriodesSaison($saisonSource,false);
            }
            $orderedPeriodesSaisonCible= $repoPeriodeSaison->getOdererdPeriodesSaison($saisonCible);
            if(0 === sizeof($orderedPeriodesSaisonCible)){
                $orderedPeriodesSaisonCible = $repoPeriodeSaison->getOdererdPeriodesSaison($saisonCible,false);
            }
            $volsTraites = array();
            foreach ($orderedPeriodesSaisonSource as $i=>$orderedPeriodeSaisonSource){
                /** @var PeriodeSaison $orderedPeriodeSaisonCible */
                $orderedPeriodeSaisonCible = $orderedPeriodesSaisonCible[$i];
                $yearDebutPeriodeCible = $orderedPeriodeSaisonCible->getDateDebut()->format('Y');
                $yearFinPeriodeCible = $orderedPeriodeSaisonCible->getDateFin()->format('Y');
                $yearDebutPeriodeSource= $orderedPeriodeSaisonSource->getDateDebut()->format('Y');
                $vols = $repoVol->getVolsInOrIntersectPeriode($templateSource,$orderedPeriodeSaisonSource);
                /** @var Vol $vol */
                foreach ($vols as $j=>$vol){

	                $stamp = $vol->toStamp();
                    if(in_array($vol->getId(),$volsTraites) || in_array($stamp,$idUnique)){
                        continue;
                    }

                    if(in_array($vol->getId(),$volsTraites)){
                        continue;
                    }

                    $aCodesShares = $vol->getCodesShareVol();
                    $volNew = clone $vol;
                    $volNew->setId('');
                    $volNew->setTemplate($templateCible);
                    $volNew->getCodesShareVol()->clear();
                    $aCodeShare = array();
                    /** @var CodeShareVol $codeShare */
                    foreach ($aCodesShares as $codeShare){
                        $aCodeShare[] = $codeShare->getLibelle();
                    }
                    $volNew->setCodesShareFromList($aCodeShare);



                    /*
                     * @todo à refaire
                     */
                    $volNew->getPeriodeDeVol()->getDateDebut()->setDate(
                        $yearDebutPeriodeCible,
                        $orderedPeriodeSaisonCible->getDateDebut()->format('m'),
                        $orderedPeriodeSaisonCible->getDateDebut()->format('d')
                    );

                    $volNew->getPeriodeDeVol()->getDateFin()->setDate(
                        $yearFinPeriodeCible,
                        $orderedPeriodeSaisonCible->getDateFin()->format('m'),
                        $orderedPeriodeSaisonCible->getDateFin()->format('d')
                    );

                    /*
                     * Ancienne version
                     */

//                    if($orderedPeriodeSaisonCible->overlapsYear()){
//                        if($vol->getPeriodeDeVol()->overlapsYear()){
//                            if($vol->getPeriodeDeVol()->getDateDebut() < $orderedPeriodeSaisonCible->getDateDebut()){
//                                $volNew->getPeriodeDeVol()->getDateDebut()->setDate(
//                                    $yearDebutPeriodeCible - 1,
//                                    $vol->getPeriodeDeVol()->getDateDebut()->format('m'),
//                                    $vol->getPeriodeDeVol()->getDateDebut()->format('d')
//                                );
//
//                                $volNew->getPeriodeDeVol()->getDateFin()->setDate(
//                                    $yearDebutPeriodeCible,
//                                    $orderedPeriodeSaisonCible->getDateFin()->format('m'),
//                                    $orderedPeriodeSaisonCible->getDateFin()->format('d')
//                                );
//                            }elseif($vol->getPeriodeDeVol()->getDateFin() > $orderedPeriodeSaisonCible->getDateFin()){
//                                $volNew->getPeriodeDeVol()->getDateDebut()->setDate(
//                                    $yearFinPeriodeCible,
//                                    $vol->getPeriodeDeVol()->getDateDebut()->format('m'),
//                                    $vol->getPeriodeDeVol()->getDateDebut()->format('d')
//                                );
//                                $volNew->getPeriodeDeVol()->getDateFin()->setDate(
//                                    $yearFinPeriodeCible + 1,
//                                    $orderedPeriodeSaisonCible->getDateFin()->format('m'),
//                                    $orderedPeriodeSaisonCible->getDateFin()->format('d')
//                                );
//                            }else{
//                                $volNew->getPeriodeDeVol()->getDateDebut()->setDate(
//                                    $yearDebutPeriodeCible,
//                                    $vol->getPeriodeDeVol()->getDateDebut()->format('m'),
//                                    $vol->getPeriodeDeVol()->getDateDebut()->format('d')
//                                );
//                                $volNew->getPeriodeDeVol()->getDateFin()->setDate(
//                                    $yearFinPeriodeCible,
//                                    $orderedPeriodeSaisonCible->getDateFin()->format('m'),
//                                    $orderedPeriodeSaisonCible->getDateFin()->format('d')
//                                );
//                            }
//
//                        }else{
//                            if($vol->getPeriodeDeVol()->getDateDebut()->format('Y') == $yearDebutPeriodeSource){
//                                $volNew->getPeriodeDeVol()->getDateDebut()->setDate(
//                                    $yearDebutPeriodeCible,
//                                    $vol->getPeriodeDeVol()->getDateDebut()->format('m'),
//                                    $vol->getPeriodeDeVol()->getDateDebut()->format('d')
//                                );
//
//                                $volNew->getPeriodeDeVol()->getDateFin()->setDate(
//                                    $yearFinPeriodeCible,
//                                    $orderedPeriodeSaisonCible->getDateFin()->format('m'),
//                                    $orderedPeriodeSaisonCible->getDateFin()->format('d')
//                                );
//                            }else{
//                                $volNew->getPeriodeDeVol()->getDateDebut()->setDate(
//                                    $yearFinPeriodeCible,
//                                    $vol->getPeriodeDeVol()->getDateDebut()->format('m'),
//                                    $vol->getPeriodeDeVol()->getDateDebut()->format('d')
//                                );
//
//                                $volNew->getPeriodeDeVol()->getDateFin()->setDate(
//                                    $yearFinPeriodeCible,
//                                    $orderedPeriodeSaisonCible->getDateFin()->format('m'),
//                                    $orderedPeriodeSaisonCible->getDateFin()->format('d')
//                                );
//                            }
//                        }
//
//                    }else{
//                        if($vol->getPeriodeDeVol()->overlapsYear()){
//                            if($vol->getPeriodeDeVol()->getDateDebut() > $orderedPeriodeSaisonCible->getDateDebut()){
//                                $volNew->getPeriodeDeVol()->getDateDebut()->setDate(
//                                    $yearDebutPeriodeCible,
//                                    $vol->getPeriodeDeVol()->getDateDebut()->format('m'),
//                                    $vol->getPeriodeDeVol()->getDateDebut()->format('d')
//                                );
//
//                                $volNew->getPeriodeDeVol()->getDateFin()->setDate(
//                                    $yearDebutPeriodeCible + 1,
//                                    $orderedPeriodeSaisonCible->getDateFin()->format('m'),
//                                    $orderedPeriodeSaisonCible->getDateFin()->format('d')
//                                );
//                            }else{
//                                $volNew->getPeriodeDeVol()->getDateDebut()->setDate(
//                                    $yearDebutPeriodeCible -1 ,
//                                    $orderedPeriodeSaisonCible->getDateDebut()->format('m'),
//                                    $orderedPeriodeSaisonCible->getDateDebut()->format('d')
//                                );
//
//                                $volNew->getPeriodeDeVol()->getDateFin()->setDate(
//                                    $yearDebutPeriodeCible,
//                                    $vol->getPeriodeDeVol()->getDateFin()->format('m'),
//                                    $vol->getPeriodeDeVol()->getDateFin()->format('d')
//                                );
//                            }
//
//                        }else{
//                            $volNew->getPeriodeDeVol()->getDateDebut()->setDate(
//                                $yearDebutPeriodeCible,
//                                $vol->getPeriodeDeVol()->getDateDebut()->format('m'),
//                                $vol->getPeriodeDeVol()->getDateDebut()->format('d')
//                                );
//
//                            $volNew->getPeriodeDeVol()->getDateFin()->setDate(
//                                $yearDebutPeriodeCible,
//                                $vol->getPeriodeDeVol()->getDateFin()->format('m'),
//                                $vol->getPeriodeDeVol()->getDateFin()->format('d')
//                            );
//                        }
//                    }


                    if($volNew->getPeriodeDeVol()->getDateDebut() <  $orderedPeriodeSaisonCible->getDateDebut()){
                        $volNew->getPeriodeDeVol()->setDateDebut($orderedPeriodeSaisonCible->getDateDebut());
                        $volHistoriqueNew = VolHistorique::createFromVol($volNew);
                        Vol::saveVolInHistorique($volHistoriqueNew,$em);
                        $volNew->setVolHistoriqueParentable($volHistoriqueNew);
                        $volHistoriqueNew->setVolHistorique($volNew);
                        $em->persist($volNew);
                        $errors[$j] = $volNew;
                        $errorsDates[$j] = array($volNew->getPeriodeDeVol()->getDateDebut(),$volNew->getPeriodeDeVol()->getDateFin());
                    } elseif ( $volNew->getPeriodeDeVol()->getDateFin() > $orderedPeriodeSaisonCible->getDateFin()){
                        $volNew->getPeriodeDeVol()->setDateFin($orderedPeriodeSaisonCible->getDateFin());
                        $volHistoriqueNew = VolHistorique::createFromVol($volNew);
                        Vol::saveVolInHistorique($volHistoriqueNew,$em);
                        $volNew->setVolHistoriqueParentable($volHistoriqueNew);
                        $volHistoriqueNew->setVolHistorique($volNew);
                        $em->persist($volNew);
                        $errors[$j] = $volNew;
                        $errorsDates[$j] = array($volNew->getPeriodeDeVol()->getDateDebut(),$volNew->getPeriodeDeVol()->getDateFin());
                    }else{
                        $volHistoriqueNew = VolHistorique::createFromVol($volNew);
                        Vol::saveVolInHistorique($volHistoriqueNew,$em);
                        $volNew->setVolHistoriqueParentable($volHistoriqueNew);
                        $volHistoriqueNew->setVolHistorique($volNew);
                        $em->persist($volNew);
                    }
                    $volsTraites[] = $vol->getId();
	                $idUnique[] = $stamp;
//                    $em->detach($vol);
                }
            }

            $em->flush();

            $this->get('session')->set('errors',$errors);
            $this->get('session')->set('errorsDates',$errorsDates);

            if(0 == sizeof($errors)){
                $request->getSession()->getFlashBag()->add('copie_template', 'Recopie des templates effectuée avec succès');
            }else{
                $request->getSession()->getFlashBag()->add('copie_template_errors', '<p><b>Dates de vols ambigües lors de la recopie de données !</b></p>
                                                                                     <p>Certains vols du template source possèdent des dates ambigües(dates de début de période ou date de fin de période
                                                                                        , ou les deux).</p>
                                                                                      <p>Vous pouvez spécifier unitairement les dates voulus pour chacun des vols repérés dans la liste suivante : </p>');
            }

            return $this->redirect(
                $this->generateUrl('template_index') . '?id='.$templateSource->getId().'&idCible='.$templateCible->getId().'#results'
            );
        }
    }


    /**
     * MAJ dates template
     * @Security("has_role('ROLE_ADMIN')")
     *
     */
    public function majdatesAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        foreach ($request->get('dateDebut') as $idVol => $sDatesDebut){
            $vol = $em->getRepository('AirCorsicaXKPlanBundle:Vol')->find($idVol);
            $volOrigine = clone $vol;

            $vol->updateVol($volOrigine);
        }

        $em->flush();
    }


    /**
     * Creates a form to delete a template entity.
     *
     * @param Template $template The template entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Template $template)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('template_delete', array('id' => $template->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }

}
