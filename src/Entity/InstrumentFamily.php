<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'instrument_families')]
class InstrumentFamily
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;
    #[ORM\Column(length: 100)]
    private string $name;
    #[ORM\Column(length: 120, unique: true)]
    private string $slug;
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;
    #[ORM\Column(length: 30)]
    private string $category = 'other';
    #[ORM\Column]
    private bool $isActive = true;
    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(string $name, string $slug, string $category = 'other', ?string $description = null)
    {
        $this->name = $name; $this->slug = $slug; $this->category = $category;
        $this->description = $description; $this->createdAt = new \DateTimeImmutable();
    }
    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getSlug(): string { return $this->slug; }
    public function getDescription(): ?string { return $this->description; }
    public function getCategory(): string { return $this->category; }
    public function isActive(): bool { return $this->isActive; }
    public function setName(string $v): void { $this->name = $v; }
    public function setSlug(string $v): void { $this->slug = $v; }
    public function setDescription(?string $v): void { $this->description = $v; }
    public function setCategory(string $v): void { $this->category = $v; }
    public function setIsActive(bool $v): void { $this->isActive = $v; }
}
