<?php

namespace App\Controller;

use App\Entity\AudioLicense;
use App\Entity\AudioSample;
use App\Entity\AudioSource;
use App\Entity\Instrument;
use App\Entity\InstrumentFamily;
use App\Entity\Preset;
use App\Entity\SoundLibrary;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/v1')]
final class CatalogController
{
    public function __construct(private readonly EntityManagerInterface $em) {}

    #[Route('/families', methods: ['GET'])]
    public function families(Request $request): JsonResponse
    {
        $items = $this->em->getRepository(InstrumentFamily::class)->findBy(['isActive' => true], ['name' => 'ASC']);
        return $this->collection(array_map(fn (InstrumentFamily $x) => [
            'id' => $x->getId(), 'name' => $x->getName(), 'slug' => $x->getSlug(),
            'description' => $x->getDescription(), 'category' => $x->getCategory(),
        ], $items), $request);
    }

    #[Route('/admin/families', methods: ['POST'])]
    public function createFamily(Request $request): JsonResponse
    {
        $data = $this->payload($request);
        if (!$data || empty($data['name']) || empty($data['slug'])) return $this->error('name and slug are required.', 422);
        $entity = new InstrumentFamily(trim($data['name']), trim($data['slug']), $data['category'] ?? 'other', $data['description'] ?? null);
        $this->em->persist($entity); $this->em->flush();
        return $this->item(['id' => $entity->getId(), 'name' => $entity->getName(), 'slug' => $entity->getSlug()], 201);
    }

    #[Route('/instruments', methods: ['GET'])]
    public function instruments(Request $request): JsonResponse
    {
        $criteria = ['isActive' => true];
        if ($request->query->getInt('familyId') > 0) $criteria['family'] = $this->em->getReference(InstrumentFamily::class, $request->query->getInt('familyId'));
        $items = $this->em->getRepository(Instrument::class)->findBy($criteria, ['name' => 'ASC']);
        return $this->collection(array_map(fn (Instrument $x) => $this->instrumentData($x), $items), $request);
    }

    #[Route('/admin/instruments', methods: ['POST'])]
    public function createInstrument(Request $request): JsonResponse
    {
        $data = $this->payload($request);
        $family = $this->em->getRepository(InstrumentFamily::class)->find((int) ($data['familyId'] ?? 0));
        if (!$family || empty($data['name']) || empty($data['slug'])) return $this->error('A valid familyId, name and slug are required.', 422);
        $entity = new Instrument($family, trim($data['name']), trim($data['slug']), $data['soundType'] ?? 'acoustic', $data['description'] ?? null);
        if (isset($data['defaultTuning'])) $entity->setDefaultTuning($data['defaultTuning']);
        $this->em->persist($entity); $this->em->flush();
        return $this->item($this->instrumentData($entity), 201);
    }

    #[Route('/libraries', methods: ['GET'])]
    public function libraries(Request $request): JsonResponse
    {
        $items = $this->em->getRepository(SoundLibrary::class)->findBy(['status' => 'published', 'visibility' => 'public'], ['name' => 'ASC']);
        return $this->collection(array_map(fn (SoundLibrary $x) => [
            'id' => $x->getId(), 'name' => $x->getName(), 'slug' => $x->getSlug(), 'description' => $x->getDescription(),
        ], $items), $request);
    }

    #[Route('/admin/libraries', methods: ['POST'])]
    public function createLibrary(Request $request): JsonResponse
    {
        $data = $this->payload($request);
        if (!$data || empty($data['name']) || empty($data['slug'])) return $this->error('name and slug are required.', 422);
        $entity = new SoundLibrary(trim($data['name']), trim($data['slug']), $data['description'] ?? null);
        if (isset($data['status'])) $entity->setStatus($data['status']);
        if (isset($data['visibility'])) $entity->setVisibility($data['visibility']);
        $this->em->persist($entity); $this->em->flush();
        return $this->item(['id' => $entity->getId(), 'name' => $entity->getName(), 'slug' => $entity->getSlug()], 201);
    }

    #[Route('/presets', methods: ['GET'])]
    public function presets(Request $request): JsonResponse
    {
        $items = $this->em->getRepository(Preset::class)->findBy(['status' => 'published'], ['name' => 'ASC']);
        return $this->collection(array_map(fn (Preset $x) => [
            'id' => $x->getId(), 'name' => $x->getName(), 'description' => $x->getDescription(),
            'instrumentId' => $x->getInstrument()->getId(), 'libraryId' => $x->getLibrary()->getId(), 'settings' => $x->getSettings(),
        ], $items), $request);
    }

    #[Route('/admin/presets', methods: ['POST'])]
    public function createPreset(Request $request): JsonResponse
    {
        $data = $this->payload($request);
        $instrument = $this->em->getRepository(Instrument::class)->find((int) ($data['instrumentId'] ?? 0));
        $library = $this->em->getRepository(SoundLibrary::class)->find((int) ($data['libraryId'] ?? 0));
        if (!$instrument || !$library || empty($data['name'])) return $this->error('instrumentId, libraryId and name are required.', 422);
        $entity = new Preset($instrument, $library, trim($data['name']), $data['settings'] ?? [], $data['description'] ?? null);
        if (isset($data['status'])) $entity->setStatus($data['status']);
        $this->em->persist($entity); $this->em->flush();
        return $this->item(['id' => $entity->getId(), 'name' => $entity->getName()], 201);
    }

