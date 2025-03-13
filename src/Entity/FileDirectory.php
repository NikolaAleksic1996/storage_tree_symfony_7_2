<?php

namespace App\Entity;

use App\Repository\FileDirectoryRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FileDirectoryRepository::class)]
class FileDirectory
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    private string $name;

    #[ORM\ManyToOne(targetEntity: self::class, cascade: ['persist'], inversedBy: 'subdirectories')]
    #[ORM\JoinColumn(onDelete: "CASCADE")]
    private ?self $parent = null;

    /**
     * @var Collection<int, self>
     */
    #[ORM\OneToMany(targetEntity: self::class, mappedBy: "parent", cascade: ["persist", "remove"])]
    private Collection $subdirectories;

    /**
     * @var Collection<int, File>
     */
    #[ORM\OneToMany(targetEntity: File::class, mappedBy: 'directory', cascade: ["persist", "remove"])]
    private Collection $files;

    #[ORM\Column(type: 'datetime')]
    private DateTime $createdAt;

    public function __construct()
    {
        $this->subdirectories = new ArrayCollection();
        $this->files = new ArrayCollection();
        $this->createdAt = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setParent(?self $parent): static
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getSubdirectories(): Collection
    {
        return $this->subdirectories;
    }

    public function addSubdirectory(self $subdirectory): static
    {
        if (!$this->subdirectories->contains($subdirectory)) {
            $this->subdirectories->add($subdirectory);
            $subdirectory->setParent($this);
        }

        return $this;
    }

    public function removeSubdirectory(self $subdirectory): static
    {
        if ($this->subdirectories->removeElement($subdirectory)) {
            // set the owning side to null (unless already changed)
            if ($subdirectory->getParent() === $this) {
                $subdirectory->setParent(null);
            }
        }

        return $this;
    }

    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return Collection<int, File>
     */
    public function getFiles(): Collection
    {
        return $this->files;
    }

    public function addFile(File $file): static
    {
        if (!$this->files->contains($file)) {
            $this->files->add($file);
            $file->setDirectory($this);
        }

        return $this;
    }

    public function removeFile(File $file): static
    {
        if ($this->files->removeElement($file)) {
            // set the owning side to null (unless already changed)
            if ($file->getDirectory() === $this) {
                $file->setDirectory(null);
            }
        }

        return $this;
    }
}
