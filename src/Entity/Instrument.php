<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'instruments')]
class Instrument
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;
    #[ORM\ManyToOne(targetEntity: InstrumentFamily::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private InstrumentFamily $family;
    #[ORM\Column(length: 120)]
    private string $name;
    #[ORM\Column(length: 140, unique: true)]
    private string $slug;
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;
    #[ORM\Column(length: 40)]
    private string $soundType = 'acoustic';
    #[ORM\Column(length: 80, nullable: true)]
    private ?string $defaultTuning = null;
    #[ORM\Column]
    private bool $isActive = true;
    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(InstrumentFamily $family, string $name, string $slug, string $soundType = 'acoustic', ?string $description = null)
    {
        $this->family = $family; $this->name = $name; $this->slug = $slug;
        $this->soundType = $soundType; $this->description = $description; $this->createdAt = new \DateTimeImmutable();
    }
    public function getId(): ?int { return $this->id; }
    public function getFamily(): InstrumentFamily { return $this->family; }
    public function getName(): string { return $this->name; }
    public function getSlug(): string { return $this->slug; }
    public function getDescription(): ?string { return $this->description; }
    public function getSoundType(): string { return $this->soundType; }
    public function getDefaultTuning(): ?string { return $this->defaultTuning; }
    public function isActive(): bool { return $this->isActive; }
    public function setFamily(InstrumentFamily $v): void { $this->family = $v; }
    public function setName(string $v): void { $this->name = $v; }
    public function setSlug(string $v): void { $this->slug = $v; }
    public function setDescription(?string $v): void { $this->description = $v; }
    public function setSoundType(string $v): void { $this->soundType = $v; }
    public function setDefaultTuning(?string $v): void { $this->defaultTuning = $v; }
    public function setIsActive(bool $v): void { $this->isActive = $v; }
}
