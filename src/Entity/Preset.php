<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'presets')]
class Preset
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;
    #[ORM\ManyToOne(targetEntity: Instrument::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Instrument $instrument;
    #[ORM\ManyToOne(targetEntity: SoundLibrary::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private SoundLibrary $library;
    #[ORM\Column(length: 140)]
    private string $name;
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;
    #[ORM\Column(type: 'json')]
    private array $settings = [];
    #[ORM\Column(length: 30)]
    private string $status = 'draft';
    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(Instrument $instrument, SoundLibrary $library, string $name, array $settings = [], ?string $description = null)
    {
        $this->instrument = $instrument; $this->library = $library; $this->name = $name;
        $this->settings = $settings; $this->description = $description; $this->createdAt = new \DateTimeImmutable();
    }
    public function getId(): ?int { return $this->id; }
    public function getInstrument(): Instrument { return $this->instrument; }
    public function getLibrary(): SoundLibrary { return $this->library; }
    public function getName(): string { return $this->name; }
    public function getDescription(): ?string { return $this->description; }
    public function getSettings(): array { return $this->settings; }
    public function getStatus(): string { return $this->status; }
    public function setName(string $v): void { $this->name = $v; }
    public function setDescription(?string $v): void { $this->description = $v; }
    public function setSettings(array $v): void { $this->settings = $v; }
    public function setStatus(string $v): void { $this->status = $v; }
}
