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

    #[Route('/books/byRef', name: 'app_book_byRef')]
    public function searchBookByRef(BookRepository $bookRepo,
                            Request $request): Response
    {
        $ref = $request->request->get('search');
        $book = $bookRepo->searchBookByRef($ref);
        return $this->render('book/index.html.twig', [
            'controller_name' => 'BookController',
            'books' => $book
        ]);
    }

    //d’afficher la liste des livres triée par auteur.
    #[Route('/books/orderByAuthor', name: 'app_book_orderByAuthor')]
    public function findAllOrderByAuthor(BookRepository $bookRepo): Response
    {
        $result = $bookRepo->findAllOrderByAuthor();
        return $this->render('book/index.html.twig', [
            'controller_name' => 'BookController',
            'books' => $result
        ]);
    }

    //Afficher la liste des livres publiés avant l’année 2023 dont l’auteur a plus de 10 livres
    #[Route('/books/publishedBefore2023WithAuthorMoreThan10Books', name: 'app_book_publishedBefore2023WithAuthorMoreThan10Books')]
    public function findPublishedBooksBefore2023WithAuthorMoreThan10Books(BookRepository $bookRepo): Response
    {
        $result = $bookRepo->findBookPublishedBefore2023AndAuthorHasMoreThan10Books();
        return $this->render('book/index.html.twig', [
            'controller_name' => 'BookController',
            'books' => $result
        ]);
    }

    //Afficher le nombre des livres dont la catégorie est « Romance ».
    #[Route('/books/countBooksByCategory', name: 'app_book_countBooksByCategory')]
    public function countBooksByCategory(BookRepository $bookRepo): Response
    {
        $result = $bookRepo->countBooksByCategory('Romance');
        return new Response("<h3>le nombre des livres dont la catégorie est « Romance » est :
                            {$result}</h3>");
    }

    //Afficher la liste des livres publiés entre deux dates « 2014-01-01 » et «2018- 12-31 ».
    #[Route('/books/publishedBetweenTwoDates', name: 'app_book_publishedBetweenTwoDates')]
    public function findPublishedBooksBetweenTwoDates(BookRepository $bookRepo): Response
    {
        $result = $bookRepo->findBooksPublishedBetweenTwoDates('2014-01-01','2018-12-31');
        return $this->render('book/index.html.twig', [
            'controller_name' => 'BookController',
            'books' => $result
        ]);
    }
}
