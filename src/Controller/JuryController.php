<?php

namespace App\Controller;


use App\Entity\Jury;
use App\Entity\Note;
use App\Entity\User;
use App\Entity\Folder;
use App\Form\NoteType;
use App\Repository\UserRepository;
use App\Repository\SecretaryRepository;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class JuryController extends AbstractController
{
    /**
     * @Route("/jury/listeUtilisateur", name="listJuryUser")
     */
    public function listeUser(UserRepository $repo)
        {
            $repo = $this->getDoctrine()->getRepository(User::class);
            $user = $repo->findAll();
            return $this->render('jury/listeUser.html.twig', [
                'user' => $user
            ]);
        }

    /**
     * @Route("/jury/{id}/NoteUtilisateur", name="noteUser")
     * 
     * @param User $user
     * @return Response
     */
    public function noteUser(User $user, Request $request, ObjectManager $manager)
        {
            $juryId = $this->getUser()->getId();
            $juryId = $manager->getRepository(Jury::class)->find($juryId);
            $folderId = $manager->getRepository(Folder::class)->findOneBy([
                'user' => $user,
            ]);
            $check = $manager->getRepository(Note::class)->findOneBy([
                'jury' => $juryId,
                'folder' => $folderId,
            ]);
            
            if(!$check){
                $note = new Note();
                $form = $this->createForm(NoteType::class, $note);
                $form->handleRequest($request);
                if($form->isSubmitted() && $form->isValid()){
                    $note->setJury($juryId);
                    $note->setFolder($folderId);
                    $manager->persist($note);
                    $manager->flush();
                    $allnote = $manager->getRepository(Note::class)->findBy([
                        'folder' => $folderId
                    ]);
                    $count = count($allnote);
                    $total = 0;
                    foreach($allnote as $allnote ){
                        $value = $allnote->getNote();
                        $total = $total + $value;
                        
                    };
                    $moyenne = $total / $count;
                    $folderId->setFinalNote($moyenne);
                    $manager->persist($folderId);
                    $manager->flush();
                }
                
            }else{
                $note = $manager->getRepository(Note::class)->findOneBy([
                    'jury' => $juryId,
                    'folder' => $folderId,
                ]);
                $form = $this->createForm(NoteType::class, $note);
                $form->handleRequest($request);
                if($form->isSubmitted() && $form->isValid()){
                    $manager->persist($note);
                    $manager->flush();
                    $allnote = $manager->getRepository(Note::class)->findBy([
                        'folder' => $folderId
                    ]);
                    $count = count($allnote);
                    $total = 0;
                    foreach($allnote as $allnote ){
                        $value = $allnote->getNote();
                        $total = $total + $value;
                        
                    };
                    $moyenne = $total / $count;
                    $folderId->setFinalNote($moyenne);
                    $manager->persist($folderId);
                    $manager->flush();
                    
                }
            }
            
            
            
            return $this->render('jury/noteUser.html.twig', [
                'user' => $user,
                'noteForm' => $form->createView(),
            ]);
        }

         /**
     * @Route("/jury/{id}/dossierUtilisateur", name="dossierUser")
     * 
     * @param User $user
     * @return Response
     */
    public function dossierUser(User $user)
    {      
        return $this->render('jury/dossierUser.html.twig', [
            'user' => $user,
        ]);
    }
}

