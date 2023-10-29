<?php

namespace App\Controller;

use App\Entity\Author;
use App\Form\AuthorType;
use App\Repository\AuthorRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthorController extends AbstractController
{
    #[Route('/allAuthor', name: 'app_all_author')]
    public function showAll(AuthorRepository $authorRepo): Response
    {
        $authors = $authorRepo->findAll();
        return $this->render('author/index.html.twig', [
            'controller_name' => 'AuthorController',
            'authors' => $authors
        ]);
    }

    #[Route('/addAuthor', name: 'app_author_add')]
    public function addAuthor(ManagerRegistry $mr,Request $request): Response
    {
        $author = new Author();
        $form = $this->createForm(AuthorType::class, $author);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $mr->getManager();
            $em->persist($author);
            $em->flush();
            return $this->redirectToRoute('app_all_author');
        }

        return $this->render('author/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/editAuthor/{id}', name: 'app_author_edit')]
    public function editAuthor(ManagerRegistry $mr,Request $request,AuthorRepository $authorRepo, int $id): Response
    {
        $author = $authorRepo->find($id);
        $form = $this->createForm(AuthorType::class, $author);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $mr->getManager();
            $em->persist($author);
            $em->flush();
            return $this->redirectToRoute('app_all_author');
        }

        return $this->render('author/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/deleteAuthor/{id}', name: 'app_author_delete')]
    public function deleteAuthor(ManagerRegistry $mr,AuthorRepository $authorRepo, int $id): Response
    {
        $author = $authorRepo->find($id);
        $em = $mr->getManager();
        $em->remove($author);
        $em->flush();
        return $this->redirectToRoute('app_all_author');
    }

    #[Route('/listAuthorByEmail', name: 'app_author_listByEmail')]
    public function listAuthorByEmail(AuthorRepository $authorRepo): Response
    {
        $authors = $authorRepo->listAuthorByEmail();
        return $this->render('author/index.html.twig', [
            'controller_name' => 'AuthorController',
            'authors' => $authors
        ]);
    }

    //rechercher la liste des auteurs dont le nombre de livres est compris entre deux valeurs
    #[Route('/listAuthorByNbBook', name: 'app_author_listByNbBook')]
    public function listAuthorByNbBook(AuthorRepository $authorRepo,Request $request): Response
    {
        $min = $request->request->get('min');
        $max = $request->request->get('max');
        $authors = $authorRepo->listAuthorByNbBook($min, $max);
        return $this->render('author/index.html.twig', [
            'controller_name' => 'AuthorController',
            'authors' => $authors
        ]);
    }
}
