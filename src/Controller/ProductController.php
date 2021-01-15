<?php


// src/Controller/ProductController.php
namespace App\Controller;
use App\Entity\mkdir;
use App\Entity\Product;
use App\Form\ProductType;
use App\Form\mkdirFormType;
use App\Repository\UserRepository;
use App\Form\adminQuota;
use RecursiveIteratorIterator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProductController extends AbstractController
{
    /**
     * @Route("/product/new/", name="app_product_new")
     */
    public function new(Request $request, SluggerInterface $slugger, UserRepository $userRepository)
    {

        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);


        $stockage = $this->getUser()->getFormule();

        /*  if ($form->isSubmitted() && $form->isValid()) {
              /** @var UploadedFile $brochureFile
              $brochureFile = $form->get('brochure')->getData();

              // this condition is needed because the 'brochure' field is not required
              // so the PDF file must be processed only when a file is uploaded


              if ($brochureFile) {

                  /* $fileUploader =null;
                   $brochureFileName = $fileUploader->upload($brochureFile);
                   $product->setBrochureFilename($brochureFileName);

                  $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
                  // this is needed to safely include the file name as part of the URL
                  $safeFilename = $slugger->slug($originalFilename);
                  $newFilename = $safeFilename . '-' . uniqid() . '.' . $brochureFile->guessExtension();

                  // Move the file to the directory where brochures are stored
                  try {
                      $Debut = $this->getParameter('brochures_directory');
                      $nomUser = $this->getUser()->getUsername();
                      $Debut = $Debut . '/' . $nomUser;
                      $brochureFile->move(
                      // $this->getParameter('brochures_directory'),
                          $Debut,
                          $newFilename
                      );
                  } catch (FileException $e) {
                      // ... handle exception if something happens during file upload
                  }

                  // updates the 'brochureFilename' property to store the PDF file name
                  // instead of its contents
                  $product->setBrochureFilename($newFilename);
              }

              // ... persist the $product variable or any other work

              return $this->forward('App\Controller\ProductController::new');

             // return $this->redirectToRoute('app_product_new_upload"');
          } */


        //$nameDir = $this->getParameter('kernel.project_dir').'/public';
        $nomUser = $this->getUser()->getUsername();
        $cheminDossierUser = $this->getParameter('kernel.project_dir') . '/public/' . $nomUser;

        if (file_exists($cheminDossierUser)) {
        } else {
            mkdir($cheminDossierUser);
        }

        $dir = opendir($cheminDossierUser) or die('Erreur de listage : le répertoire n\'existe pas');
        $files = array();


        $dir_iterator = new RecursiveDirectoryIterator($cheminDossierUser, 0, false);
        $iterator = new RecursiveIteratorIterator($dir_iterator, 1, 0);
        //  new RecursiveDirectoryIterator()

        //$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($nameDir), RecursiveIteratorIterator::SELF_FIRST);
        $Folder = [];
        $Folder[] = ["racine" . "Directory"];
        $Files = [];
        foreach ($iterator as $file) {

            $test = $file->getFilename();
            if ($test != '..' && $test != '.') {
                if (is_dir($file)) {
                    $test = str_replace("/", "|", $file);
                    $Folder[] = [$test . "Directory"];
                } else {
                    $test = $file->getPathname();
                    $aEnlever = $this->getParameter('kernel.project_dir') . '/public/' . $nomUser;
                    $test = str_replace($aEnlever, "", $test);
                    $test = str_replace("/", "|", $test);
                    $Files[] = $test . "File";
                }
            }
        }

        // var_dump($Folder);
        //var_dump($Files);

        foreach ($Files as $file) {

            for ($i = 0; $i < count($Folder); $i++) {
                $chaineATest = substr($Folder[$i][0], 0, -9);
                $nombreBarre = substr_count($file, '|');
                //var_dump($nombreBarre);
                $tabSeparate = explode("|", $file);
                //var_dump($tabSeparate);
                if (count($tabSeparate) > 2) {
                    $file2 = $tabSeparate[$nombreBarre - 1];
                } else {
                    $file2 = 'chaineImPosSibleAtRouver';
                    $FichierDeLaRacine[0][] = $file;
                }


                $nombreBarre = substr_count($chaineATest, '|');
                $tabSeparate = explode("|", $chaineATest);
                $chaineATest = $tabSeparate[$nombreBarre];

                // var_dump($chaineATest);

                if (str_contains($file2, $chaineATest)) {

                    $Folder[$i][] = $file;
                }
            }


        }
        if (isset($FichierDeLaRacine)) {
            $FichierDeLaRacine = array_unique($FichierDeLaRacine);
            for ($i = 0; $i < count($FichierDeLaRacine); $i++) {
                $Folder[0][] = $FichierDeLaRacine[$i][0];
            }
        }

        for ($i = 0; $i < count($Folder); $i++) {

            $file = $Folder[$i][0];

            $nomUser = $this->getUser()->getUsername();
            $chaineAvirer = $this->getParameter('kernel.project_dir') . '/public/' . $nomUser;
            $chaineAvirer = str_replace("/", "|", $chaineAvirer);
            $nomDossierPropre = str_replace($chaineAvirer, "", $file);
            $Folder[$i][0] = $nomDossierPropre;

        }

        for ($i = 0; $i < count($Folder); $i++) {
            $tableauDeBase = $Folder[$i];
            $tableauAsso = [];

            foreach ($tableauDeBase as $file) {
                if (str_contains($file, "Directory")) {

                    if ($file == 'racineDirectory') {
                        $nomTransition = '';
                    } else {

                        $nomTransition = str_replace("|", "/", $file);
                        $nomTransition = substr($nomTransition, 0, -9);
                    }

                    $compteur = 0;
                    $element = $this->getParameter('kernel.project_dir') . '/public/' . $nomUser . $nomTransition;
                    $dir_iterator = new RecursiveDirectoryIterator($element, 0, true);
                    $iterator = new RecursiveIteratorIterator($dir_iterator, 1, 0);
                    foreach ($iterator as $key => $value) {

                        $test = str_replace("$nomUser", "|", $key);

                        if (!str_contains($test, "/."))

                            $compteur++;

                    }


                    $tableauAsso[$file] = $compteur;
                } else {
                    $filePourDate = str_replace("|", "/", $file);
                    $filePourDate = substr($filePourDate, 0, -4);

                    $tableauAsso[$file] = "Date et heure de l'upload : " . date("F d Y H:i:s.", filemtime($this->getParameter('kernel.project_dir') . '/public/' . $nomUser . $filePourDate)) . " taille : " . filesize($this->getParameter('kernel.project_dir') . '/public/' . $nomUser . $filePourDate) . "KB";
                }
            }
            $Folder[$i] = $tableauAsso;
        }
        //  var_dump($Folder);
        //  var_dump($Folder);

        /*
                $filesPath[]=["racine"."Directory"];
                $aParcourirALaFin=[];
                foreach ($iterator as $file) {

                    $test = $file->getPathname();
                    $test2=$file->getFilename();

                    $aEnlever= $this->getParameter('kernel.project_dir') . '/public/' . $nomUser;
                    $test = str_replace($aEnlever, "", $test);
                    $test = str_replace("/", "|", $test);
                    if($test2 != '..' && $test2 != '.' ) {

                        $nombreBarre = substr_count($test,'|');

                        if(is_dir($file)) {

                             {
                                 if(array_key_exists($nombreBarre, $filesPath)) {
                                     $filesPath[$nombreBarre][] =  $filesPath[$nombreBarre];
                                    array_shift($filesPath[$nombreBarre]);
                                     $filesPath[$nombreBarre][] = [$test . "Directory"];

                                 }
                                 else {

                                     $filesPath[$nombreBarre] = [$test . "Directory"];
                                 }
                            }
                        }
                        else{

                            if(array_key_exists($nombreBarre, $filesPath) )
                            {

                                $filesPath[$nombreBarre-1][] = $test."File";
                            }
                            else
                            {
                                $aParcourirALaFin[]= $test."File";

                            }
                         //   $filesPath[$nombreBarre] = [$test."File"];
                        }
                    }
                }

                   foreach ($aParcourirALaFin as $fichierRestant)
                 {
                      $nombreBarre = substr_count($fichierRestant,'|');


                      if(array_key_exists($nombreBarre-1, $filesPath) )
                      {
                          $nbElement=count($filesPath[$nombreBarre-1]);
                          for ($i=0;$i<$nbElement;$i++)
                          {
        print"yoyoyo";
                              $chaineATest=$filesPath[$nombreBarre-1][$i][0];
                              print ($chaineATest);
                              print"yoyoyo";
                              //$chaineATest=strval($chaineATest);
                              $chaineATest=substr($chaineATest,0,-9);

                              if (str_contains($fichierRestant,$chaineATest) AND is_array($filesPath[$nombreBarre-1][$i]))
                              {
                                  $filesPath[$nombreBarre-1][$i][]=$fichierRestant;
                              }
                          }



                        //$filesPath[$nombreBarre-1][]=$fichierRestant;
                    }
                }*/

        /*  while($element = readdir($dir)) {
              if($element != '..' && $element != '.' ) {
                  if (is_dir($element))
                      $files[] = $element;
              }
          }*/
        closedir($dir);


        $mkdir = new mkdir();
        $formMkdir = $this->createForm(mkdirFormType::class, $mkdir);
        $formMkdir->handleRequest($request);

        $stockageRestant = $this->getUser()->getActuelStockage();



        $AfficherAjoutFichier = false;
        $AfficherAjoutDossier = false;
        return $this->render('product/new.html.twig', [
            'form' => $form->createView(),
            'cheminDossierUser' => $files,
            'filesPath'=> $Folder,
            'url'=> 'download/',
            'urlDossier'=>'',
            'endroitAdding'=>"racineDirectory",
            'endroitAddingDossier'=>"racineDirectory",
            'formMkdir' => $formMkdir->createView(),
            'test'=> $stockage,
            'AfficherAjoutFichier' => $AfficherAjoutFichier,
            'AfficherAjoutDossier' => $AfficherAjoutDossier,
            'StockageRestant' => $stockageRestant,
        ]);
    }

    /**
     * @return BinaryFileResponse
     * @Route("/product/new/download/{path}", name="app_product_download")
     */
    public function downloadAction($path)
    {
        //var_dump($path);
        $newPath = str_replace("|", "/", $path);
        //var_dump($newPath);
        $nomUser= $this->getUser()->getUsername();
        $cheminDossierUser = $this->getParameter('kernel.project_dir') . '/public/' . $nomUser."/".$newPath;
        $cheminDossierUser=substr($cheminDossierUser,0,-4);
        //$path = $this->get('kernel')->getRootDir(). "/../downloads/";
        $file = $cheminDossierUser; // Path to the file on the server
        $response = new BinaryFileResponse($file);

        // Give the file a name:
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT,$path);

        return $response;
    }

  
    /**
     * @param $path
     * @Route ("/product/new/delete/{path}", name="app_product_delete")
     */
    public function deleteAction($path)
    {
        $newPath = str_replace("|", "/", $path);
        $newnewPath=substr($newPath,0,-4);
        $nomUser= $this->getUser()->getUsername();
        $cheminDuFichier = $this->getParameter('kernel.project_dir') . '/public/'. $nomUser . $newnewPath;

        $tailleFichier = filesize($cheminDuFichier);
        $stockage = $this->getUser()->getActuelStockage();
        $this->getUser()->setActuelStockage($stockage + $tailleFichier);
        $em = $this->getDoctrine()->getManager();
        $em->persist($this->getUser());
        $em->flush();


        unlink($cheminDuFichier);
        return $this->redirectToRoute('app_product_new');

        //return $response;
    }

    /**
     * @Route("/product/new/deleteDir/{path}", name="app_product_delete_dir")
     */
    public function deleteActionDir($path)
    {

        $newPath = str_replace("|", "/", $path);
        $newnewPath=substr($newPath,0,-9);
        $nomUser= $this->getUser()->getUsername();
        $user=$this->getUser();
        $cheminDuFichier = $this->getParameter('kernel.project_dir') . '/public/'. $nomUser . $newnewPath;
        
        $tailleFichierSupp=0;

        function deleteTree($dir,$user){
            foreach(glob($dir . "/*") as $element){
                if(is_dir($element)){
                    deleteTree($element,$user); // On rappel la fonction deleteTree
                    rmdir($element); // Une fois le dossier courant vidé, on le supprime
                } else { // Sinon c'est un fichier, on le supprime


                    $stockage = $user->getActuelStockage();
                    $tailleFichierSupp = filesize($element);
                    var_dump($dir);
                    var_dump($tailleFichierSupp);
                    $user->setActuelStockage($stockage + $tailleFichierSupp);

                    unlink($element);

                }
                // On passe à l'élément suivant
            }

        }

        deleteTree($cheminDuFichier,$user);
        rmdir($cheminDuFichier); // Et on le supprime

        $em = $this->getDoctrine()->getManager();
        $em->persist($this->getUser());
        $em->flush();

        return $this->forward('App\Controller\ProductController::new');

        //return $response;
    }

    /**
     * @Route("/product/new/deleteUser/{path}/{id}", name="app_product_delete_dir")
     */
    public function deleteUser($path,$id,UserRepository $userRepository)
    {


        $cheminDuFichier = $this->getParameter('kernel.project_dir') . '/public/' . $path;


            function deleteTree($dir){
                foreach(glob($dir . "/*") as $element){
                    if(is_dir($element)){
                        deleteTree($element); // On rappel la fonction deleteTree
                        rmdir($element); // Une fois le dossier courant vidé, on le supprime
                    } else { // Sinon c'est un fichier, on le supprime


                        unlink($element);

                    }
                    // On passe à l'élément suivant
                }

            }

        deleteTree($cheminDuFichier);
        rmdir($cheminDuFichier); // Et on le supprime

        $a=$userRepository->findBy(['id'=>$id]);
        $em = $this->getDoctrine()->getManager();
        $em->remove($a[0]);
        $em->persist($this->getUser());
        $em->flush();

        return $this->redirectToRoute('home');

        //return $response;
    }


}

