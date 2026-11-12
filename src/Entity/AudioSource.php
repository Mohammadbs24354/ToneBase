<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'audio_sources')]
class AudioSource
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;
    #[ORM\Column(length: 140)]
    private string $name;
    #[ORM\Column(length: 500, nullable: true)]
    private ?string $sourceUrl = null;
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $attributionText = null;

    public function __construct(string $name, ?string $sourceUrl = null, ?string $attributionText = null)
    {
        $this->name = $name; $this->sourceUrl = $sourceUrl; $this->attributionText = $attributionText;
    }
    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getSourceUrl(): ?string { return $this->sourceUrl; }
    public function getAttributionText(): ?string { return $this->attributionText; }
}
