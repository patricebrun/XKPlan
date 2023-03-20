<?php
/**
 * Created by PhpStorm.
 * User: damie
 * Date: 22/02/2017
 * Time: 10:21
 */

namespace AirCorsica\XKPlanBundle\DataFixtures;


use AirCorsica\XKPlanBundle\Entity\Utilisateur;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadUtilisateurData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $mysqli=mysqli_connect("10.20.0.32","xkplan",'xkplan$pwd2016',"xkplanv1");
        $query = "SELECT ste,nom,prenom,login,password,droits,verrouille,systeme,notes FROM utilisateur ORDER BY utilisateur ASC";

        if ($stmt = $mysqli->prepare($query)) {

            /* Exécution de la requête */
            $stmt->execute();

            /* Association des variables de résultat */
            $stmt->bind_result($societe, $nom, $prenom, $login, $password, $droits, $verrouille, $systeme, $notes);

            /* Lecture des valeurs */
            while ($stmt->fetch()) {

                if($verrouille)
                    continue;

                $user = new Utilisateur();

                $user->setNom($nom);
                $user->setPrenom($prenom);
                $user->setUsername($login);
                $user->setEmail($login.'@aircorsica.fr');
                $user->setSociete($societe);
                $user->setPlainPassword($password);
                $user->setEnabled(1);
                $droit = array();
                switch ($droits){
                    case 'RW':
                        $droit[] = 'ROLE_ADMIN';
                    break;
                    case 'RO':
                        $droit[] = 'ROLE_AUTEUR';
                    break;
                }
                $user->setRoles($droit);
                $user->setNote($notes);

                $manager->persist($user);
                $manager->flush();
            }

            /* Fermeture de la commande */
            $stmt->close();
        }

        /* Fermeture de la connexion */
        $mysqli->close();
    }

    public function getOrder()
    {
        return 1;
    }
}