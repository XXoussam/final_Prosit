<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BookController extends AbstractController
{
    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    #[Route('/books', name: 'app_book')]
    public function index(BookRepository $bookRepo): Response
    {
        $result = $bookRepo->findAll();
        $nbr_published_books = $bookRepo->countPublishedBooks();
        $nbr_unpublished_books = $bookRepo->countUnpublishedBooks();
        return $this->render('book/index.html.twig', [
            'controller_name' => 'BookController',
            'books' => $result,
            'nbr_published_books' => $nbr_published_books,
            'nbr_unpublished_books' => $nbr_unpublished_books
        ]);
    }

    #[Route('/books/add', name: 'app_book_add')]
    public function add(ManagerRegistry $mr,Request $request,AuthorRepository $authorRepo): Response
    {
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $em = $mr->getManager();
            $em->persist($book);
            $em->flush();
            $author = $authorRepo->find($book->getAuthor()->getId());
            $author->setNbBooks(count($author->getBookList()));
            $em->persist($author);
            $em->flush();
            return $this->redirectToRoute('app_book');
        }


        return $this->render('book/add.html.twig', [
            'controller_name' => 'BookController',
            'form' => $form->createView()
        ]);
    }

    #[Route('/books/edit/{id}', name: 'app_book_edit')]
    public function edit(ManagerRegistry $mr,Request $request,Book $book): Response
    {
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $em = $mr->getManager();
            $em->persist($book);
            $em->flush();
            return $this->redirectToRoute('app_book');
        }
        return $this->render('book/add.html.twig', [
            'controller_name' => 'BookController',
            'form' => $form->createView()
        ]);
    }

    #[Route('/books/remove/{id}', name: 'app_book_remove')]
    public function remove(BookRepository $bookRepo,
                           ManagerRegistry $mr,
                           int $id,
                           AuthorRepository $authorRepo): Response
    {
        $book = $bookRepo->find($id);
        $em = $mr->getManager();
        $em->remove($book);
        $em->flush();

        $author = $authorRepo->find($book->getAuthor()->getId());
        if (count($author->getBookList()) > 0) {
            $author->setNbBooks(count($author->getBookList()));
            $em->persist($author);
        }else{
            $em->remove($author);
        }
        $em->flush();

        return $this->redirectToRoute('app_book');
    }

    #[Route('/books/show/{id}', name: 'app_book_show')]
    public function publish(BookRepository $bookRepo,
                            ManagerRegistry $mr,
                            int $id): Response
    {
        $book = $bookRepo->find($id);
        return $this->render('book/show.html.twig', [
            'controller_name' => 'BookController',
            'book' => $book
        ]);
    }
}
