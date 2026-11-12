<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'audio_licenses')]
class AudioLicense
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;
    #[ORM\Column(length: 120)]
    private string $name;
    #[ORM\Column(length: 100, nullable: true)]
    private ?string $identifier = null;
    #[ORM\Column]
    private bool $allowsRedistribution = false;
    #[ORM\Column]
    private bool $attributionRequired = true;
    #[ORM\Column(length: 500, nullable: true)]
    private ?string $termsUrl = null;

    public function __construct(string $name, bool $allowsRedistribution = false, bool $attributionRequired = true, ?string $identifier = null, ?string $termsUrl = null)
    {
        $this->name = $name; $this->allowsRedistribution = $allowsRedistribution;
        $this->attributionRequired = $attributionRequired; $this->identifier = $identifier; $this->termsUrl = $termsUrl;
    }
    public function getId(): ?int { return $this->id; }
    public function getName(): string { return $this->name; }
    public function getIdentifier(): ?string { return $this->identifier; }
    public function allowsRedistribution(): bool { return $this->allowsRedistribution; }
    public function isAttributionRequired(): bool { return $this->attributionRequired; }
    public function getTermsUrl(): ?string { return $this->termsUrl; }
}
