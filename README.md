# ToneBase

**ToneBase** is an API-first platform for organizing, storing, and delivering musical instrument sounds and audio sample libraries.

The project is designed to support Eastern, Western, acoustic, and electronic instruments. Applications such as Maqam Tuner, future Android clients, and music software will consume the library through a versioned REST API.

## Project goals

- Build a structured catalog of instrument families and instruments.
- Manage sound libraries, presets, and individual audio samples.
- Store audio files separately from relational metadata.
- Expose a stable, versioned REST API.
- Provide an administration interface for catalog and asset management.
- Track source, license, and attribution information for every audio asset.

## Technology

- **Backend:** PHP 8.2+ / Symfony 7.4
- **Database:** PostgreSQL (planned)
- **Audio storage:** S3-compatible object storage (planned)
- **API:** REST, versioned under `/api/v1`
- **Local infrastructure:** Docker Compose (planned)

## Current status

The initial Symfony application foundation is being established.

Available endpoint:

| Method | Endpoint | Purpose |
| --- | --- | --- |
| GET | `/api/v1/health` | API health check |

## Getting started

Requirements: PHP 8.2+, Composer, and Symfony CLI (optional).

```bash
composer install
cp .env.example .env
php -S 127.0.0.1:8000 -t public
```

Check the API:

```bash
curl http://127.0.0.1:8000/api/v1/health
```

## Core domain (planned)

- Instrument families
- Instruments
- Sound libraries
- Presets
- Audio samples
- Audio formats and technical metadata
- Licenses, sources, and attributions
- API clients and access permissions

## Audio asset principles

Audio binaries will not be stored directly in PostgreSQL. The database will hold metadata and storage keys, while the actual files will live in object storage. Each asset must retain its source and license information so redistribution rights can be checked.

## Language

English is the official language for source code, identifiers, comments, API responses, documentation, and administration terminology.

## License

All rights reserved. Audio assets may have separate licenses and usage restrictions; those terms must be recorded per asset.
