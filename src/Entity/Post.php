<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\HttpFoundation\File\File;
use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PostRepository")
 * @Vich\Uploadable
 */
class Post
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="posts", fetch="EAGER")
     * @ORM\JoinColumn(nullable=false)
     */
    private $author;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\SubCategory", inversedBy="posts")
     */
    private $subcategory;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @Vich\UploadableField(mapping="images_posts", fileNameProperty="image")
     * @Assert\File(maxSize="1M", mimeTypes={"image/jpeg", "image/png", "image/gif"})
     * @var File
     */
    private $imageFile;

    /**
     * @ORM\Column(type="text")
     */
    private $content;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Message", mappedBy="post")
     */
    private $messages;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", mappedBy="upvotedposts")
     */
    private $upvotes;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\User", mappedBy="downvotedposts")
     */
    private $downvotes;

    public function __construct()
    {
        $this->createdAt = new \DateTime('now');
        $this->messages = new ArrayCollection();
        $this->upvotes = new ArrayCollection();
        $this->downvotes = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAuthor(): User
    {
        return $this->author;
    }

    public function setAuthor(User $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getImageFile()
    {
        return $this->imageFile;
    }

    public function setImageFile(File $image = null)
    {
        $this->imageFile = $image;
        // VERY IMPORTANT:
        // It is required that at least one field changes if you are using Doctrine,
        // otherwise the event listeners won't be called and the file is lost
        if ($image) {
            // if 'updatedAt' is not defined in your entity, use another property
            $this->updatedAt = new \DateTime('now');
        }
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): self
    {
        if (!$this->messages->contains($message)) {
            $this->messages[] = $message;
            $message->setPost($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->contains($message)) {
            $this->messages->removeElement($message);
            // set the owning side to null (unless already changed)
            if ($message->getPost() === $this) {
                $message->setPost(null);
            }
        }

        return $this;
    }

    public function getSubcategory(): ?SubCategory
    {
        return $this->subcategory;
    }

    public function setSubcategory(?SubCategory $subcategory): self
    {
        $this->subcategory = $subcategory;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getUpvotes(): Collection
    {
        return $this->upvotes;
    }

    public function addUpvote(User $upvote): self
    {
        if (!$this->upvotes->contains($upvote)) {
            $this->upvotes[] = $upvote;
            $upvote->addUpvotedpost($this);
        }

        return $this;
    }

    public function removeUpvote(User $upvote): self
    {
        if ($this->upvotes->contains($upvote)) {
            $this->upvotes->removeElement($upvote);
            $upvote->removeUpvotedpost($this);
        }

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getDownvotes(): Collection
    {
        return $this->downvotes;
    }

    public function addDownvote(User $downvote): self
    {
        if (!$this->downvotes->contains($downvote)) {
            $this->downvotes[] = $downvote;
            $downvote->addDownvotedpost($this);
        }

        return $this;
    }

    public function removeDownvote(User $downvote): self
    {
        if ($this->downvotes->contains($downvote)) {
            $this->downvotes->removeElement($downvote);
            $downvote->removeDownvotedpost($this);
        }

        return $this;
    }
}
