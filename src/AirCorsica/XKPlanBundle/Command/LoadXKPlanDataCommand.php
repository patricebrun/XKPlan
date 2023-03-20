<?php
// myapplication/src/sandboxBundle/Command/TestCommand.php
// Change the namespace according to your bundle
namespace AirCorsica\XKPlanBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use AirCorsica\XKPlanBundle\Entity\AdresseSITA;
use AirCorsica\XKPlanBundle\Entity\Aeroport;
use AirCorsica\XKPlanBundle\Entity\Affretement;
use AirCorsica\XKPlanBundle\Entity\Avion;
use AirCorsica\XKPlanBundle\Entity\CodeInterne;
use AirCorsica\XKPlanBundle\Entity\CodeSharePrecharge;
use AirCorsica\XKPlanBundle\Entity\CodeShareVol;
use AirCorsica\XKPlanBundle\Entity\Compagnie;
use AirCorsica\XKPlanBundle\Entity\GroupeSITA;
use AirCorsica\XKPlanBundle\Entity\Ligne;
use AirCorsica\XKPlanBundle\Entity\ModeleSousPeriode;
use AirCorsica\XKPlanBundle\Entity\NatureDeVol;
use AirCorsica\XKPlanBundle\Entity\Pays;
use AirCorsica\XKPlanBundle\Entity\Periode;
use AirCorsica\XKPlanBundle\Entity\PeriodeDeVol;
use AirCorsica\XKPlanBundle\Entity\PeriodeImmobilisation;
use AirCorsica\XKPlanBundle\Entity\PeriodeSaison;
use AirCorsica\XKPlanBundle\Entity\Saison;
use AirCorsica\XKPlanBundle\Entity\Template;
use AirCorsica\XKPlanBundle\Entity\TempsDeVol;
use AirCorsica\XKPlanBundle\Entity\TypeAvion;
use AirCorsica\XKPlanBundle\Entity\TypeDeVol;
use AirCorsica\XKPlanBundle\Entity\Vol;
use AirCorsica\XKPlanBundle\Entity\VolHistorique;
use AirCorsica\XKPlanBundle\Repository\AffretementRepository;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Mapping as ORM;

// Add the Container
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

class LoadXKPlanDataCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:load-data-xkplan')
            // the short description shown while running "php bin/console list"
            ->setDescription('Chargement des données XKPlan V1 dans la nouvelle BDD')
            // the full command description shown when running the command with
            // the "--help" option
            ->setHelp("Chargement des données XKPlan V1 dans la nouvelle BDD")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // timestamp en millisecondes du début du script (en PHP 5)
        $timestamp_debut = microtime(true);

        $doctrine = $this->getContainer()->get('doctrine');
        $manager = $doctrine->getEntityManager();
        $manager->getConnection()->getConfiguration()->setSQLLogger(null);

        $user = $this->getUser($manager);

        $mysqli=mysqli_connect("10.20.0.32","xkplan",'xkplan$pwd2016',"xkplanv1");
        $query = "SELECT pays, codePays,pays.nom,verrouille, ordre
                  FROM pays
                  ORDER BY pays.pays ASC";

        $result=mysqli_query($mysqli,$query);

        $aRowsPays = mysqli_fetch_all($result,MYSQLI_ASSOC);

        $aCorrespondanceAeroport = array();
        $aCorrespondancePays = array();

        /* Lecture des valeurs */
        foreach ($aRowsPays as $dbPays) {
            if($dbPays['verrouille'])
                continue;

            $pays = new Pays();

            $pays->setCode($dbPays['codePays']);
            $pays->setLibelle($dbPays['nom']);
            $pays->setModificateur($user);
            $pays->setCreateur($user);

            $manager->persist($pays);
            $manager->flush();
//            $manager->clear();

            $aCorrespondancePays[$dbPays['pays']] = $pays->getId();

            $queryAeroport = "SELECT aeroport.aeroport, aeroport.codeIATA, aeroport.nom, coordonne, tempsDemiTour, terminal, notes, corbeille
              FROM aeroport
              WHERE aeroport.pays = ".$dbPays['pays']."";

            $resultAeroport=mysqli_query($mysqli,$queryAeroport);

            $aRowsAeroports = mysqli_fetch_all($resultAeroport,MYSQLI_ASSOC);

            foreach ($aRowsAeroports as $dbAeroport) {
                if($dbAeroport['corbeille'])
                    continue;

                $aeroport = new Aeroport();

                $aeroport->setCodeIATA($dbAeroport['codeIATA']);
                $aeroport->setNom($dbAeroport['nom']);
                $aeroport->setCoordonne($dbAeroport['coordonne']);
                $aeroport->setTempsDemiTour($dbAeroport['tempsDemiTour']);
                $aeroport->setTerminal($dbAeroport['terminal']);
                $aeroport->setNotes($dbAeroport['notes']);

                $aeroport->setPays($pays);

                $aeroport->setModificateur($user);
                $aeroport->setCreateur($user);

                $manager->persist($aeroport);
                $manager->flush();
//                $manager->clear();

                $aCorrespondanceAeroport[$dbAeroport['aeroport']] = $aeroport->getId();
            }

        }

        gc_collect_cycles();

        $queryCompagnie = "SELECT compagnie, nom, codeIATA, codeAlternatif, codeOACI, corbeille
                  FROM compagnie
                  ORDER BY compagnie.compagnie ASC";

        $resultCompagnie=mysqli_query($mysqli,$queryCompagnie);

        $aRowsCompagnie = mysqli_fetch_all($resultCompagnie,MYSQLI_ASSOC);

        /* Lecture des valeurs */
        $aCorrespondanceCompagnie = array();
        foreach ($aRowsCompagnie as $dbCompagnie) {
            if($dbCompagnie['corbeille'])
                continue;

            $compagnie = new Compagnie();
            $compagnie->setCodeIATA($dbCompagnie['codeIATA']);
            $compagnie->setCodeOACI($dbCompagnie['codeOACI']);
            $compagnie->setNom($dbCompagnie['nom']);
            $compagnie->setCodeAlternatif($dbCompagnie['codeAlternatif']);

            $compagnie->setModificateur($user);
            $compagnie->setCreateur($user);

            $manager->persist($compagnie);
            $manager->flush();
//            $manager->clear();

            $aCorrespondanceCompagnie[$dbCompagnie['compagnie']] = $compagnie->getId();
        }

        gc_collect_cycles();

        $queryTypeAvion = "SELECT typeavion, `version`, codeIATA, codeOACI, corbeille, capacite, tempsDemiTour
                  FROM typeavion
                  ORDER BY typeavion.typeavion ASC";

        $resultTypeAvion=mysqli_query($mysqli,$queryTypeAvion);

        $aRowsTypeAvion = mysqli_fetch_all($resultTypeAvion,MYSQLI_ASSOC);

        /* Lecture des valeurs */
        $aCorrespondanceTypeAvion = array();
        foreach ($aRowsTypeAvion as $dbTypeAvion) {
            if($dbTypeAvion['corbeille'])
                continue;

            $typeAvion = new TypeAvion();
            $typeAvion->setCodeIATA($dbTypeAvion['codeIATA']);
            $typeAvion->setCodeOACI($dbTypeAvion['codeOACI']);
            $typeAvion->setVersion($dbTypeAvion['version']);
            $typeAvion->setCapaciteSiege($dbTypeAvion['capacite']);
            $typeAvion->setTempsDemiTour($dbTypeAvion['tempsDemiTour']);

            $typeAvion->setModificateur($user);
            $typeAvion->setCreateur($user);

            $manager->persist($typeAvion);
            $manager->flush();
//            $manager->clear();

            $aCorrespondanceTypeAvion[$dbTypeAvion['typeavion']] = $typeAvion->getId();
        }

        gc_collect_cycles();

        $queryAvion = "SELECT avion, nom, typeAvion, affrete, compagnie, corbeille, ordre
                  FROM avion
                  ORDER BY avion.avion ASC";

        $resultAvion=mysqli_query($mysqli,$queryAvion);

        $aRowsAvion = mysqli_fetch_all($resultAvion,MYSQLI_ASSOC);

        $aCorrespondanceAvion = array();
        /* Lecture des valeurs */
        $repoCompagnie = $manager->getRepository('AirCorsicaXKPlanBundle:Compagnie');
        $repoTypeAvion = $manager->getRepository('AirCorsicaXKPlanBundle:TypeAvion');
        $ordre = 1000;
        foreach ($aRowsAvion as $dbAvion) {

            if($dbAvion['corbeille'])
                continue;

            $compagnie = $repoCompagnie->find($aCorrespondanceCompagnie[$dbAvion['compagnie']]);
            $typeAvion = $repoTypeAvion->find($aCorrespondanceTypeAvion[$dbAvion['typeAvion']]);

            $avion = new Avion();
            $avion->setNom($dbAvion['nom']);
            $avion->setAffrete($dbAvion['affrete']);
//            if($dbAvion['ordre']=="0")
//            {
//                $avion->setOrdre($ordre);
//                $ordre++;
//            }else{
                $avion->setOrdre($dbAvion['ordre']);
//            }

            $avion->setCompagnie($compagnie);
            $avion->setTypeAvion($typeAvion);

            $avion->setModificateur($user);
            $avion->setCreateur($user);

            $manager->persist($avion);
            $manager->flush();
//            $manager->clear();

            $aCorrespondanceAvion[$dbAvion['avion']] = $avion->getId();
        }

        gc_collect_cycles();

        $queryNature = "SELECT naturePeriodeVol, nom, ordre, corbeille
                  FROM natureperiodevol
                  ORDER BY natureperiodevol.naturePeriodeVol ASC";

        $resultNature=mysqli_query($mysqli,$queryNature);

        $aRowsNature = mysqli_fetch_all($resultNature,MYSQLI_ASSOC);

        /* Lecture des valeurs */
        $aCorrespondanceNature = array();
        foreach ($aRowsNature as $dbNature) {
            if($dbNature['corbeille'])
                continue;

            $nature = new NatureDeVol();
            $nature->setNom($dbNature['nom']);
            $nature->setOrdre($dbNature['ordre']);

            $nature->setModificateur($user);
            $nature->setCreateur($user);

            $manager->persist($nature);
            $manager->flush();
//            $manager->clear();

            $aCorrespondanceNature[$dbNature['naturePeriodeVol']] = $nature->getId();
        }

        gc_collect_cycles();

        $queryLigne = "SELECT ligne, aeroportDepart, aeroportArrivee, ordre
                  FROM ligne
                  ORDER BY ligne.ligne ASC";

        $resultLigne=mysqli_query($mysqli,$queryLigne);

        $aRowsLigne = mysqli_fetch_all($resultLigne,MYSQLI_ASSOC);

        /* Lecture des valeurs */
        $aCorrespondanceLigne = array();
        $repoAeroport = $manager->getRepository('AirCorsicaXKPlanBundle:Aeroport');
        $repoNatureDeVol = $manager->getRepository('AirCorsicaXKPlanBundle:NatureDeVol');
        $aDepartArrivee = array();
        foreach ($aRowsLigne as $dbLigne) {
            $output->writeln( "Ligne ".$dbLigne['ligne']);
            /** @var Aeroport $aeroportArr */
            $aeroportArr = $repoAeroport->find($aCorrespondanceAeroport[$dbLigne['aeroportArrivee']]);
            /** @var Aeroport $aeroportDep */
            $aeroportDep = $repoAeroport->find($aCorrespondanceAeroport[$dbLigne['aeroportDepart']]);

            if(0 == sizeof($aDepartArrivee) || !isset($aDepartArrivee[$dbLigne['aeroportDepart']."-".$dbLigne['aeroportArrivee']])){
                $ligne = new Ligne();
                $ligne->setAeroportArrivee($aeroportArr);
                $ligne->setAeroportDepart($aeroportDep);

                $ordre = null;

                if($aeroportArr->getCodeIATA() == "MRS"){
                    if($aeroportDep->getCodeIATA() == "AJA"){
                        $ordre = 1;
                    }
                    if($aeroportDep->getCodeIATA() == "BIA"){
                        $ordre = 3;
                    }
                    if($aeroportDep->getCodeIATA() == "CLY"){
                        $ordre = 5;
                    }
                    if($aeroportDep->getCodeIATA() == "FSC"){
                        $ordre = 7;
                    }

                }
                if($aeroportDep->getCodeIATA() == "MRS"){
                    if($aeroportArr->getCodeIATA() == "AJA"){
                        $ordre = 2;
                    }
                    if($aeroportArr->getCodeIATA() == "BIA"){
                        $ordre = 4;
                    }
                    if($aeroportArr->getCodeIATA() == "CLY"){
                        $ordre = 6;
                    }
                    if($aeroportArr->getCodeIATA() == "FSC"){
                        $ordre = 8;
                    }

                }
                if($aeroportArr->getCodeIATA() == "NCE"){
                    if($aeroportDep->getCodeIATA() == "AJA"){
                        $ordre = 100;
                    }
                    if($aeroportDep->getCodeIATA() == "BIA"){
                        $ordre = 102;
                    }
                    if($aeroportDep->getCodeIATA() == "CLY"){
                        $ordre = 104;
                    }
                    if($aeroportDep->getCodeIATA() == "FSC"){
                        $ordre = 106;
                    }

                }
                if($aeroportDep->getCodeIATA() == "NCE"){
                    if($aeroportArr->getCodeIATA() == "AJA"){
                        $ordre = 101;
                    }
                    if($aeroportArr->getCodeIATA() == "BIA"){
                        $ordre = 103;
                    }
                    if($aeroportArr->getCodeIATA() == "CLY"){
                        $ordre = 105;
                    }
                    if($aeroportArr->getCodeIATA() == "FSC"){
                        $ordre = 107;
                    }

                }
                if($aeroportArr->getCodeIATA() == "ORY"){
                    if($aeroportDep->getCodeIATA() == "AJA"){
                        $ordre = 200;
                    }
                    if($aeroportDep->getCodeIATA() == "BIA"){
                        $ordre = 202;
                    }
                    if($aeroportDep->getCodeIATA() == "CLY"){
                        $ordre = 204;
                    }
                    if($aeroportDep->getCodeIATA() == "FSC"){
                        $ordre = 206;
                    }

                }
                if($aeroportDep->getCodeIATA() == "ORY"){
                    if($aeroportArr->getCodeIATA() == "AJA"){
                        $ordre = 201;
                    }
                    if($aeroportArr->getCodeIATA() == "BIA"){
                        $ordre = 203;
                    }
                    if($aeroportArr->getCodeIATA() == "CLY"){
                        $ordre = 205;
                    }
                    if($aeroportArr->getCodeIATA() == "FSC"){
                        $ordre = 207;
                    }

                }
                if($aeroportArr->getCodeIATA() == "CDG"){
                    if($aeroportDep->getCodeIATA() == "AJA"){
                        $ordre = 300;
                    }
                    if($aeroportDep->getCodeIATA() == "BIA"){
                        $ordre = 302;
                    }
                    if($aeroportDep->getCodeIATA() == "CLY"){
                        $ordre = 304;
                    }
                    if($aeroportDep->getCodeIATA() == "FSC"){
                        $ordre = 306;
                    }

                }
                if($aeroportDep->getCodeIATA() == "CDG"){
                    if($aeroportArr->getCodeIATA() == "AJA"){
                        $ordre = 301;
                    }
                    if($aeroportArr->getCodeIATA() == "BIA"){
                        $ordre = 303;
                    }
                    if($aeroportArr->getCodeIATA() == "CLY"){
                        $ordre = 305;
                    }
                    if($aeroportArr->getCodeIATA() == "FSC"){
                        $ordre = 307;
                    }

                }
                if($aeroportArr->getCodeIATA() == "LYS"){
                    if($aeroportDep->getCodeIATA() == "AJA"){
                        $ordre = 400;
                    }
                    if($aeroportDep->getCodeIATA() == "BIA"){
                        $ordre = 402;
                    }
                    if($aeroportDep->getCodeIATA() == "CLY"){
                        $ordre = 404;
                    }
                    if($aeroportDep->getCodeIATA() == "FSC"){
                        $ordre = 406;
                    }

                }
                if($aeroportDep->getCodeIATA() == "LYS"){
                    if($aeroportArr->getCodeIATA() == "AJA"){
                        $ordre = 401;
                    }
                    if($aeroportArr->getCodeIATA() == "BIA"){
                        $ordre = 403;
                    }
                    if($aeroportArr->getCodeIATA() == "CLY"){
                        $ordre = 405;
                    }
                    if($aeroportArr->getCodeIATA() == "FSC"){
                        $ordre = 407;
                    }

                }
                if($aeroportArr->getCodeIATA() == "TLS"){
                    if($aeroportDep->getCodeIATA() == "AJA"){
                        $ordre = 500;
                    }
                    if($aeroportDep->getCodeIATA() == "BIA"){
                        $ordre = 502;
                    }
                    if($aeroportDep->getCodeIATA() == "CLY"){
                        $ordre = 504;
                    }
                    if($aeroportDep->getCodeIATA() == "FSC"){
                        $ordre = 506;
                    }

                }
                if($aeroportDep->getCodeIATA() == "TLS"){
                    if($aeroportArr->getCodeIATA() == "AJA"){
                        $ordre = 501;
                    }
                    if($aeroportArr->getCodeIATA() == "BIA"){
                        $ordre = 503;
                    }
                    if($aeroportArr->getCodeIATA() == "CLY"){
                        $ordre = 505;
                    }
                    if($aeroportArr->getCodeIATA() == "FSC"){
                        $ordre = 507;
                    }

                }
                if($aeroportArr->getCodeIATA() == "NTE"){
                    if($aeroportDep->getCodeIATA() == "AJA"){
                        $ordre = 600;
                    }
                    if($aeroportDep->getCodeIATA() == "BIA"){
                        $ordre = 602;
                    }
                    if($aeroportDep->getCodeIATA() == "CLY"){
                        $ordre = 604;
                    }
                    if($aeroportDep->getCodeIATA() == "FSC"){
                        $ordre = 606;
                    }

                }
                if($aeroportDep->getCodeIATA() == "NTE"){
                    if($aeroportArr->getCodeIATA() == "AJA"){
                        $ordre = 601;
                    }
                    if($aeroportArr->getCodeIATA() == "BIA"){
                        $ordre = 603;
                    }
                    if($aeroportArr->getCodeIATA() == "CLY"){
                        $ordre = 605;
                    }
                    if($aeroportArr->getCodeIATA() == "FSC"){
                        $ordre = 607;
                    }

                }
                if($aeroportArr->getCodeIATA() == "BOD"){
                    if($aeroportDep->getCodeIATA() == "AJA"){
                        $ordre = 700;
                    }
                    if($aeroportDep->getCodeIATA() == "BIA"){
                        $ordre = 702;
                    }
                    if($aeroportDep->getCodeIATA() == "CLY"){
                        $ordre = 704;
                    }
                    if($aeroportDep->getCodeIATA() == "FSC"){
                        $ordre = 706;
                    }
                }
                if($aeroportDep->getCodeIATA() == "BOD"){
                    if($aeroportArr->getCodeIATA() == "AJA"){
                        $ordre = 701;
                    }
                    if($aeroportArr->getCodeIATA() == "BIA"){
                        $ordre = 703;
                    }
                    if($aeroportArr->getCodeIATA() == "CLY"){
                        $ordre = 705;
                    }
                    if($aeroportArr->getCodeIATA() == "FSC"){
                        $ordre = 707;
                    }
                }
                if($aeroportArr->getCodeIATA() == "TLN"){
                    if($aeroportDep->getCodeIATA() == "AJA"){
                        $ordre = 800;
                    }
                    if($aeroportDep->getCodeIATA() == "BIA"){
                        $ordre = 802;
                    }
                    if($aeroportDep->getCodeIATA() == "CLY"){
                        $ordre = 804;
                    }
                    if($aeroportDep->getCodeIATA() == "FSC"){
                        $ordre = 806;
                    }

                }
                if($aeroportDep->getCodeIATA() == "TLN"){
                    if($aeroportArr->getCodeIATA() == "AJA"){
                        $ordre = 801;
                    }
                    if($aeroportArr->getCodeIATA() == "BIA"){
                        $ordre = 803;
                    }
                    if($aeroportArr->getCodeIATA() == "CLY"){
                        $ordre = 805;
                    }
                    if($aeroportArr->getCodeIATA() == "FSC"){
                        $ordre = 807;
                    }

                }
                if($aeroportArr->getCodeIATA() == "DLE"){
                    if($aeroportDep->getCodeIATA() == "AJA"){
                        $ordre = 900;
                    }
                    if($aeroportDep->getCodeIATA() == "BIA"){
                        $ordre = 902;
                    }
                    if($aeroportDep->getCodeIATA() == "CLY"){
                        $ordre = 904;
                    }
                    if($aeroportDep->getCodeIATA() == "FSC"){
                        $ordre = 906;
                    }
                }
                if($aeroportDep->getCodeIATA() == "DLE"){
                    if($aeroportArr->getCodeIATA() == "AJA"){
                        $ordre = 901;
                    }
                    if($aeroportArr->getCodeIATA() == "BIA"){
                        $ordre = 903;
                    }
                    if($aeroportArr->getCodeIATA() == "CLY"){
                        $ordre = 905;
                    }
                    if($aeroportArr->getCodeIATA() == "FSC"){
                        $ordre = 907;
                    }
                }
                if($aeroportArr->getCodeIATA() == "CFE"){
                    if($aeroportDep->getCodeIATA() == "AJA"){
                        $ordre = 1000;
                    }
                    if($aeroportDep->getCodeIATA() == "BIA"){
                        $ordre = 1002;
                    }
                    if($aeroportDep->getCodeIATA() == "CLY"){
                        $ordre = 1004;
                    }
                    if($aeroportDep->getCodeIATA() == "FSC"){
                        $ordre = 1006;
                    }
                }
                if($aeroportDep->getCodeIATA() == "CFE"){
                    if($aeroportArr->getCodeIATA() == "AJA"){
                        $ordre = 1001;
                    }
                    if($aeroportArr->getCodeIATA() == "BIA"){
                        $ordre = 1003;
                    }
                    if($aeroportArr->getCodeIATA() == "CLY"){
                        $ordre = 1005;
                    }
                    if($aeroportArr->getCodeIATA() == "FSC"){
                        $ordre = 1007;
                    }
                }
                if($aeroportArr->getCodeIATA() == "CRL"){
                    if($aeroportDep->getCodeIATA() == "AJA"){
                        $ordre = 1100;
                    }
                    if($aeroportDep->getCodeIATA() == "BIA"){
                        $ordre = 1102;
                    }
                    if($aeroportDep->getCodeIATA() == "CLY"){
                        $ordre = 1104;
                    }
                    if($aeroportDep->getCodeIATA() == "FSC"){
                        $ordre = 1106;
                    }
                }
                if($aeroportDep->getCodeIATA() == "CRL"){
                    if($aeroportArr->getCodeIATA() == "AJA"){
                        $ordre = 1101;
                    }
                    if($aeroportArr->getCodeIATA() == "BIA"){
                        $ordre = 1103;
                    }
                    if($aeroportArr->getCodeIATA() == "CLY"){
                        $ordre = 1105;
                    }
                    if($aeroportArr->getCodeIATA() == "FSC"){
                        $ordre = 1107;
                    }
                }

                if(null == $ordre) {
                    $ordre = 0;
                }

                $ligne->setOrdre($ordre);

                $ligne->setModificateur($user);
                $ligne->setCreateur($user);

                $queryLigneNatureDeVol = "SELECT ligne, naturePeriodeVol
                                      FROM lignenatureperiodevol
                                      WHERE lignenatureperiodevol.ligne = ".$dbLigne['ligne']."";
                $resultLigneNatureDeVol=mysqli_query($mysqli,$queryLigneNatureDeVol);

                $aRowsLigneNatureDeVol = mysqli_fetch_all($resultLigneNatureDeVol,MYSQLI_ASSOC);

                $aIdParcouru = array();
                foreach ($aRowsLigneNatureDeVol as $dbNatureDeVol) {
                    if(!in_array($dbNatureDeVol['naturePeriodeVol'],$aIdParcouru)) {
                        $dbNatureDeVol = array_unique($dbNatureDeVol);
                        $natureDeVol = $manager->getRepository('AirCorsicaXKPlanBundle:NatureDeVol')->find($aCorrespondanceNature[$dbNatureDeVol['naturePeriodeVol']]);
                        $ligne->addNaturesDeVol($natureDeVol);
                        $aIdParcouru[] = $dbNatureDeVol['naturePeriodeVol'];
                    }
                }

                $manager->persist($ligne);
                $manager->flush();

                $aDepartArrivee[$dbLigne['aeroportDepart']."-".$dbLigne['aeroportArrivee']] = $ligne->getId();
                $output->writeln( "Nouvelle ligne ".$ligne->getId());
            }else{
                $output->writeln( "Ligne existante  ".$dbLigne['aeroportDepart']."-".$dbLigne['aeroportArrivee']);
            }
            $aCorrespondanceLigne[$dbLigne['ligne']] = $aDepartArrivee[$dbLigne['aeroportDepart']."-".$dbLigne['aeroportArrivee']];
            $output->writeln( "Nouvelle Correspondace ".$dbLigne['ligne']."-".$aDepartArrivee[$dbLigne['aeroportDepart']."-".$dbLigne['aeroportArrivee']]."-".$dbLigne['aeroportDepart']."-".$dbLigne['aeroportArrivee']);
        }