    #[Route('/samples', methods: ['GET'])]
    public function samples(Request $request): JsonResponse
    {
        $criteria = ['status' => 'published'];
        if ($request->query->getInt('instrumentId') > 0) $criteria['instrument'] = $this->em->getReference(Instrument::class, $request->query->getInt('instrumentId'));
        if ($request->query->getInt('libraryId') > 0) $criteria['library'] = $this->em->getReference(SoundLibrary::class, $request->query->getInt('libraryId'));
        $items = $this->em->getRepository(AudioSample::class)->findBy($criteria, ['name' => 'ASC']);
        return $this->collection(array_map(fn (AudioSample $x) => [
            'id' => $x->getId(), 'name' => $x->getName(), 'instrumentId' => $x->getInstrument()->getId(),
            'libraryId' => $x->getLibrary()->getId(), 'format' => $x->getFormat(),
            'fileSizeBytes' => $x->getFileSizeBytes(), 'midiNote' => $x->getMidiNote(), 'octave' => $x->getOctave(),
            'velocityMin' => $x->getVelocityMin(), 'velocityMax' => $x->getVelocityMax(),
            'rootNote' => $x->getRootNote(), 'sampleRate' => $x->getSampleRate(), 'durationMs' => $x->getDurationMs(),
            'articulation' => $x->getArticulation(),
        ], $items), $request);
    }

    #[Route('/admin/sources', methods: ['POST'])]
    public function createSource(Request $request): JsonResponse
    {
        $data = $this->payload($request);
        if (!$data || empty($data['name'])) return $this->error('name is required.', 422);
        $entity = new AudioSource($data['name'], $data['sourceUrl'] ?? null, $data['attributionText'] ?? null);
        $this->em->persist($entity); $this->em->flush();
        return $this->item(['id' => $entity->getId(), 'name' => $entity->getName()], 201);
    }

    #[Route('/admin/licenses', methods: ['POST'])]
    public function createLicense(Request $request): JsonResponse
    {
        $data = $this->payload($request);
        if (!$data || empty($data['name'])) return $this->error('name is required.', 422);
        $entity = new AudioLicense($data['name'], (bool) ($data['allowsRedistribution'] ?? false), (bool) ($data['attributionRequired'] ?? true), $data['identifier'] ?? null, $data['termsUrl'] ?? null);
        $this->em->persist($entity); $this->em->flush();
        return $this->item(['id' => $entity->getId(), 'name' => $entity->getName()], 201);
    }

    #[Route('/admin/samples', methods: ['POST'])]
    public function registerSample(Request $request): JsonResponse
    {
        $data = $this->payload($request);
        $instrument = $this->em->getRepository(Instrument::class)->find((int) ($data['instrumentId'] ?? 0));
        $library = $this->em->getRepository(SoundLibrary::class)->find((int) ($data['libraryId'] ?? 0));
        $license = $this->em->getRepository(AudioLicense::class)->find((int) ($data['licenseId'] ?? 0));
        $source = $this->em->getRepository(AudioSource::class)->find((int) ($data['sourceId'] ?? 0));
        foreach ([$instrument, $library, $license, $source] as $required) if (!$required) return $this->error('Valid instrumentId, libraryId, licenseId and sourceId are required.', 422);
        foreach (['name', 'storageKey', 'format', 'checksumSha256'] as $key) if (empty($data[$key])) return $this->error($key . ' is required.', 422);
        if (!preg_match('/^[a-f0-9]{64}$/i', $data['checksumSha256'])) return $this->error('checksumSha256 must be a SHA-256 hex digest.', 422);
        if (!in_array(strtolower($data['format']), ['wav', 'flac', 'ogg', 'mp3', 'sf2', 'sfz'], true)) return $this->error('Unsupported audio format.', 422);
        $entity = new AudioSample($instrument, $library, $license, $source, $data['name'], $data['storageKey'], $data['format'], (int) ($data['fileSizeBytes'] ?? 0), $data['checksumSha256']);
        $entity->setMidiMapping($data['midiNote'] ?? null, $data['octave'] ?? null, $data['velocityMin'] ?? null, $data['velocityMax'] ?? null, $data['rootNote'] ?? null);
        $entity->setTechnicalMetadata($data['sampleRate'] ?? null, $data['durationMs'] ?? null, $data['loopStart'] ?? null, $data['loopEnd'] ?? null, $data['articulation'] ?? null);
        $this->em->persist($entity); $this->em->flush();
        return $this->item(['id' => $entity->getId(), 'name' => $entity->getName(), 'status' => $entity->getStatus()], 201);
    }

    private function payload(Request $request): array
    {
        try { $data = $request->toArray(); return is_array($data) ? $data : []; }
        catch (\Throwable) { return []; }
    }

    private function instrumentData(Instrument $x): array
    {
        return ['id' => $x->getId(), 'name' => $x->getName(), 'slug' => $x->getSlug(), 'description' => $x->getDescription(),
            'family' => ['id' => $x->getFamily()->getId(), 'name' => $x->getFamily()->getName(), 'slug' => $x->getFamily()->getSlug()],
            'soundType' => $x->getSoundType(), 'defaultTuning' => $x->getDefaultTuning()];
    }

    private function collection(array $items, Request $request): JsonResponse
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = min(100, max(1, $request->query->getInt('limit', 25)));
        $offset = ($page - 1) * $limit;
        return new JsonResponse(['data' => array_slice($items, $offset, $limit), 'meta' => ['page' => $page, 'limit' => $limit, 'total' => count($items)]]);
    }

    private function item(array $data, int $status = 200): JsonResponse { return new JsonResponse(['data' => $data], $status); }
    private function error(string $message, int $status): JsonResponse { return new JsonResponse(['error' => $message], $status); }
}
