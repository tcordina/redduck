<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\User\UserInterface;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks
 * @Vich\Uploadable
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $bio;

    /**
     * @ORM\Column(type="array")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    /**
     * @Vich\UploadableField(mapping="images_users", fileNameProperty="image")
     * @Assert\File(maxSize="1M", mimeTypes={"image/jpeg", "image/png", "image/gif"})
     * @var File
     */
    private $imageFile;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Post", inversedBy="upvotes")
     * @ORM\JoinTable(name="post_upvotes",
     *     joinColumns={@ORM\JoinColumn(onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(onDelete="CASCADE")}
     * )
     */
    private $upvotedposts;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Post", inversedBy="downvotes")
     * @ORM\JoinTable(name="post_downvotes",
     *     joinColumns={@ORM\JoinColumn(onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(onDelete="CASCADE")}
     * )
     */
    private $downvotedposts;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Message", inversedBy="upvotes")
     * @ORM\JoinTable(name="message_upvotes",
     *     joinColumns={@ORM\JoinColumn(onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(onDelete="CASCADE")}
     * )
     */
    private $upvotedmessages;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Message", inversedBy="downvotes")
     * @ORM\JoinTable(name="message_downvotes",
     *     joinColumns={@ORM\JoinColumn(onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(onDelete="CASCADE")}
     * )
     */
    private $downvotedmessages;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    private $plainpassword;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Post", mappedBy="author")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $posts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Message", mappedBy="author")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $messages;


    public function __construct()
    {
        $this->posts = new ArrayCollection();
        $this->messages = new ArrayCollection();
        $this->upvotedposts = new ArrayCollection();
        $this->downvotedposts = new ArrayCollection();
        $this->upvotedmessages = new ArrayCollection();
        $this->downvotedmessages = new ArrayCollection();
    }

    public function __toString()
    {
        return (string)$this->username;
    }

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->createdAt = new \DateTime("now");
    }

    /**
     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->updatedAt = new \DateTime("now");
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(?string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function hasRole($role): bool
    {
        $role = strtoupper($role);
        return in_array($role, $this->roles);
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string)$this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(?\DateTimeInterface $createdAt): self
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

    public function getPlainpassword()
    {
        return $this->plainpassword;
    }

    public function setPlainpassword($plainpassword)
    {
        $this->plainpassword = $plainpassword;
        return $this;
    }

    /**
     * @return Collection|Post[]
     */
    public function getPosts(): Collection
    {
        return $this->posts;
    }

    public function addPost(Post $post): self
    {
        if (!$this->posts->contains($post)) {
            $this->posts[] = $post;
            $post->setAuthor($this);
        }

        return $this;
    }

    public function removePost(Post $post): self
    {
        if ($this->posts->contains($post)) {
            $this->posts->removeElement($post);
            // set the owning side to null (unless already changed)
            if ($post->getAuthor() === $this) {
                $post->setAuthor(null);
            }
        }

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
            $message->setAuthor($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): self
    {
        if ($this->messages->contains($message)) {
            $this->messages->removeElement($message);
            // set the owning side to null (unless already changed)
            if ($message->getAuthor() === $this) {
                $message->setAuthor(null);
            }
        }

        return $this;
    }

    public function getActivity(): array
    {
        $posts = array_reverse($this->posts->toArray());
        $messages = array_reverse($this->messages->toArray());
        $activity = [];
        $latest_post = 0;
        $latest_message = 0;
        for ($i = 0; $i < 10; $i++) {
            if (!isset($posts[$latest_post])) {
                if (isset($messages[$latest_message])) {
                    $activity[$i] = $messages[$latest_message];
                    $latest_message++;
                }
            } elseif (!isset($messages[$latest_message])) {
                if (isset($posts[$latest_post])) {
                    $activity[$i] = $posts[$latest_post];
                    $latest_post++;
                }
            } elseif ($posts[$latest_post]->getCreatedAt() > $messages[$latest_message]->getCreatedAt()) {
                $activity[$i] = $posts[$latest_post];
                $latest_post++;
            } elseif ($posts[$latest_post]->getCreatedAt() < $messages[$latest_message]->getCreatedAt()) {
                $activity[$i] = $messages[$latest_message];
                $latest_message++;
            }
        }
        return $activity;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function setBio(?string $bio): self
    {
        $this->bio = $bio;

        return $this;
    }

    public function getKarma(): ?int
    {
        $posts = $this->getPosts();
        $messages = $this->getMessages();
        $karma = 0;
        foreach ($posts as $post) {
            $karma += count($post->getUpvotes());
            $karma -= count($post->getDownvotes());
        }
        foreach ($messages as $msg) {
            $karma += count($msg->getUpvotes());
            $karma -= count($msg->getDownvotes());
        }
        return $karma;
    }

    /**
     * @return Collection|Post[]
     */
    public function getUpvotedposts(): Collection
    {
        return $this->upvotedposts;
    }

    public function addUpvotedpost(Post $upvotedpost): self
    {
        if (!$this->upvotedposts->contains($upvotedpost)) {
            $this->upvotedposts[] = $upvotedpost;
        }

        return $this;
    }

    public function removeUpvotedpost(Post $upvotedpost): self
    {
        if ($this->upvotedposts->contains($upvotedpost)) {
            $this->upvotedposts->removeElement($upvotedpost);
        }

        return $this;
    }


    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * @return Collection|Post[]
     */
    public function getDownvotedposts(): Collection
    {
        return $this->downvotedposts;
    }

    public function addDownvotedpost(Post $downvotedpost): self
    {
        if (!$this->downvotedposts->contains($downvotedpost)) {
            $this->downvotedposts[] = $downvotedpost;
        }

        return $this;
    }

    public function removeDownvotedpost(Post $downvotedpost): self
    {
        if ($this->downvotedposts->contains($downvotedpost)) {
            $this->downvotedposts->removeElement($downvotedpost);
        }

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getUpvotedmessages(): Collection
    {
        return $this->upvotedmessages;
    }

    public function addUpvotedmessage(Message $upvotedmessage): self
    {
        if (!$this->upvotedmessages->contains($upvotedmessage)) {
            $this->upvotedmessages[] = $upvotedmessage;
        }

        return $this;
    }

    public function removeUpvotedmessage(Message $upvotedmessage): self
    {
        if ($this->upvotedmessages->contains($upvotedmessage)) {
            $this->upvotedmessages->removeElement($upvotedmessage);
        }

        return $this;
    }

    /**
     * @return Collection|Message[]
     */
    public function getDownvotedmessages(): Collection
    {
        return $this->downvotedmessages;
    }

    public function addDownvotedmessage(Message $downvotedmessage): self
    {
        if (!$this->downvotedmessages->contains($downvotedmessage)) {
            $this->downvotedmessages[] = $downvotedmessage;
        }

        return $this;
    }

    public function removeDownvotedmessage(Message $downvotedmessage): self
    {
        if ($this->downvotedmessages->contains($downvotedmessage)) {
            $this->downvotedmessages->removeElement($downvotedmessage);
        }

        return $this;
    }
}