//        var_dump($aCorrespondanceLigne);

        gc_collect_cycles();

        $queryTypeDeVol = "SELECT typeperiodevol, nom, codeType, codeService, couleur_R, couleur_G, couleur_B
                  FROM typeperiodevol
                  ORDER BY typeperiodevol.typeperiodevol ASC";

        $resultTypeDeVol=mysqli_query($mysqli,$queryTypeDeVol);

        $aRowsTypeDeVol = mysqli_fetch_all($resultTypeDeVol,MYSQLI_ASSOC);

        /* Lecture des valeurs */
        $aCorrespondanceTypeDeVol = array();
        foreach ($aRowsTypeDeVol as $dbTypeDeVol) {
            $typeDeVol = new TypeDeVol();
            $typeDeVol->setNom($dbTypeDeVol['nom']);
            $typeDeVol->setCodeType($dbTypeDeVol['codeType']);
            $typeDeVol->setCodeService($dbTypeDeVol['codeService']);
            $typeDeVol->setCodeCouleur($this->rgb2hex(array($dbTypeDeVol['couleur_R'],$dbTypeDeVol['couleur_G'],$dbTypeDeVol['couleur_B'])));

            $typeDeVol->setModificateur($user);
            $typeDeVol->setCreateur($user);

            $manager->persist($typeDeVol);
            $manager->flush();
//            $manager->clear();

            $aCorrespondanceTypeDeVol[$dbTypeDeVol['typeperiodevol']] = $typeDeVol->getId();
        }

        gc_collect_cycles();

        $queryAffretement = "SELECT typeaffretement, nom, verrouille
                  FROM typeaffretement
                  ORDER BY typeaffretement.typeaffretement ASC";

        $resultAffretement=mysqli_query($mysqli,$queryAffretement);

        $aRowsAffretement = mysqli_fetch_all($resultAffretement,MYSQLI_ASSOC);

        /* Lecture des valeurs */
        $aCorrespondanceAffretement = array();
        foreach ($aRowsAffretement as $dbAffretement) {
            $affretement = new Affretement();
            $affretement->setNom($dbAffretement['nom']);
            $affretement->setVerrouille($dbAffretement['verrouille']);

            $affretement->setModificateur($user);
            $affretement->setCreateur($user);

            $manager->persist($affretement);
            $manager->flush();
//            $manager->clear();

            $aCorrespondanceAffretement[$dbAffretement['typeaffretement']] = $affretement->getId();
        }

        gc_collect_cycles();

        $queryCodesShare = "SELECT codesShares, codeXK, codePartenaire
                  FROM codesshares
                  ORDER BY codesshares.codesShares DESC";

        $resultCodesShare=mysqli_query($mysqli,$queryCodesShare);

        $aRowsCodesShare = mysqli_fetch_all($resultCodesShare,MYSQLI_ASSOC);

        /* Lecture des valeurs */
        $aCorrespondanceCodesSharePrecharge = array();
        $aCorrespondanceCodesInterne = array();
        $aCodeInterneParcouru = array();
        $repoCondeInterne = $manager->getRepository('AirCorsicaXKPlanBundle:CodeInterne');
        foreach ($aRowsCodesShare as $dbCodesShare) {
            $output->writeln("Codes Share ".$dbCodesShare['codesShares']);
            if(!in_array($dbCodesShare['codeXK'],$aCodeInterneParcouru)){
                $codeInterne = new CodeInterne();
                $codeInterne->setLibelle($dbCodesShare['codeXK']);

                $codeInterne->setModificateur($user);
                $codeInterne->setCreateur($user);

                $manager->persist($codeInterne);
                $manager->flush();
//                $manager->clear();

                $aCorrespondanceCodesInterne[$dbCodesShare['codeXK']] = $codeInterne->getId();
            }else{
                $codeInterne = $repoCondeInterne->find($aCorrespondanceCodesInterne[$dbCodesShare['codeXK']]);
            }

            $codeSharePrecharge = new CodeSharePrecharge();
            $codeSharePrecharge->setCodeInterne($codeInterne);
            $codeSharePrecharge->setLibelle($dbCodesShare['codePartenaire']);

            $codeSharePrecharge->setModificateur($user);
            $codeSharePrecharge->setCreateur($user);

            $manager->persist($codeSharePrecharge);
            $manager->flush();
//            $manager->clear();

            $aCorrespondanceCodesSharePrecharge[$dbCodesShare['codesShares']] = $codeSharePrecharge->getId();
        }

        gc_collect_cycles();

        $queryTemplate = "SELECT template, nom, production
                  FROM template
                  WHERE template = 1 or template = 53 or template = 50 or template = 55 or template = 56 or template = 57 or template = 59
                  ORDER BY template.template ASC";

        $resultTemplate=mysqli_query($mysqli,$queryTemplate);

        $aRowsTemplate = mysqli_fetch_all($resultTemplate,MYSQLI_ASSOC);

        /* Lecture des valeurs */
        $aCorrespondanceTemplate = array();
        foreach ($aRowsTemplate as $dbTemplate) {
            $output->writeln( "Template ".$dbTemplate['template']);
            $template = new Template();
            $template->setLibelle($dbTemplate['nom']);
            $template->setProduction($dbTemplate['production']);

            $template->setModificateur($user);
            $template->setCreateur($user);

            $manager->persist($template);
            $manager->flush();
//            $manager->clear();

            $aCorrespondanceTemplate[$dbTemplate['template']] = $template->getId();
        }

        gc_collect_cycles();

        $queryGroupeSITA = "SELECT groupedestinatairessita, libelle, aeroportDAttacheParDefaut, generique
                  FROM groupedestinatairessita
                  ORDER BY groupedestinatairessita.groupedestinatairessita ASC";

        $resultGroupeSITA=mysqli_query($mysqli,$queryGroupeSITA);

        $aRowsGroupeSITA = mysqli_fetch_all($resultGroupeSITA,MYSQLI_ASSOC);

        /* Lecture des valeurs */
        $aCorrespondanceGroupeSITA = array();
        $repoPays = $manager->getRepository('AirCorsicaXKPlanBundle:Pays');
        foreach ($aRowsGroupeSITA as $dbGroupeSITA) {
            $output->writeln( "Groupe SITA ".$dbGroupeSITA['groupedestinatairessita']);
            $groupeSITA = new GroupeSITA();
            $groupeSITA->setNom($dbGroupeSITA['libelle']);
            $groupeSITA->setGroupeGenerique($dbGroupeSITA['generique']);

            if($dbGroupeSITA['aeroportDAttacheParDefaut'] != 0) {
                $aeroportAttache = $repoAeroport->find($aCorrespondanceAeroport[$dbGroupeSITA['aeroportDAttacheParDefaut']]);
                $groupeSITA->setAeroportAttache($aeroportAttache);
            }

            $groupeSITA->setModificateur($user);
            $groupeSITA->setCreateur($user);

            $manager->persist($groupeSITA);
            $manager->flush();
//            $manager->clear();

            $aCorrespondanceGroupeSITA[$dbGroupeSITA['groupedestinatairessita']] = $groupeSITA->getId();

            $queryDestinataireSITA = "SELECT destinatairesSita, libelle, groupedestinatairessita, codeSITA, aeroportAttache, compagnieAttachee, receptacleMessagesSlots, paysCoordinateur, verouille, corbeille
                  FROM destinatairessita
                  WHERE destinatairessita.groupedestinatairessita = ".$dbGroupeSITA['groupedestinatairessita']."";

            $resultDestinataireSITA=mysqli_query($mysqli,$queryDestinataireSITA);

            $aRowsDestinataireSITA = mysqli_fetch_all($resultDestinataireSITA,MYSQLI_ASSOC);

            /* Lecture des valeurs */
            $aCorrespondanceDestinataireSITA = array();
            foreach ($aRowsDestinataireSITA as $dbDestinataireSITA) {
                $output->writeln( "Destinataire SITA ".$dbGroupeSITA['groupedestinatairessita']);
                $destinataireSITA = new AdresseSITA();
                $destinataireSITA->setLibelle($dbDestinataireSITA['libelle']);
                $destinataireSITA->setAdresseSITA($dbDestinataireSITA['codeSITA']);
                $destinataireSITA->setSuiviDemandeSlot($dbDestinataireSITA['receptacleMessagesSlots']);
                $destinataireSITA->setGroupeSITA($groupeSITA);

                if($dbDestinataireSITA['aeroportAttache'] != 0) {
                    $aeroportAttache = $repoAeroport->find($aCorrespondanceAeroport[$dbDestinataireSITA['aeroportAttache']]);
                    $destinataireSITA->setAeroportAttache($aeroportAttache);
                }
                if($dbDestinataireSITA['compagnieAttachee'] != 0) {
                    $compagnie = $repoCompagnie->find($aCorrespondanceCompagnie[$dbDestinataireSITA['compagnieAttachee']]);
                    $destinataireSITA->setCompagnieAttachee($compagnie);
                }
                if($dbDestinataireSITA['paysCoordinateur'] != 0) {
                    $pays = $repoPays->find($aCorrespondancePays[$dbDestinataireSITA['paysCoordinateur']]);
                    $destinataireSITA->setPaysCoordinateur($pays);
                }

                $destinataireSITA->setModificateur($user);
                $destinataireSITA->setCreateur($user);

                $destinataireSITA->setEmail("");

                $manager->persist($destinataireSITA);
                $manager->flush();
//                $manager->clear();
            }

            gc_collect_cycles();
        }

        gc_collect_cycles();

        $querySousPeriode = "SELECT sousPeriode, nom, creerSousPeriodeEstivale, creerSousPeriodeHivernale, notes
                  FROM sousperiode
                  ORDER BY sousperiode.sousPeriode ASC";

        $resultSousPeriode=mysqli_query($mysqli,$querySousPeriode);

        $aRowsSousPeriode = mysqli_fetch_all($resultSousPeriode,MYSQLI_ASSOC);

        /* Lecture des valeurs */
        $aCorrespondanceSousPeriode = array();
        foreach ($aRowsSousPeriode as $dbSousPeriode) {
            $modeleSousPeriode = new ModeleSousPeriode();
            $modeleSousPeriode->setNom($dbSousPeriode['nom']);
            $modeleSousPeriode->setPourPeriodeEstivalle($dbSousPeriode['creerSousPeriodeEstivale']);
            $modeleSousPeriode->setPourPeriodeHivernalle($dbSousPeriode['creerSousPeriodeHivernale']);

            $modeleSousPeriode->setModificateur($user);
            $modeleSousPeriode->setCreateur($user);

            $manager->persist($modeleSousPeriode);
            $manager->flush();
//            $manager->clear();

            $aCorrespondanceSousPeriode[$dbSousPeriode['sousPeriode']] = $modeleSousPeriode->getId();
        }

        gc_collect_cycles();

        $querySaison = "SELECT  saison, nom, descriptif, eteIATA, hiverIATA
                  FROM saison
                  ORDER BY saison.saison ASC";

        $resultSaison=mysqli_query($mysqli,$querySaison);

        $aRowsSaison = mysqli_fetch_all($resultSaison,MYSQLI_ASSOC);

        /* Lecture des valeurs */
        $aCorrespondanceSaison = array();
        $aCorrespondanceNomSaison = array();
        foreach ($aRowsSaison as $dbSaison) {
            $output->writeln("Saison ".$dbSaison['saison']);
            $saison = new Saison();
            $saison->setNom($dbSaison['nom']);
            $saison->setDescriptif($dbSaison['descriptif']);
            $saison->setVisibleMenuPopup(1);

            $saison->setModificateur($user);
            $saison->setCreateur($user);

            $queryPeriode = "SELECT  periode, saison, nom, descriptif, dateDebut, dateFin, ordre, periodeIATAComplete
                  FROM periode
                  WHERE saison= ".$dbSaison['saison']." AND periodeIATAComplete=1
                  ORDER BY periode.periode ASC";

            $resultPeriode=mysqli_query($mysqli,$queryPeriode);

            $aRowsPeriode = mysqli_fetch_all($resultPeriode,MYSQLI_ASSOC);

            $dbPeriode = $aRowsPeriode[0];

            $periode = new Periode($dbPeriode['dateDebut'],$dbPeriode['dateFin']);
            $periode->setModificateur($user);
            $periode->setCreateur($user);

            $saison->setPeriode($periode);

            $manager->persist($saison);
            $manager->flush();
//            $manager->clear();

            $aCorrespondanceSaison[$dbSaison['saison']] = $saison->getId();
        }

        gc_collect_cycles();

        $queryPeriode = "SELECT  periode, saison, nom, descriptif, dateDebut, dateFin, ordre, periodeIATAComplete
                  FROM periode
                  ORDER BY periode.periode ASC";

        $resultPeriode=mysqli_query($mysqli,$queryPeriode);

        $aRowsPeriode = mysqli_fetch_all($resultPeriode,MYSQLI_ASSOC);

        /* Lecture des valeurs */
        $aCorrespondancePeriode = array();

        $repoSaison = $manager->getRepository('AirCorsicaXKPlanBundle:Saison');
        foreach ($aRowsPeriode as $dbPeriode) {
            $output->writeln("Periode ".$dbPeriode['periode']);
            $periodeSaison = new PeriodeSaison($dbPeriode['dateDebut'],$dbPeriode['dateFin']);
            $periodeSaison->setNom($dbPeriode['nom']);

            $saison = $repoSaison->find($aCorrespondanceSaison[$dbPeriode['saison']]);
            $periodeSaison->setSaison($saison);

            $periodeSaison->setModificateur($user);
            $periodeSaison->setCreateur($user);
            $periodeSaison->setIsIATA($dbPeriode['periodeIATAComplete']);
            $periodeSaison->setIsVisible(1);

            $manager->persist($periodeSaison);
            $manager->flush();
//            $manager->clear();

            $aCorrespondancePeriode[$dbPeriode['periode']] = $periodeSaison->getId();
        }

        gc_collect_cycles();

        $queryPeriodeImmoAvion = "SELECT  avionImmo, dateImmoDebut, dateImmoFin, avion
                  FROM avionimmo
                  ORDER BY avionimmo.avionImmo ASC";

        $resultPeriodeImmoAvion=mysqli_query($mysqli,$queryPeriodeImmoAvion);

        $aRowsPeriodeImmoAvion = mysqli_fetch_all($resultPeriodeImmoAvion,MYSQLI_ASSOC);

        /* Lecture des valeurs */
        $aCorrespondancePeriodeImmoAvion = array();
        $repoAvion = $manager->getRepository('AirCorsicaXKPlanBundle:Avion');
        foreach ($aRowsPeriodeImmoAvion as $dbPeriodeImmoAvion) {
            $periodeImmoAvion = new PeriodeImmobilisation($dbPeriodeImmoAvion['dateImmoDebut'],$dbPeriodeImmoAvion['dateImmoFin']);

            $avion =$repoAvion->find($aCorrespondanceAvion[$dbPeriodeImmoAvion['avion']]);
            $periodeImmoAvion->setAvion($avion);

            $periodeImmoAvion->setModificateur($user);
            $periodeImmoAvion->setCreateur($user);

            $manager->persist($periodeImmoAvion);
            $manager->flush();
//            $manager->clear();

            $aCorrespondancePeriodeImmoAvion[$dbPeriodeImmoAvion['avionImmo']] = $periodeImmoAvion->getId();
        }

        gc_collect_cycles();

        $queryTempsDeVol = "SELECT  typeAvionTempsVolSaison, typeAvion, ligne, periode.periode, tempsDeVol
                  FROM typeaviontempsvolsaison,periode
                  WHERE typeaviontempsvolsaison.periode = periode.periode
                  AND tempsDeVol > 0
                  AND (dateDebut >= '2014-12-01' OR dateFin >= '2015-01-01')
                  ORDER BY typeaviontempsvolsaison.typeAvionTempsVolSaison ASC";

        $resultTempsDeVol=mysqli_query($mysqli,$queryTempsDeVol);

        $aRowsTempsDeVol = mysqli_fetch_all($resultTempsDeVol,MYSQLI_ASSOC);

        /* Lecture des valeurs */
        $aCorrespondanceTempsDeVol = array();
        $repoPeriodeSaison = $manager->getRepository('AirCorsicaXKPlanBundle:PeriodeSaison');
        $repoLigne = $manager->getRepository('AirCorsicaXKPlanBundle:Ligne');
        foreach ($aRowsTempsDeVol as $dbTempsDeVol) {
            $output->writeln( "Temps de Vol ".$dbTempsDeVol['typeAvionTempsVolSaison']);
            //TypeAvion mis à la corbeille dans XKPlan Xavier
            if(!isset($aCorrespondanceTypeAvion[$dbTempsDeVol['typeAvion']]))
                continue;
            if(!isset($aCorrespondanceLigne[$dbTempsDeVol['ligne']]))
                continue;
            if($dbTempsDeVol['ligne'] == 0)
                continue;

            $tempsDeVol = new TempsDeVol();
            $tempsDeVol->setDuree($dbTempsDeVol['tempsDeVol']);

            $saison = $repoPeriodeSaison->find($aCorrespondancePeriode[$dbTempsDeVol['periode']]);
            $typeAvion = $repoTypeAvion->find($aCorrespondanceTypeAvion[$dbTempsDeVol['typeAvion']]);
            $ligne = $repoLigne->find($aCorrespondanceLigne[$dbTempsDeVol['ligne']]);

            $tempsDeVol->setSaison($saison);
            $tempsDeVol->setTypeAvion($typeAvion);
            $tempsDeVol->setLigne($ligne);

            $tempsDeVol->setModificateur($user);
            $tempsDeVol->setCreateur($user);

            $manager->persist($tempsDeVol);

//            $manager->clear();

            $aCorrespondanceTempsDeVol[$dbTempsDeVol['typeAvionTempsVolSaison']] = $tempsDeVol->getId();
        }
        $manager->flush();
        gc_collect_cycles();

