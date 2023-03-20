<?php

namespace AirCorsica\XKPlanBundle\Controller;


use AirCorsica\XKPlanBundle\Entity\Avion;
use AirCorsica\XKPlanBundle\Entity\Ligne;
use AirCorsica\XKPlanBundle\Entity\Vol;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class StatistiquesController extends Controller
{
    public function indexAction(Request $request, $export = null)
    {
        ini_set('max_execution_time', 0);

        $ligne_tab_vol = array();
        $ligne_tab_generaux = array();
        $ligne_tab_lignes = array();
        $em = $this->getDoctrine()->getManager();
        $repoTemplate = $em->getRepository('AirCorsicaXKPlanBundle:Template');

        $repoSaison = $em->getRepository('AirCorsicaXKPlanBundle:Saison');
        $saisons = $repoSaison->findAll();

        $repoTypeVol = $em->getRepository('AirCorsicaXKPlanBundle:TypeDeVol');
        $type_vol = $repoTypeVol->findAll();

        $repoLigne = $em->getRepository('AirCorsicaXKPlanBundle:Ligne');
        $aeroportCorse = array('AJA','BIA','CLY','FSC');
        $aLigne1 = $em->getRepository('AirCorsicaXKPlanBundle:Ligne')->findAllByOrdre();
        $aLigne2 = $em->getRepository('AirCorsicaXKPlanBundle:Ligne')->findAllNonOrdonnee($aeroportCorse,"IN");
        $aLigne3 = $em->getRepository('AirCorsicaXKPlanBundle:Ligne')->findAllNonOrdonnee($aeroportCorse,"NOT IN");
        $lignes = array_merge($aLigne1, $aLigne2 , $aLigne3);
//        $lignes    = $repoLigne->findAll();

        $repoAffretement= $em->getRepository('AirCorsicaXKPlanBundle:Affretement');
        $affretements    = $repoAffretement->findAll();

        $repoTypeAvion  = $em->getRepository('AirCorsicaXKPlanBundle:TypeAvion');
        $types_avion    = $repoTypeAvion->findAll();

        $repoCompagnie = $em->getRepository('AirCorsicaXKPlanBundle:Compagnie');
        $compagnies    = $repoCompagnie->findAll();

        if($request->get('rafraichir')){
            $repoVol = $em->getRepository('AirCorsicaXKPlanBundle:Vol');
            $repoLigne = $em->getRepository('AirCorsicaXKPlanBundle:Ligne');
            $templateCurrent = $repoTemplate->find($this->get('session')->get('template'));
            $aLigne = $request->get("ligne");
            $aLigneAllerRetour = $aLigne;
            if($request->get("allerRetour")){
                /** @var Ligne $ligne */
                foreach ($aLigne as $idLigne){
                    $ligne = $repoLigne->find($idLigne);
//                    $lignes[] = $ligne;
                    $ligneRetour = $repoLigne->findOneBy(array("aeroportDepart" => $ligne->getAeroportArrivee(),"aeroportArrivee"=>$ligne->getAeroportDepart()));
                    if(!in_array($ligneRetour->getId(),$aLigneAllerRetour)){
                        $aLigneAllerRetour[] = $ligneRetour->getId();
//                        $lignes[] = $ligneRetour;
                    }
                }

            }
            if(sizeof($aLigne)==0){
                foreach ($lignes as $ligne){
                    $aLigneAllerRetour[] = $ligne->getId();
                }
            }

            $request->query->set('ligne',$aLigneAllerRetour);

            $vols = array();
            foreach ($lignes as $ligne){
                $vols = array_merge($vols, $repoVol->getVolsFilter($request,$templateCurrent,null,$ligne))  ;
            }
//            $vols = $repoVol->getVolsFilter($request,$templateCurrent,null,1);
            $dateDebut = \DateTime::createFromFormat('j-m-Y',$request->get("date_debut"));
            $dateDebut->setTime(0,0,0);
            $dateFin = \DateTime::createFromFormat('j-m-Y',$request->get("date_fin"));
            $dateFin->setTime(0,0,0);

            $ligne_tab_vol = $this->listeVolsToAvionTab($vols,$dateDebut, $dateFin);
            $ligne_tab_lignes = $this->listeVolsToLigneTab($vols,$dateDebut, $dateFin);
            $ligne_tab_generaux = $this->listeVolsToGenerauxTab($vols,$dateDebut, $dateFin);

        }else{
            $vols = array();
        }

        if($export){
            switch ($export) {
                case 'pdf_generaux':
                    $template = 'export_pdf_generaux.html.twig';
                    break;
                case 'pdf_ligne':
                    $template = 'export_pdf_ligne.html.twig';
                    break;
                case 'pdf_avion':
                    $template = 'export_pdf_avion.html.twig';
                    break;
            }
        }else{
            $template = 'index.html.twig';
        }

        return $this->render('AirCorsicaXKPlanBundle:Statistiques:'.$template,array(
            "vols"        => $vols,
            "saisons"     => $saisons,
            'request'     => $request,
            'type_vol'    => $type_vol,
            'lignes'      => $lignes,
            'types_avion' => $types_avion,
            'compagnies'   => $compagnies,
            'affretements'   => $affretements,
            'ligne_tab_vol'   => $ligne_tab_vol,
            'ligne_tab_generaux'   => $ligne_tab_generaux,
            'ligne_tab_lignes'   => $ligne_tab_lignes,
        ));
    }

    public function exportpdfgenerauxAction(Request $request){
        $html = $this->indexAction($request,'pdf_generaux')->getContent();
        $sujet = 'Résultat généraux';
        $filename = 'XKPLAN_statistique_general';
        $this->returnPDFResponseFromHTML($html,$sujet,$filename);
    }

    public function exportpdfligneAction(Request $request){
        $html = $this->indexAction($request,'pdf_ligne')->getContent();
        $sujet = 'Résultat par ligne';
        $filename = 'XKPLAN_statistique_ligne';
        $this->returnPDFResponseFromHTML($html,$sujet,$filename);
    }

    public function exportpdfavionAction(Request $request){
        $html = $this->indexAction($request,'pdf_avion')->getContent();
        $sujet = 'Résultat par avion';
        $filename = 'XKPLAN_statistique_avion';
        $this->returnPDFResponseFromHTML($html,$sujet,$filename);
    }

    public function returnPDFResponseFromHTML($html,$sujet,$filename){
        //set_time_limit(30); uncomment this line according to your needs
        // If you are not in a controller, retrieve of some way the service container and then retrieve it
        //$pdf = $this->container->get("white_october.tcpdf")->create('vertical', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        //if you are in a controlller use :
        //$pdf = $this->get("white_october.tcpdf")->create('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf = $this->get("white_october.tcpdf")->create('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetAuthor('Air Corsica');
        $pdf->SetTitle(('XKPLAN'));
        $pdf->SetSubject($sujet);
        $pdf->setFontSubsetting(true);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->SetFont('helvetica', '', 11, '', true);
        //$pdf->SetMargins(20,20,40, true);
        $pdf->AddPage();

        $filename = $filename;

        $pdf->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
        $pdf->Output($filename.".pdf",'I'); // This will output the PDF as a response directly
    }


    /**
     * Transforme une liste de vols en lignes de tableau statistiques
     * Onglet général
     *
     * @param array $vols
     * @param \DateTime $dateDebut
     * @param \DateTime $dateFin
     * @return array
     */
    public function listeVolsToGenerauxTab(array $vols,\DateTime $dateDebut,\DateTime $dateFin){
        $ligne_tab_vol = array();
        $add = new \DateInterval("P1D");

        while ($dateDebut <= $dateFin){
            $curentDate = clone $dateDebut;
            $nbSieges = 0;
            $nbEtapes = 0;
            $tempsDeVol = 0;
            /** @var Vol $vol */
            foreach ($vols as $vol){
                $periodeDevol = $vol->getPeriodeDeVol();
                if($periodeDevol->getDeleste()){
                    continue;
                }
                $avion = $vol->getAvion();
                $typeAvion = $avion->getTypeAvion();
                $dateDebutVol = $periodeDevol->getDateDebut();
                $datefinVol = $periodeDevol->getDateFin();
                $joursValidite = $periodeDevol->getJoursDeValidite();

                if($dateDebutVol <= $curentDate && $datefinVol >= $curentDate) {
                    $jourSemaine = $dateDebut->format('N');
                    if(in_array($jourSemaine,$joursValidite)) {
                        $nbSieges +=  $typeAvion->getCapaciteSiege();
                        $nbEtapes ++;
                        $tempsDeVol += $vol->getTempsDeVol('total_minutes');
                    }
                }
            }

            if($nbEtapes != 0 ){
                $ligne_tab_vol[] = array(
                    'date'   => $curentDate,
                    'vols'     => $nbEtapes,
                    'sieges' => $nbSieges,
                    'heures'=> number_format ($tempsDeVol / 60,2),
                );
            }

            $dateDebut->add($add);
        }

        return $ligne_tab_vol;
    }

    /**
     * Transforme une liste de vols en lignes de tableau statistiques
     * Onglet Avion
     *
     * @param array $vols
     * @param \DateTime $dateDebut
     * @param \DateTime $dateFin
     * @return array
     */
    public function listeVolsToAvionTab(array $vols,\DateTime $dateDebut,\DateTime $dateFin){
        $ligne_tab_vol = array();
        /** @var Vol $vol */
        foreach ($vols as $vol){
            $periodeDevol = $vol->getPeriodeDeVol();
            if($periodeDevol->getDeleste()){
                continue;
            }
            $values = false;
            $nbSieges = 0;
            $nbEtapes = 0;
            $tempsDeVol = 0;
            $avion = $vol->getAvion();
            $avion_name = $avion->getNom();
            $ordre = $avion->getOrdre();
            $typeAvion = $avion->getTypeAvion();
            $appareil = $typeAvion->getCodeIATA();
            $compagnie = $avion->getCompagnie();
            $compagnie_name = $compagnie->getNom();
            $dateDebutVol = $periodeDevol->getDateDebut();
            $datefinVol = $periodeDevol->getDateFin();
            $joursValidite = $periodeDevol->getJoursDeValidite();
            $add = new \DateInterval("P1D");
            $dateDebutPeriode = clone $dateDebut;
            $dateFinPeriode = clone $dateFin;
            while ($dateDebutPeriode <= $dateFinPeriode){
                $curentDate = clone $dateDebutPeriode;
                if($dateDebutVol <= $curentDate && $datefinVol >= $curentDate) {
                    $jourSemaine = $dateDebutPeriode->format('N');
                    if(in_array($jourSemaine,$joursValidite)) {
                        $nbSieges +=  $typeAvion->getCapaciteSiege();
                        $nbEtapes ++;
                        $tempsDeVol += $vol->getTempsDeVol('total_minutes');
                        $values = true;
                    }

                }
                $dateDebutPeriode->add($add);
            }

            if(true == $values){
                if (array_key_exists($avion->getId(),$ligne_tab_vol)){
                    $nbEtapesTotal = $ligne_tab_vol[$avion->getId()]['etapes'] + $nbEtapes;
                    $nbSiegesTotal = $ligne_tab_vol[$avion->getId()]['sieges'] + $nbSieges;
                    $tempsDeVolTotal = $ligne_tab_vol[$avion->getId()]['heures'] + $tempsDeVol;

                    $ligne_tab_vol[$avion->getId()] = array(
                        'appareil'   => $appareil,
                        'avion'     => $avion_name,
                        'compagnie' => $compagnie_name,
                        'etapes'   => $nbEtapesTotal,
                        'sieges'   => $nbSiegesTotal,
                        'heures'=> $tempsDeVolTotal,
                        'ordre'=> $ordre,

                    );
                }else{
                    $ligne_tab_vol[$avion->getId()] = array(
                        'appareil'   => $appareil,
                        'avion'     => $avion_name,
                        'compagnie' => $compagnie_name,
                        'etapes'   => $nbEtapes,
                        'sieges'   => $nbSieges,
                        'heures'=> $tempsDeVol,
                        'ordre'=> $ordre,
                    );
                }
            }
        }

        foreach ($ligne_tab_vol as $id=>$values){
            $ligne_tab_vol[$id]['heures'] = round($values['heures']/60,2);
        }

        usort($ligne_tab_vol, function($a, $b) {
            return $a['ordre'] - $b['ordre'];
        });

        return $ligne_tab_vol;
    }

    /**
     * Transforme une liste de vols en lignes de tableau statistiques
     * Onglet lignes
     *
     * @param array $vols
     * @param \DateTime $dateDebut
     * @param \DateTime $dateFin
     * @return array
     */
    public function listeVolsToLigneTab(array $vols,\DateTime $dateDebut,\DateTime $dateFin){
//
//        $ligne_tab_vol = array();
//        $ligne_tab_vol_final = array();
//        $ligne_tab_vol_test = array();
//        $nbSieges_total = 0;
//        $nbEtapes_total = 0;
//        $tempsDeVol_total = 0;
//        // @var Vol $vol
//        foreach ($vols as $vol){
//            $periodeDevol = $vol->getPeriodeDeVol();
//            if($periodeDevol->getDeleste()){
//                continue;
//            }
//            $values = false;
//            $nbSieges = 0;
//            $nbEtapes = 0;
//            $tempsDeVol = 0;
//
//            $avion = $vol->getAvion();
//            $typeAvion = $avion->getTypeAvion();
//            $appareil = $typeAvion->getCodeIATA();
//            $compagnie = $avion->getCompagnie();
//            $compagnie_name = $compagnie->getNom();
//            $ligne = $vol->getLigne();
//            $aeroportDepart = $vol->getLigne()->getAeroportDepart();
//            $aeroportArrivee = $vol->getLigne()->getAeroportArrivee();
//            $joursValidite = $periodeDevol->getJoursDeValidite();
//            $add = new \DateInterval("P1D");
//            $dateDebutVol = $periodeDevol->getDateDebut();
//            $datefinVol = $periodeDevol->getDateFin();
//            $dateDebutPeriode = clone $dateDebut;
//            $dateFinPeriode = clone $dateFin;
//            while ($dateDebutPeriode <= $dateFinPeriode){
//                $curentDate = clone $dateDebutPeriode;
//                if($dateDebutVol <= $curentDate && $datefinVol >= $curentDate) {
//                    $jourSemaine = $dateDebutPeriode->format('N');
//                    if(in_array($jourSemaine,$joursValidite)) {
//                        $nbSieges +=  $typeAvion->getCapaciteSiege();
//                        $nbEtapes ++;
//                        $tempsDeVol += $vol->getTempsDeVol('total_minutes');
//                        $values = true;
//                    }
//                }
//                $dateDebutPeriode->add($add);
//            }
//
//            if(true == $values){
//                $id_ligne = str_replace('-','_',$ligne);
//
//                if (array_key_exists($id_ligne,$ligne_tab_vol)){
//                    $nbEtapesTotal = $ligne_tab_vol[$id_ligne]['etapes'] + $nbEtapes;
//                    $nbSiegesTotal = $ligne_tab_vol[$id_ligne]['sieges'] + $nbSieges;
//                    $tempsDeVolTotal = $ligne_tab_vol[$id_ligne]['heures'] + $tempsDeVol;
//
//                    $ligne_tab_vol[$id_ligne] = array(
//                        'appareil'   => $appareil,
//                        'ligne'     => $ligne,
//                        'aeroportDepart' => $aeroportDepart->getCodeIATA(),
//                        'aeroportArrivee' => $aeroportArrivee->getCodeIATA(),
//                        'compagnie' => $compagnie_name,
//                        'etapes'   => $nbEtapesTotal,
//                        'sieges'   => $nbSiegesTotal,
//                        'heures'=> $tempsDeVolTotal,
//                        'ligne_id'=> $id_ligne,
//                        'type'=> 'ligne'
//                    );
//                }else{
//                    $ligne_tab_vol[$id_ligne] = array(
//                        'appareil'   => $appareil,
//                        'ligne'     => $ligne,
//                        'aeroportDepart' => $aeroportDepart->getCodeIATA(),
//                        'aeroportArrivee' => $aeroportArrivee->getCodeIATA(),
//                        'compagnie' => $compagnie_name,
//                        'etapes'   => $nbEtapes,
//                        'sieges'   => $nbSieges,
//                        'heures'=> $tempsDeVol,
//                        'ligne_id'=> $id_ligne,
//                        'type'=> 'ligne'
//                    );
//                }
//            }
//        }
//        $ligne_tab_vol_ordonne = self::orderVol($ligne_tab_vol);
//        foreach ($ligne_tab_vol_ordonne as $libelle_ligne=>$values){
//
//            $values['heures']= round($values['heures']/60,2);
//
//            if(!array_key_exists($libelle_ligne,$ligne_tab_vol_test)){
//                $ligne_tab_vol_final[] = $values;
//                $aLabelRetour = explode("_",$libelle_ligne);
//                $labelRetour = $aLabelRetour[1].'_'.$aLabelRetour[0];
//
//                //pas de retour
//                if(!array_key_exists($labelRetour,$ligne_tab_vol)){
//                    $labelRetour = $libelle_ligne;
//                }
//                $ligne_tab_vol[$labelRetour]['heures']= round($ligne_tab_vol[$labelRetour]['heures']/60,2);
//                $ligneRetour = $ligne_tab_vol[$labelRetour];
//
//                if($labelRetour == $libelle_ligne){
//                    $ligne_tab_vol_final[] = array(
//                        'type'=> 'empty',
//                    );
//                    $ligne_tab_vol_final[] = array(
//                        'etapes'=> $values['etapes'],
//                        'sieges'=>$values['sieges'],
//                        'heures'=>$values['heures'],
//                        'type'=> 'total',
//                    );
//
//                    $nbSieges_total += $values['etapes'];
//                    $nbEtapes_total += $values['sieges'];
//                    $tempsDeVol_total += $values['heures'];
//
//                }else{
//                    $ligne_tab_vol_final[] = $ligneRetour;
//                    $ligne_tab_vol_final[] = array(
//                        'etapes'=> $values['etapes'] + $ligneRetour['etapes'],
//                        'sieges'=>$values['sieges'] + $ligneRetour['sieges'],
//                        'heures'=>$values['heures'] + $ligneRetour['heures'],
//                        'type'=> 'total',
//                    );
//
//                    $nbSieges_total += $values['etapes'] + $ligneRetour['etapes'];
//                    $nbEtapes_total += $values['sieges'] + $ligneRetour['sieges'];
//                    $tempsDeVol_total += $values['heures']+ $ligneRetour['heures'];
//                }
//
//                $ligne_tab_vol_test[$libelle_ligne]= 'true';
//                $ligne_tab_vol_test[$labelRetour]= 'true';
//
//            }
//        }
//
//        $ligne_tab_vol_final[]=array(
//            'etapes'=> $nbSieges_total,
//            'sieges'=> $nbEtapes_total,
//            'heures'=> $tempsDeVol_total,
//            'type'=> 'total_global',
//        );
//
//        return $ligne_tab_vol_final;


        //=========================
        // Nouvel Algo JJo like V1
        //=========================

        $ligne_tab_vol = array();
        $ligne_tab_vol_final = array();
        $ligne_tab_vol_test = array();

        $i = 0;
        /** @var Vol $vol */
        foreach ($vols as $vol){
            $periodeDevol = $vol->getPeriodeDeVol();
            if($periodeDevol->getDeleste()){
                continue;
            }
            $values = false;
            $nbSieges = 0;
            $nbEtapes = 0;
            $tempsDeVol = 0;

            $avion = $vol->getAvion();
            $typeAvion = $avion->getTypeAvion();
            $appareil = $typeAvion->getCodeIATA();
            $compagnie = $avion->getCompagnie();
            $compagnie_name = $compagnie->getNom();
            $ligne = $vol->getLigne();
            $aeroportDepart = $vol->getLigne()->getAeroportDepart();
            $aeroportArrivee = $vol->getLigne()->getAeroportArrivee();
            $joursValidite = $periodeDevol->getJoursDeValidite();
            $add = new \DateInterval("P1D");
            $dateDebutVol = $periodeDevol->getDateDebut();
            $datefinVol = $periodeDevol->getDateFin();
            $dateDebutPeriode = clone $dateDebut;
            $dateFinPeriode = clone $dateFin;
            while ($dateDebutPeriode <= $dateFinPeriode){
                $curentDate = clone $dateDebutPeriode;
                if($dateDebutVol <= $curentDate && $datefinVol >= $curentDate) {
                    $jourSemaine = $dateDebutPeriode->format('N');
                    if(in_array($jourSemaine,$joursValidite)) {
                        $nbSieges +=  $typeAvion->getCapaciteSiege();
                        $nbEtapes ++;
                        $tempsDeVol += $vol->getTempsDeVol('total_minutes');
                        $values = true;
                    }
                }
                $dateDebutPeriode->add($add);
            }

            if(true == $values){
                $id_ligne = str_replace('-','_',$ligne);

                if (array_key_exists($id_ligne,$ligne_tab_vol)){

                    //on verifie que cet avion n'existe pas déja sur cette ligne
                    $appareilExiste = false;
                    $compagnieExiste = false;
                    //on re-index le tableau pour éviter les $ligne_tab_vol[$id_ligne][0] existe $ligne_tab_vol[$id_ligne][1] existe pas et $ligne_tab_vol[$id_ligne][2] existe
                    $aTemp = array_values($ligne_tab_vol[$id_ligne]);
                    $ligne_tab_vol[$id_ligne] = $aTemp;

                    for($j=0;$j<count($ligne_tab_vol[$id_ligne]);$j++){

                        if( $ligne_tab_vol[$id_ligne][$j]['appareil'] == $appareil && $ligne_tab_vol[$id_ligne][$j]['compagnie'] == $compagnie_name ){
                            $appareilExiste = true;
                            $compagnieExiste = true;

                            $nbEtapesTotal = $ligne_tab_vol[$id_ligne][$j]['etapes'] + $nbEtapes;
                            $nbSiegesTotal = $ligne_tab_vol[$id_ligne][$j]['sieges'] + $nbSieges;
                            $tempsDeVolTotal = $ligne_tab_vol[$id_ligne][$j]['heures'] + $tempsDeVol;

                            $ligne_tab_vol[$id_ligne][$j] = array(
                                'appareil'   => $appareil,
                                'ligne'     => $ligne,
                                'aeroportDepart' => $aeroportDepart->getCodeIATA(),
                                'aeroportArrivee' => $aeroportArrivee->getCodeIATA(),
                                'compagnie' => $compagnie_name,
                                'etapes'   => $nbEtapesTotal,
                                'sieges'   => $nbSiegesTotal,
                                'heures'=> $tempsDeVolTotal,
                                'ligne_id'=> $id_ligne,
                                'type'=> 'ligne'
                            );
                            break;
                        }

                    }

                    if($appareilExiste == false && $compagnieExiste == false) {

                        $i = sizeof($ligne_tab_vol[$id_ligne]);

                        $ligne_tab_vol[$id_ligne][$i] = array(
                            'appareil' => $appareil,
                            'ligne' => $ligne,
                            'aeroportDepart' => $aeroportDepart->getCodeIATA(),
                            'aeroportArrivee' => $aeroportArrivee->getCodeIATA(),
                            'compagnie' => $compagnie_name,
                            'etapes' => $nbEtapes,
                            'sieges' => $nbSieges,
                            'heures' => $tempsDeVol,
                            'ligne_id' => $id_ligne,
                            'type' => 'ligne'
                        );

                    }

                }else{

                    $i = 0;

                    $ligne_tab_vol[$id_ligne][$i] = array(
                        'appareil'   => $appareil,
                        'ligne'     => $ligne,
                        'aeroportDepart' => $aeroportDepart->getCodeIATA(),
                        'aeroportArrivee' => $aeroportArrivee->getCodeIATA(),
                        'compagnie' => $compagnie_name,
                        'etapes'   => $nbEtapes,
                        'sieges'   => $nbSieges,
                        'heures'=> $tempsDeVol,
                        'ligne_id'=> $id_ligne,
                        'type'=> 'ligne'
                    );
                }
            }
        }

//        $ligne_tab_vol_ordonne = self::orderVol($ligne_tab_vol);

        $nbEtapes_totalglobal = 0;
        $nbSieges_totalglobal = 0;
        $tempsDeVol_totalglobal = 0;

        //Pour chaque ligne
        foreach ($ligne_tab_vol as $libelle_ligne=>$ligne_effectue_appareil){

            //si la ligne n'a pas déja été traitée
            if(!array_key_exists($libelle_ligne,$ligne_tab_vol_test)) {

                //-------------
                // ligne aller
                //-------------

                $nbEtapes_soustotal_aller = 0;
                $nbSieges_soustotal_aller = 0;
                $tempsDeVol_soustotal_aller = 0;

                //pour chaque aapareil faisant cette ligne (aller)
                foreach ($ligne_effectue_appareil as $un_appareil) {

                    $nbEtapes_soustotal_aller += $un_appareil['etapes'];
                    $nbSieges_soustotal_aller += $un_appareil['sieges'];
                    $tempsDeVol_soustotal_aller += $un_appareil['heures'];

                    //on arrondis les heures
                    $un_appareil['heures']= round($un_appareil['heures']/60,2);

                    $ligne_tab_vol_final[] = $un_appareil;

                }

                //on arrondis le sous-total des heures
                $tempsDeVol_soustotal_aller = round($tempsDeVol_soustotal_aller/60,2);

                //------------------------
                // Sous-total ligne aller
                //------------------------

                $ligne_tab_vol_final[] = array(
                    'etapes' => $nbEtapes_soustotal_aller,
                    'sieges' => $nbSieges_soustotal_aller,
                    'heures' => $tempsDeVol_soustotal_aller,
                    'type' => 'soustotal',
                );

                //--------------
                // ligne retour
                //--------------

                $nbEtapes_soustotal_retour = 0;
                $nbSieges_soustotal_retour = 0;
                $tempsDeVol_soustotal_retour = 0;

                $aLabelRetour = explode("_", $libelle_ligne);
                if($aLabelRetour[0] == $aLabelRetour[1]){
                    $labelRetour = "";
                }else{
                    $labelRetour = $aLabelRetour[1] . '_' . $aLabelRetour[0];
                }


                if (!array_key_exists($labelRetour, $ligne_tab_vol)) {//pas de retour
                    $labelRetour = $libelle_ligne;

                    //-----------
                    // Total A/R
                    //-----------

                    $ligne_tab_vol_final[] = array(
                        'etapes'=> $nbEtapes_soustotal_aller,
                        'sieges'=> $nbSieges_soustotal_aller,
                        'heures'=> $tempsDeVol_soustotal_aller,
                        'type'=> 'total',
                    );

                    //actualisation du grand total
                    $nbEtapes_totalglobal += $nbEtapes_soustotal_aller;
                    $nbSieges_totalglobal += $nbSieges_soustotal_aller;
                    $tempsDeVol_totalglobal += $tempsDeVol_soustotal_aller;

                }else{

                    $ligneRetour_effectue_appareil = $ligne_tab_vol[$labelRetour];

                    //pour chaque aapareil faisant cette ligne (retour)
                    foreach ($ligneRetour_effectue_appareil as $un_appareil) {

                        $nbEtapes_soustotal_retour += $un_appareil['etapes'];
                        $nbSieges_soustotal_retour += $un_appareil['sieges'];
                        $tempsDeVol_soustotal_retour += $un_appareil['heures'];

                        //on arrondis les heures
                        $un_appareil['heures']= round($un_appareil['heures']/60,2);

                        $ligne_tab_vol_final[] = $un_appareil;

                    }

                    //on arrondis le sous-total des heures
                    $tempsDeVol_soustotal_retour = round($tempsDeVol_soustotal_retour/60,2);

                    //------------------------
                    // Sous-total ligne retour
                    //------------------------

                    $ligne_tab_vol_final[] = array(
                        'etapes' => $nbEtapes_soustotal_retour,
                        'sieges' => $nbSieges_soustotal_retour,
                        'heures' => $tempsDeVol_soustotal_retour,
                        'type' => 'soustotal',
                    );

                    //-----------
                    // Total A/R
                    //-----------

                    $ligne_tab_vol_final[] = array(
                        'etapes'=> $nbEtapes_soustotal_aller + $nbEtapes_soustotal_retour,
                        'sieges'=> $nbSieges_soustotal_aller + $nbSieges_soustotal_retour,
                        'heures'=> $tempsDeVol_soustotal_aller + $tempsDeVol_soustotal_retour,
                        'type'=> 'total',
                    );

                    //actualisation du grand total
                    $nbEtapes_totalglobal += $nbEtapes_soustotal_aller + $nbEtapes_soustotal_retour;
                    $nbSieges_totalglobal += $nbSieges_soustotal_aller + $nbSieges_soustotal_retour;
                    $tempsDeVol_totalglobal += $tempsDeVol_soustotal_aller + $tempsDeVol_soustotal_retour;

                }

                // on enregistre les lignes déja traitées
                $ligne_tab_vol_test[$libelle_ligne] = 'true';
                $ligne_tab_vol_test[$labelRetour] = 'true';

            }
        }

        //--------------
        // Total Global
        //--------------

        $ligne_tab_vol_final[]=array(
            'etapes'=> $nbEtapes_totalglobal,
            'sieges'=> $nbSieges_totalglobal,
            'heures'=> $tempsDeVol_totalglobal,
            'type'=> 'total_global',
        );

        return $ligne_tab_vol_final;


        //=====================
        // Fin Nouvel Algo JJo
        //=====================
    }

    /**
     * ordonne les vols par aéroport de départ
     *
     * @param array $vols
     * @return array
     */
    private static function orderVol(array $vols){
        $aVolMRS = array();
        $aVolNCE = array();
        $aVolORY = array();
        $aVolCDG = array();
        $aVolLYS = array();
        $aVolTLS = array();
        $aVolNTE = array();
        $aVolBOD = array();
        $aVolTLN = array();
        $aVolEBU = array();
        $aVolDLE = array();
        $aVolCFE = array();
        $aVolCRL = array();
        $aVol = array();


        foreach ($vols as $key=>$vol){
            switch (substr($key,0,3)) {
                case "MRS":
                    $aVolMRS[$key] = $vol;
                    break;
                case "NCE":
                    $aVolNCE[$key] = $vol;
                    break;
                case "ORY":
                    $aVolCLY[$key] = $vol;
                    break;
                case "CDG":
                    $aVolCDG[$key] = $vol;
                    break;
                case "LYS":
                    $aVolLYS[$key] = $vol;
                    break;
                case "TLS":
                    $aVolFSC[$key] = $vol;
                    break;
                case "BOD":
                    $aVolTLS[$key] = $vol;
                    break;
                case "TLN":
                    $aVolFSC[$key] = $vol;
                    break;
                case "EBU":
                    $aVolEBU[$key] = $vol;
                    break;
                case "DLE":
                    $aVolDLE[$key] = $vol;
                    break;
                case "CFE":
                    $aVolCFE[$key] = $vol;
                    break;
                case "CRL":
                    $aVolCRL[$key] = $vol;
                    break;
                default:
                    $aVol[$key] = $vol;
            }
        }

        return $aVolMRS + $aVolNCE + $aVolORY + $aVolCDG + $aVolLYS + $aVolTLS + $aVolNTE + $aVolBOD +  $aVolTLN + $aVolEBU + $aVolDLE + $aVolCFE + $aVolCRL + $aVol;
    }

}


