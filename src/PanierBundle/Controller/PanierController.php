<?php

namespace PanierBundle\Controller;
use PanierBundle\Entity\Commande;
use PanierBundle\Entity\Historique;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use PanierBundle\Form\CommandeType;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\PieChart;
use CMEN\GoogleChartsBundle\GoogleCharts\Charts\BarChart;
use Symfony\Component\Validator\Constraints\Date;


class PanierController extends Controller
{

    public function AjouterCommandeAction(Request $request)
    {

        $userID = $this->getUser();
        $username=$this->getUser()->getUsername();
        $userManager = $this->get('fos_user.user_manager');
        $users=$userManager->findUsers();
        $userIDD=$this->getUser()->getId();
        $historique=new Historique();
        $id=$request->get('id');
        $em = $this->getDoctrine()->getManager();
        $produit = $em->getRepository('ProduitBundle:Produit')->find($id);
        $commande=new Commande();
        $form=$this->createForm(CommandeType::class,$commande);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $quantite=$commande->getQuantiteC();
            $total=$quantite*$produit->getPrix();

            $historique->setAction($username." ordered the following product : ".$produit->getNom()." with order reference : ".$commande->getReference());
            $commande->setTotalC($total);
            $commande->setIdP($produit);
            $commande->setDateC(new \DateTime('now'));
            $commande->setEtatC(0);
            $commande->setIdUser($userID);
            $historique->setDateAction(new \DateTime('now'));
            $em=$this->getDoctrine()->getManager();
            $manager = $this->get('mgilet.notification');
            $historique->setIdUtilisateur($userID);
            $notif = $manager->createNotification("Une nouvelle commande a été effectué par :".$username);
            $notif->setMessage('');
            $manager->addNotification(array($users[0]), $notif, true);
            $em->persist($produit);
            $em->persist($commande);
            $em->flush();
            $historique->setIdCommande($commande->getId());
            $em->persist($historique);
            $em->flush();


        }
        return $this->render('@Panier/Panier/ajouterCommande.html.twig',array('produit'=>$produit,'form'=>$form->createView()));

    }




    public function listCommandesAction()
    {
        $doctrine=$this->getDoctrine();
        $repository=$doctrine->getRepository('PanierBundle:Commande');
        $commandes=$repository->findAll();
        return $this->render('@Panier/Panier/AfficherCommandes.html.twig',array('commandes'=>$commandes));

    }

    public function listCommandesFrontAction()
    {
        $em=$this->getDoctrine()->getManager();
        $userID = $this->getUser()->getID();
        $commandes=$em->getRepository("PanierBundle:Commande")->RechercheCommandeUser($userID);
        return $this->render('@Panier/Panier/AfficherCommandeFront.html.twig',array('commandes'=>$commandes));
    }



    public function supprimerCommandeFrontAction(Request $request)
{
    $id=$request->get('id');
    $user=$this->getUser();
    $historique=new Historique();
    $username=$this->getUser()->getUsername();
    $em=$this->getDoctrine()->getManager();
    $commande=$em->getRepository("PanierBundle:Commande")->find($id);
    $historique->setIdUtilisateur($user);
    $historique->setIdCommande($commande->getId());
    $historique->setDateAction(new \DateTime('now'));
    $historique->setAction($username." has canceled an order with the reference : ".$commande->getReference());
    $em->remove($commande);
    $em->persist($historique);
    $em->flush();
    return $this->redirectToRoute('AfficherCommandesFront');
}

    public function supprimerCommandeBackAction($id)
    {
        $em=$this->getDoctrine()->getManager();
        $commande=$em->getRepository("PanierBundle:Commande")->find($id);
        $em->remove($commande);
        $em->flush();
        return $this->redirectToRoute('AfficherCommandes');
    }



    public function ValiderCommandesAction(Request $request)
    {
        $userManager = $this->get('fos_user.user_manager');
        $id=$request->get('id');
        $em = $this->getDoctrine()->getManager();
        $commande = $em->getRepository('PanierBundle:Commande')->find($id);
        $commande->setEtatC(1);
        $em->flush();
        $idUser=$commande->getIdUser();
        $nb=$em->getRepository('PanierBundle:Commande')->NombreDeCommandesValides($commande->getIdUser()->getId());
        $nbCommandes=$nb[0]['nbCommandes'];
        $nbCommandesValides=(int) $nbCommandes;
        $reste=$nbCommandesValides % 3;
        $user=$userManager->findUserBy(array('id'=>$idUser));
        $username=$user->getUsername();
        if($nbCommandesValides != 0 && $nbCommandesValides % 3 == 0 ){
            $total= $commande->getTotalC();
            $nvtotal = $total - ($total * 0.2);
            $commande->setTotalC($nvtotal);
            $historique=new Historique();
            $historique->setAction($username." had a 20% discount on the following order: ".$commande->getReference());
            $historique->setIdCommande($commande->getId());
            $historique->setDateAction(new \DateTime('now'));
            $historique->setIdUtilisateur($user);
            $em->persist($commande);
            $em->persist($historique);
            $em->flush();
        }


        $idProduit=$commande->getIdP();

        $produit = $em->getRepository('ProduitBundle:Produit')->find($idProduit);
        $quantite=$commande->getQuantiteC();
        $quantiteProduit=$produit->getNvquantite() ;
        $nvquantite=$quantiteProduit-$quantite;
        $produit->setNvquantite($nvquantite);
        $em->persist($produit);
        $em->flush();
        return $this->redirectToRoute('AfficherCommandes') ;
    }


    public function PdfAction(Request $request)
    {
        $userManager = $this->get('fos_user.user_manager');
        $id=$request->get('id');
        $doctrine=$this->getDoctrine();
        $repository=$doctrine->getRepository('PanierBundle:Commande');
        $commande=$repository->find($id);
        $reference=$commande->getReference();
        $quantite=$commande->getQuantiteC();
        $date=$commande->getDateC();
        $total=$commande->getTotalC();
        $idUser=$commande->getIdUser();
        $idProduit=$commande->getIdP();
        $user=$userManager->findUserBy(array('id'=>$idUser));
        $produit=$doctrine->getRepository('ProduitBundle:Produit')->find($idProduit);
        $userName=$user->getUsername();
        $idU=$user->getId();
        $produitName=$produit->getNom();
        $nb=$doctrine->getRepository('PanierBundle:Commande')->NombreDeCommandesValides($idU);

        $nbCommandes=$nb[0]['nbCommandes'];
        $nbCommandesValides=(int) $nbCommandes;

        if( $nbCommandesValides >= 4)
        {
            $msg='20%';
        }
        else
        {
            $msg='-';
        }
        return new PdfResponse(
            $this->get('knp_snappy.pdf')->generateFromHtml(
                $this->renderView(
                    'PanierBundle:Panier:pdfSenda.html.twig',
                    array(
                        'reference'  => $reference ,
                        'quantite' =>$quantite ,
                        'date' =>$date,
                        'total'=>$total,
                        'username'=>$userName,
                        'produitName'=>$produitName,
                        'msg'=>$msg
                    )
                ),
                'C:\pdfsenda\file'.$reference.'.pdf'
            ));

    }

    public function TriCommandeAction(Request $request)
    {
        $choix=$request->get('choix');
        $em=$this->getDoctrine()->getManager();

        if($choix == "total_c")
        {
            $commande=$em->getRepository('PanierBundle:Commande')->TriParPrix();
        }
        if($choix == "etat_c")
        {
            $commande=$em->getRepository('PanierBundle:Commande')->TriParEtat();
        }
        if($choix == "idP")
        {
            $commande=$em->getRepository('PanierBundle:Commande')->TriParProduit();
        }
        if($choix == "date_c")
        {
            $commande=$em->getRepository('PanierBundle:Commande')->TriParDate();
        }

        return $this->render('@Panier/Panier/AfficherCommandes.html.twig',array('commandes'=>$commande));

    }

    public function DisplayStatisticsAction()
    {
        $doctrine=$this->getDoctrine();
        $repository=$doctrine->getRepository('ProduitBundle:Produit');
        $produits=$repository->findAll();
        return $this->render('@Panier/Panier/StatisticCommande.html.twig',array('produits'=>$produits));
    }

    public function statCommandesAction(Request $request){
        $months=array("January","February","March","April","May","June","July","August","September","October","November","December");
        $nomProduit=$request->get('choix');
        $barChart = new BarChart();
        $pieChart = new PieChart();
        $doctrine=$this->getDoctrine();
        $repository=$doctrine->getRepository('ProduitBundle:Produit');
        $produits=$repository->ProduitName($nomProduit);
        $idP=$produits->getId();
        $nbCommandes=$doctrine->getRepository('PanierBundle:Commande')->nbCommandesProduits($idP);
        $comJan=0;
        $comFeb=0;
        $comMar=0;
        $comApr=0;
        $comMay=0;
        $comJune=0;
        $comJuly=0;
        $comAug=0;
        $comSep=0;
        $comOct=0;
        $comJNov=0;
        $comDec=0;

        for ($i=0;$i<count($nbCommandes);$i++)
        {
            if ($nbCommandes[$i]['mois'] == '1')
            {
                $comJan=(int) $nbCommandes[$i]['nbCommandes'];
            }
        }
        for ($i=0;$i<count($nbCommandes);$i++)
        {
            if ($nbCommandes[$i]['mois'] == '2')
            {
                $comFeb=(int) $nbCommandes[$i]['nbCommandes'];
            }
        }
        for ($i=0;$i<count($nbCommandes);$i++)
        {
            if ($nbCommandes[$i]['mois'] == '3')
            {
                $comMar=(int) $nbCommandes[$i]['nbCommandes'];
            }
        }
        for ($i=0;$i<count($nbCommandes);$i++)
        {
            if ($nbCommandes[$i]['mois'] == '4')
            {
                $comApr=(int) $nbCommandes[$i]['nbCommandes'];
            }
        }
        for ($i=0;$i<count($nbCommandes);$i++)
        {
            if ($nbCommandes[$i]['mois'] == '5')
            {
                $comMay=(int) $nbCommandes[$i]['nbCommandes'];
            }
        }
        for ($i=0;$i<count($nbCommandes);$i++)
        {
            if ($nbCommandes[$i]['mois'] == '6')
            {
                $comJune=(int) $nbCommandes[$i]['nbCommandes'];
            }
        }
        for ($i=0;$i<count($nbCommandes);$i++)
        {
            if ($nbCommandes[$i]['mois'] == '7')
            {
                $comJuly=(int) $nbCommandes[$i]['nbCommandes'];
            }
        }
        for ($i=0;$i<count($nbCommandes);$i++)
        {
            if ($nbCommandes[$i]['mois'] == '8')
            {
                $comAug=(int) $nbCommandes[$i]['nbCommandes'];
            }
        }

        for ($i=0;$i<count($nbCommandes);$i++)
        {
            if ($nbCommandes[$i]['mois'] == '9')
            {
                $comSep=(int) $nbCommandes[$i]['nbCommandes'];
            }
        }

        for ($i=0;$i<count($nbCommandes);$i++)
        {
            if ($nbCommandes[$i]['mois'] == '10')
            {
                $comOct=(int) $nbCommandes[$i]['nbCommandes'];
            }
        }

        for ($i=0;$i<count($nbCommandes);$i++)
        {
            if ($nbCommandes[$i]['mois'] == '11')
            {
                $comJNov=(int) $nbCommandes[$i]['nbCommandes'];
            }
        }

        for ($i=0;$i<count($nbCommandes);$i++)
        {
            if ($nbCommandes[$i]['mois'] == '12')
            {
                $comDec=(int) $nbCommandes[$i]['nbCommandes'];
            }
        }

        $commMonth=array($comJan,$comFeb,$comMar,$comApr,$comMay,$comJune,$comJuly,$comAug,$comSep,$comOct,$comJNov,$comDec);

        $data= array();
        $stat=['Month', 'ORDERS'];
        array_push($data,$stat);

        for ($i=0;$i<count($months);$i++) {
            $stat = [$months[$i],$commMonth[$i]];
            array_push($data, $stat);
        }

        $barChart->getData()->setArrayToDataTable($data);

        $barChart->getOptions()->setTitle($nomProduit);
        $barChart->getOptions()->setHeight(500);
        $barChart->getOptions()->setWidth(900);
        $barChart->getOptions()->getTitleTextStyle()->setBold(true);
        $barChart->getOptions()->getTitleTextStyle()->setColor('#009900');
        $barChart->getOptions()->getTitleTextStyle()->setItalic(true);
        $barChart->getOptions()->getTitleTextStyle()->setFontName('Arial');
        $barChart->getOptions()->getTitleTextStyle()->setFontSize(20);


        return $this->render('@Panier/Panier/displayStatisitic.html.twig',array('barchart'=>$barChart));

    }

public function HistoriqueClientAction(Request $request)
{
    $idCommande=$request->get('id');
    $userManager = $this->get('fos_user.user_manager');
    $doctrine=$this->getDoctrine();
    $repository=$doctrine->getRepository('PanierBundle:Commande');
    $commande=$repository->find($idCommande);
    $user=$commande->getIdUser();
    $userID=$user->getId();
    $repository=$doctrine->getRepository('PanierBundle:Historique');
    $Historique=$repository->RechercheHistorique($userID);

    return $this->render('@Panier/Panier/HistoriqueClient.html.twig',array('historique'=>$Historique));
}





}