//        $queryPeriodeDeVol = "SELECT  arbreParental, template, valide
//                  FROM periodevol
//                  WHERE (periodevol.dateDebut != '0001-01-01 00:00:00' OR periodevol.dateFin != '0001-01-01 00:00:00')
//                  AND periodevol.dateDebut > '2015-01-01 00:00:00' AND template = 1
//                  ORDER BY periodevol.periodevol DESC";
//
//        $resultPeriodeDeVol=mysqli_query($mysqli,$queryPeriodeDeVol);
//
//        $aRowsPeriodeDeVol = mysqli_fetch_all($resultPeriodeDeVol,MYSQLI_ASSOC);
//
//        /* Lecture des valeurs */
//        $aCorrespondancePeriodeDeVol = array();
//        foreach ($aRowsPeriodeDeVol as $dbPeriodeDeVol) {
//            $aArbreParental = explode(" ",$dbPeriodeDeVol['arbreParental']);
//            $idPeriodeDeVol = array_pop($aArbreParental);

            $test = 0;
            $batchSize = 100;
            $repoAffretement = $manager->getRepository('AirCorsicaXKPlanBundle:Affretement');
            $repoTemplate = $manager->getRepository('AirCorsicaXKPlanBundle:Template');
            $repoTypeDeVol = $manager->getRepository('AirCorsicaXKPlanBundle:TypeDeVol');
