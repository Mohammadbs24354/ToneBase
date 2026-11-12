<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'audio_samples')]
#[ORM\Index(name: 'idx_audio_sample_status', columns: ['status'])]
class AudioSample
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;
    #[ORM\ManyToOne(targetEntity: Instrument::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Instrument $instrument;
    #[ORM\ManyToOne(targetEntity: SoundLibrary::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private SoundLibrary $library;
    #[ORM\ManyToOne(targetEntity: Preset::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Preset $preset = null;
    #[ORM\ManyToOne(targetEntity: AudioLicense::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private AudioLicense $license;
    #[ORM\ManyToOne(targetEntity: AudioSource::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private AudioSource $source;
    #[ORM\Column(length: 180)]
    private string $name;
    #[ORM\Column(length: 500, unique: true)]
    private string $storageKey;
    #[ORM\Column(length: 12)]
    private string $format;
    #[ORM\Column(type: 'bigint')]
    private string $fileSizeBytes;
    #[ORM\Column(length: 64)]
    private string $checksumSha256;
    #[ORM\Column(nullable: true)]
    private ?int $midiNote = null;
    #[ORM\Column(nullable: true)]
    private ?int $octave = null;
    #[ORM\Column(nullable: true)]
    private ?int $velocityMin = null;
    #[ORM\Column(nullable: true)]
    private ?int $velocityMax = null;
    #[ORM\Column(nullable: true)]
    private ?int $rootNote = null;
    #[ORM\Column(nullable: true)]
    private ?int $sampleRate = null;
    #[ORM\Column(nullable: true)]
    private ?int $durationMs = null;
    #[ORM\Column(nullable: true)]
    private ?int $loopStart = null;
    #[ORM\Column(nullable: true)]
    private ?int $loopEnd = null;
    #[ORM\Column(length: 80, nullable: true)]
    private ?string $articulation = null;
    #[ORM\Column(length: 30)]
    private string $status = 'pending_review';
    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function __construct(Instrument $instrument, SoundLibrary $library, AudioLicense $license, AudioSource $source, string $name, string $storageKey, string $format, int $fileSizeBytes, string $checksumSha256)
    {
        $this->instrument = $instrument; $this->library = $library; $this->license = $license; $this->source = $source;
        $this->name = $name; $this->storageKey = $storageKey; $this->format = strtolower($format);
        $this->fileSizeBytes = (string) $fileSizeBytes; $this->checksumSha256 = strtolower($checksumSha256); $this->createdAt = new \DateTimeImmutable();
    }
    public function getId(): ?int { return $this->id; }
    public function getInstrument(): Instrument { return $this->instrument; }
    public function getLibrary(): SoundLibrary { return $this->library; }
    public function getPreset(): ?Preset { return $this->preset; }
    public function getLicense(): AudioLicense { return $this->license; }
    public function getSource(): AudioSource { return $this->source; }
    public function getName(): string { return $this->name; }
    public function getStorageKey(): string { return $this->storageKey; }
    public function getFormat(): string { return $this->format; }
    public function getFileSizeBytes(): string { return $this->fileSizeBytes; }
    public function getChecksumSha256(): string { return $this->checksumSha256; }
    public function getMidiNote(): ?int { return $this->midiNote; }
    public function getOctave(): ?int { return $this->octave; }
    public function getVelocityMin(): ?int { return $this->velocityMin; }
    public function getVelocityMax(): ?int { return $this->velocityMax; }
    public function getRootNote(): ?int { return $this->rootNote; }
    public function getSampleRate(): ?int { return $this->sampleRate; }
    public function getDurationMs(): ?int { return $this->durationMs; }
    public function getLoopStart(): ?int { return $this->loopStart; }
    public function getLoopEnd(): ?int { return $this->loopEnd; }
    public function getArticulation(): ?string { return $this->articulation; }
    public function getStatus(): string { return $this->status; }
    public function setPreset(?Preset $v): void { $this->preset = $v; }
    public function setStatus(string $v): void { $this->status = $v; }
    public function setMidiMapping(?int $note, ?int $octave, ?int $velocityMin, ?int $velocityMax, ?int $rootNote): void
    {
        $this->midiNote = $note; $this->octave = $octave; $this->velocityMin = $velocityMin;
        $this->velocityMax = $velocityMax; $this->rootNote = $rootNote;
    }
    public function setTechnicalMetadata(?int $sampleRate, ?int $durationMs, ?int $loopStart, ?int $loopEnd, ?string $articulation): void
    {
        $this->sampleRate = $sampleRate; $this->durationMs = $durationMs; $this->loopStart = $loopStart;
        $this->loopEnd = $loopEnd; $this->articulation = $articulation;
    }
}
