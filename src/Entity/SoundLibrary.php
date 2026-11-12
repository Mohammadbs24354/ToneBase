<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'sound_libraries')]
class SoundLibrary
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;
    #[ORM\Column(length: 140)]
    private string $name;
    #[ORM\Column(length: 160, unique: true)]
    private string $slug;
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;
    #[ORM\Column(length: 30)]
    private string $status = 'draft';
    #[ORM\Column(length: 30)]
    private string $visibility = 'private';
    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(string $name, string $slug, ?string $description = null)
    {
        $this->name = $name; $this->slug = $slug; $this->description = $description; $this->createdAt = new \DateTimeImmutable();
    }
    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getSlug(): string { return $this->slug; }
    public function getDescription(): ?string { return $this->description; }
    public function getStatus(): string { return $this->status; }
    public function getVisibility(): string { return $this->visibility; }
    public function setName(string $v): void { $this->name = $v; }
    public function setSlug(string $v): void { $this->slug = $v; }
    public function setDescription(?string $v): void { $this->description = $v; }
    public function setStatus(string $v): void { $this->status = $v; }
    public function setVisibility(string $v): void { $this->visibility = $v; }
}