//            $manager->clear();

            //AND (dateDebut >= '2014-12-01' OR dateFin >= '2015-01-01') AND template = 1 AND (valide=1 or (valide=0 AND visible = 0 and messageEnvoye =1))
            $queryPeriodeDeVol2 = "SELECT  *
                  FROM periodevol
                  WHERE (periodevol.dateDebut != '0001-01-01 00:00:00' OR periodevol.dateFin != '0001-01-01 00:00:00')
                  AND (dateDebut >= '2014-12-01' OR dateFin >= '2015-01-01') AND (template = 1 OR template = 53 OR template = 50 OR template = 55 OR template = 56 OR template = 57 OR template = 59) AND (valide=1 or (valide=0 AND visible = 0 and messageEnvoye =1))
                  ORDER BY periodevol.numeroVol DESC" ;

            $resultPeriodeDeVol2=mysqli_query($mysqli,$queryPeriodeDeVol2);
            $aRowsPeriodeDeVol2 = mysqli_fetch_all($resultPeriodeDeVol2,MYSQLI_ASSOC);
            foreach ($aRowsPeriodeDeVol2 as $dbPeriodeDeVol2) {
                $output->writeln( "Période de Vol ".$dbPeriodeDeVol2['periodeVol']);
                if($dbPeriodeDeVol2['joursValidite'] == "_______") {
                    $output->writeln( "Jour de Validité null");
                    continue;
                }
                $idPeriodeDeVol = $dbPeriodeDeVol2['periodeVol'];
                $deleste = 0;
                if((!$dbPeriodeDeVol2['visible'] && !$dbPeriodeDeVol2['valide']) || ($dbPeriodeDeVol2['messageEnvoye'] && $dbPeriodeDeVol2['valide'] && !$dbPeriodeDeVol2['visible'])
                    || ($dbPeriodeDeVol2['valide'] && !$dbPeriodeDeVol2['visible'] && !$dbPeriodeDeVol2['messageEnvoye']) || (!$dbPeriodeDeVol2['valide'] && !$dbPeriodeDeVol2['visible'] && $dbPeriodeDeVol2['messageEnvoye']))
                    $deleste = 1;

                $vol = new Vol();

                $vol->setModificateur($user);
                $vol->setCreateur($user);

                if(!isset($aCorrespondanceLigne[$dbPeriodeDeVol2['ligne']])){
                    $output->writeln( "Pas de ligne");
                    continue;
                }
                $output->writeln( "Ligne du Vol ".$dbPeriodeDeVol2['ligne']);
                $output->writeln( "Correspondance Ligne du Vol ".$aCorrespondanceLigne[$dbPeriodeDeVol2['ligne']]);
                $ligne = $repoLigne->find($aCorrespondanceLigne[$dbPeriodeDeVol2['ligne']]);
                $vol->setLigne($ligne);
                if(!isset($aCorrespondanceAvion[$dbPeriodeDeVol2['avion']])){
                    $output->writeln( "Pas d'avion ".$dbPeriodeDeVol2['avion']);
                    continue;
                }
                $output->writeln( "Avion du Vol ".$dbPeriodeDeVol2['avion']);
                $avion = $repoAvion->find($aCorrespondanceAvion[$dbPeriodeDeVol2['avion']]);
                $vol->setAvion($avion);
                if(!isset($aCorrespondanceAffretement[$dbPeriodeDeVol2['typeAffretement']])){
                    $output->writeln( "Pas de typeAffretement");
                    continue;
                }
                $output->writeln( "Affretement du Vol ".$dbPeriodeDeVol2['typeAffretement']);
                $affretement = $repoAffretement->find($aCorrespondanceAffretement[$dbPeriodeDeVol2['typeAffretement']]);
                $vol->setAffretement($affretement);
                /*if(!isset($aCorrespondanceTemplate[$dbPeriodeDeVol2['template']])){
                    $output->writeln( "Pas de template");
                    continue;
                }*/
                $output->writeln( "Template du Vol ".$dbPeriodeDeVol2['template']);
                $template = $repoTemplate->find($aCorrespondanceTemplate[$dbPeriodeDeVol2['template']]);
                $vol->setTemplate($template);
                if(!isset($aCorrespondanceTypeDeVol[$dbPeriodeDeVol2['typePeriodeVol']])){
                    $output->writeln( "Pas de typePeriodeVol");
                    continue;
                }
                $output->writeln( "Type du Vol ".$dbPeriodeDeVol2['typePeriodeVol']);
                $typeDeVol = $repoTypeDeVol->find($aCorrespondanceTypeDeVol[$dbPeriodeDeVol2['typePeriodeVol']]);
                $vol->setTypeDeVol($typeDeVol);
                if(!isset($aCorrespondanceCompagnie[$dbPeriodeDeVol2['compagnie']])){
                    $output->writeln( "Pas de compagnie");
                    continue;
                }
                $output->writeln( "Compagnie du Vol ".$dbPeriodeDeVol2['compagnie']);
                $compagnie = $repoCompagnie->find($aCorrespondanceCompagnie[$dbPeriodeDeVol2['compagnie']]);
                $vol->setCompagnie($compagnie);

                $queryCodeSharePeriodeDeVol = "SELECT  periodevolcodeshare, periodevol,codeshare
                  FROM periodevolcodeshare
                  WHERE periodevolcodeshare.periodevol = $idPeriodeDeVol";

                $resultCodeSharePeriodeDeVol = mysqli_query($mysqli, $queryCodeSharePeriodeDeVol);

                $aRowsCodeSharePeriodeDeVol = mysqli_fetch_all($resultCodeSharePeriodeDeVol, MYSQLI_ASSOC);

                /* Lecture des valeurs */
                $aCorrespondanceCodeSharePeriodeDeVol = array();
                foreach ($aRowsCodeSharePeriodeDeVol as $dbCodeSharePeriodeDeVol) {
                    $oCodeshareVol = new CodeShareVol();
                    $output->writeln( "Code Share du Vol ".$dbCodeSharePeriodeDeVol['codeshare']);
                    $oCodeshareVol->setLibelle($dbCodeSharePeriodeDeVol['codeshare']);
                    $oCodeshareVol->setModificateur($user);
                    $oCodeshareVol->setCreateur($user);
                    $vol->addCodesShareVol($oCodeshareVol);
                }

                gc_collect_cycles();

                $queryNaturePeriodeDeVol = "SELECT  PeriodeVolNaturePeriodeVol, PeriodeVol, naturePeriodeVol
                  FROM periodevolnatureperiodevol
                  WHERE periodevolnatureperiodevol.PeriodeVol = $idPeriodeDeVol";

                $resultNaturePeriodeDeVol = mysqli_query($mysqli, $queryNaturePeriodeDeVol);

                $aRowsNaturePeriodeDeVol = mysqli_fetch_all($resultNaturePeriodeDeVol, MYSQLI_ASSOC);

                /* Lecture des valeurs */
                $aCorrespondanceCodeSharePeriodeDeVol = array();
                foreach ($aRowsNaturePeriodeDeVol as $dbNaturePeriodeDeVol) {
                    if(!isset($aCorrespondanceNature[$dbNaturePeriodeDeVol['naturePeriodeVol']])){
                        $output->writeln( "Pas de naturePeriodeVol");
                        continue;
                    }
                    $output->writeln( "Nature du Vol ".$dbNaturePeriodeDeVol['naturePeriodeVol']);
                    $natureDeVol = $repoNatureDeVol->find($aCorrespondanceNature[$dbNaturePeriodeDeVol['naturePeriodeVol']]);
                    $vol->addNaturesDeVol($natureDeVol);
                }

                gc_collect_cycles();

                if (!$deleste && $dbPeriodeDeVol2['valide'] && $dbPeriodeDeVol2['messageEnvoye']) {
                    $etat = "send";
                }elseif($deleste && $dbPeriodeDeVol2['messageEnvoye'] && !$dbPeriodeDeVol2['valide']) {
                    $etat = "cancel";
                }elseif($deleste && $dbPeriodeDeVol2['messageEnvoye'] && $dbPeriodeDeVol2['valide']){
                    $etat = "pendingCancel";
                }else{
                    $etat = "pendingSend";
                }

                if("00:00:01" == $dbPeriodeDeVol2['heureArrivee']){
                    $heureArrivee = "00:00:00";
                }else{
                    $heureArrivee = $dbPeriodeDeVol2['heureArrivee'];
                }
                $periodeDeVol = New PeriodeDeVol($dbPeriodeDeVol2['dateDebut'], $dbPeriodeDeVol2['dateFin'], $dbPeriodeDeVol2['heureDepart'], $heureArrivee,
                    str_split(str_replace('_','-',$dbPeriodeDeVol2['joursValidite'])),$etat);
                $output->writeln( "Periode du Vol ".$dbPeriodeDeVol2['dateDebut']." ".$dbPeriodeDeVol2['dateFin']);
                $periodeDeVol->setDeleste($deleste);
                $periodeDeVol->setModificateur($user);
                $periodeDeVol->setCreateur($user);
                $vol->setPeriodeDeVol($periodeDeVol);

                $vol->setCommentaire($dbPeriodeDeVol2['commentaires']);
                $vol->setNumero($dbPeriodeDeVol2['numeroVol']);

                //Historique
                $volHistoriqueNew = VolHistorique::createFromVol($vol);
                $volHistoriqueNew->setModificateur($user);
                $volHistoriqueNew->setCreateur($user);
                Vol::saveVolInHistorique($volHistoriqueNew,$manager);
                $vol->setVolHistoriqueParentable($volHistoriqueNew);
                $volHistoriqueNew->setVolHistorique($vol);

                $manager->persist($vol);
//                if($test % $batchSize === 0){
//                    $manager->flush();
//                    $manager->clear();
//                }
                $test++;

                $timestamp_fin = microtime(true);
                $difference_ms = $timestamp_fin - $timestamp_debut;
                echo 'Exécution du script : ' . $difference_ms . ' secondes.';
            }
        $manager->flush();


        gc_collect_cycles();

        /* Fermeture de la connexion */
        $mysqli->close();
        $timestamp_fin = microtime(true);
        $difference_ms = $timestamp_fin - $timestamp_debut;
        echo 'Migration terminée en '.$difference_ms. ' secondes.';
    }

    private function getUser($manager){
        $aRes= $manager
            ->getRepository('AirCorsicaXKPlanBundle:Utilisateur')
            ->findAll();
        return $aRes[0];
    }

    public function getOrder()
    {
        return 2;
    }

    private function rgb2hex($rgb) {
        $hex = "#";
        $hex .= str_pad(dechex($rgb[0]), 2, "0", STR_PAD_LEFT);
        $hex .= str_pad(dechex($rgb[1]), 2, "0", STR_PAD_LEFT);
        $hex .= str_pad(dechex($rgb[2]), 2, "0", STR_PAD_LEFT);

        return $hex; // returns the hex value including the number sign (#)
    }
}