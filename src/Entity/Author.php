<?php

namespace App\Entity;

use App\Repository\AuthorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AuthorRepository::class)]
class Author
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $username = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: Book::class)]
    private Collection $bookList;

    #[ORM\Column]
    private ?int $nb_books = null;

    public function __construct()
    {
        $this->bookList = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection<int, Book>
     */
    public function getBookList(): Collection
    {
        return $this->bookList;
    }

    public function addBookList(Book $bookList): static
    {
        if (!$this->bookList->contains($bookList)) {
            $this->bookList->add($bookList);
            $bookList->setAuthor($this);
        }

        return $this;
    }

    public function removeBookList(Book $bookList): static
    {
        if ($this->bookList->removeElement($bookList)) {
            // set the owning side to null (unless already changed)
            if ($bookList->getAuthor() === $this) {
                $bookList->setAuthor(null);
            }
        }

        return $this;
    }

    public function getNbBooks(): ?int
    {
        return $this->nb_books;
    }

    public function setNbBooks(int $nb_books): static
    {
        $this->nb_books = $nb_books;

        return $this;
    }

    public function __toString(): string
    {
        return $this->username;
    }

    public function addBook(Book $book)
    {
        $this->bookList->add($book);
    }
}
