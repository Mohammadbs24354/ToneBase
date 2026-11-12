# ToneBase Architecture

## High-level components

1. **API application:** Symfony 7.4 application exposing versioned REST endpoints.
2. **Relational database:** PostgreSQL stores catalog data, metadata, ownership, and licensing information.
3. **Object storage:** Stores audio binaries such as WAV, FLAC, and MP3 files. The database stores object keys and technical metadata, not binary payloads.
4. **Administration dashboard:** A separate web application for managing instruments, libraries, presets, samples, and publishing status.
5. **API clients:** Maqam Tuner, future Android applications, and other authorized music applications.

## Initial domain model

- **InstrumentFamily:** A broad group such as Eastern, Western, or Electronic.
- **Instrument:** A specific instrument such as Oud, Qanun, Piano, or Electric Guitar.
- **SoundLibrary:** A curated collection of related sounds.
- **Preset:** A playable configuration that may reference one or more samples.
- **AudioSample:** A recorded audio asset with note, octave, velocity, articulation, format, sample rate, root note, and loop metadata where applicable.
- **AudioLicense:** Usage and redistribution terms for an asset.
- **AudioSource:** Origin and attribution details for an asset.

## API conventions

- Base path: /api/v1
- JSON request and response bodies.
- Stable resource identifiers.
- Pagination and filtering for collection endpoints.
- Explicit validation errors.
- Authentication and authorization before private asset management is introduced.

## Audio delivery

Audio files are stored outside PostgreSQL. The API returns metadata and controlled playback/download URLs. Private assets should use short-lived signed URLs or authenticated streaming.

## Important distinction

A musical note or MIDI event is not the same thing as an audio sample. ToneBase is intended to support both structured musical data and real recorded instrument samples, while keeping those concepts separate in the data model.

## Licensing

Every imported audio asset must record its source, license, attribution requirements, and redistribution permissions. An asset must not be published until its usage rights have been reviewed.
