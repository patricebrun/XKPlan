<?php
// myapplication/src/sandboxBundle/Command/TestCommand.php
// Change the namespace according to your bundle
namespace AirCorsica\XKPlanBundle\Command;

use AirCorsica\XKPlanBundle\Entity\Message\SSIM;
use AirCorsica\XKPlanBundle\Repository\VolRepository;
use DateTime;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Yaml\Yaml;

class GenerateSSIMCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            // the name of the command (the part after "bin/console")
            ->setName('app:generate-ssim')
            // the short description shown while running "php bin/console list"
            ->setDescription('Génération du fichier SSIM')
            // the full command description shown when running the command with
            // the "--help" option
            ->addArgument('dateDebut', InputArgument::REQUIRED,"Date de début du SSIM")
            ->addArgument('dateFin', InputArgument::REQUIRED,"Date de fin du SSIM")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //Uniquement pour ce script
        ini_set('max_execution_time', 0);

//        $pathParam = $_SERVER['DOCUMENT_ROOT'].'/../src/AirCorsica/XKPlanBundle/Resources/config/parametres_globaux.yml';
//        $parametres_globaux = Yaml::parse(file_get_contents($pathParam));
//
//        $messagereplyaddress = "";
//        $emailsitaaltea = "";
//
//        foreach($parametres_globaux as $i => $parametre)
//        {
//            $messagereplyaddress = $parametre['messagereplyaddress'];
//            $emailsitaaltea = $parametre['emailsitassim7'];
//        }

        $doctrine = $this->getContainer()->get('doctrine');
        /** @var EntityManager $em */
        $em = $doctrine->getEntityManager();
//        $em = $this->getDoctrine()->getManager();
        /** @var VolRepository $repoVol */
        $repoVol = $em->getRepository('AirCorsicaXKPlanBundle:Vol');
        $orderParPeriode = "ASC";

        $repoTemplate = $em->getRepository('AirCorsicaXKPlanBundle:Template');
        /** @var Template $templateCurrent */
        $templateCurrent = $repoTemplate->find(1);

        $request = new Request();
        $request->query->set('date_debut',$input->getArgument('dateDebut'));
        $request->query->set('date_fin',$input->getArgument('dateFin'));

        $vols = $repoVol->getVolsFilter($request,$templateCurrent,$orderParPeriode);

        $debutTroncate = DateTime::createFromFormat('d-m-Y',$request->get('date_debut'));
        $finTroncate = DateTime::createFromFormat('d-m-Y',$request->get('date_fin'));

        $aVolsTroncate = array();
        $modif = 0;
        /** @var Vol $vol */
        foreach($vols as $vol){
            $periodeDeVol = $vol->getPeriodeDeVol();
            if($periodeDeVol->getDateDebut()<$debutTroncate){
                $periodeDeVol->setDateDebut($debutTroncate);
//                $modif = 1;
            }
            if($periodeDeVol->getDateFin()>$finTroncate){
                $periodeDeVol->setDateFin($finTroncate);
//                $modif = 1;
            }
//            if($modif){
            $periodeDeVol->corrigerJourDeValidite(true);
                $periodeDeVol = $periodeDeVol->corrigerDate(true);
//                $periodeDeVol->corrigerJourDeValidite();
//            }

//            $modif = 0;
            $vol->setPeriodeDeVol($periodeDeVol);
            if($vol->getPeriodeDeVol()->isJoursDeValiditeDansPeriode()){
//                $vol->setPeriodeDeVol($periodeDeVol);
                $aVolsTroncate[] = $vol;
            }
        }

        $SSIM = new SSIM($aVolsTroncate);

        $return = $SSIM->genererMessage($em,$request->get('date_debut'),$request->get('date_fin'));

        $now = new \DateTime();
        $filename = 'ssim_partial_'.$now->format("dmY_His").'.txt';
//        if($this->getContainer()->get( 'kernel' )->getEnvironment() == "prod") {
        $directory = '/data/http/xkplan/current/web/SSIM/partial/';
//        }else{
//            $directory = '/data/http/damien/xkplan/web/SSIM/partial/';
//        }
        $fp = fopen ($directory.$filename, 'w+');
        fputs ($fp, $return);
        fclose($fp);

//        $aSsim['textBrut'] = $return;
//        $aSsim['txt'] = $filename;
//        $aAttachement['txt'] = 'web/SSIM/partial/'.$aSsim['txt'];

//        if($this->getContainer()->get( 'kernel' )->getEnvironment() == "prod"){
//            $this->sendEmail("SSIM7 du ".$now->format("d/m/Y h:i:s"),$messagereplyaddress,$emailsitaaltea,"AirCorsicaXKPlanBundle:Emails:ssim.html.twig",$aSsim,$aAttachement);
//        }else{
//            $this->sendEmail("SSIM7 du ".$now->format("d/m/Y h:i:s"),"xkplan@aircorsica.fr","damien.cayzac@sitec.fr","AirCorsicaXKPlanBundle:Emails:ssim.html.twig",$aSsim,$aAttachement);
//        }

        return 1;
    }

//    private function sendEmail($objet,$from,$to,$view,$paramsToView,$aAttachement = array()){
//        $message = \Swift_Message::newInstance()
//            ->setSubject($objet)
//            ->setFrom($from)
//            ->setTo($to)
//            ->setBody(
//                $this->getContainer()->get('templating')->render(
//                // app/Resources/views/Emails/registration.html.twig
//                    $view,
//                    array('aParam' => $paramsToView)
//                ),
//                'text/plain'
//            )
//            /*
//             * If you also want to include a plaintext version of the message
//            ->addPart(
//                $this->renderView(
//                    'Emails/registration.txt.twig',
//                    array('name' => $name)
//                ),
//                'text/plain'
//            )
//            */
//        ;
//        if(sizeof($aAttachement)){
//            foreach ($aAttachement as $extension=>$filename){
//                $message->attach(\Swift_Attachment::fromPath($filename));
//            }
//        }
//
//        $this->getContainer()->get('mailer')->send($message);
//    }
}
