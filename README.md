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

- **Backend:** PHP 8.3 / Symfony 7.4
- **Database:** PostgreSQL 16
- **Web server:** Nginx
- **Runtime:** PHP-FPM
- **Deployment:** Docker Engine and Docker Compose
- **API:** REST, versioned under `/api/v1`

## Docker quick start

Requirements: Docker Engine and the Docker Compose plugin.

```bash
cp .env.docker.example .env
```

Edit `.env` and replace the placeholder values with strong secrets. Keep `DATABASE_URL` synchronized with the PostgreSQL credentials.

Start the application:

```bash
docker compose up -d --build
```

The API will be available at:

```text
http://localhost:8080/api/v1/health
```

Useful commands:

```bash
docker compose ps
docker compose logs -f
docker compose down
```

See [Docker deployment documentation](docs/docker.md) for server deployment and operational notes.

## Current API endpoint

| Method | Endpoint | Purpose |
| --- | --- | --- |
| GET | `/api/v1/health` | API health check |

## Planned domain

- Instrument families
- Instruments
- Sound libraries
- Presets
- Audio samples
- Audio formats and technical metadata
- Licenses, sources, and attributions
- API clients and access permissions

## Architecture

PostgreSQL stores catalog data and metadata, not audio binaries. Audio assets will be stored in persistent object storage and delivered through controlled URLs. Docker Compose currently provides the application, Nginx, and PostgreSQL services; object storage will be added when the audio asset module is implemented.

## Audio asset principles

Every imported audio asset must record its source, license, attribution requirements, and redistribution permissions. An asset must not be published until its usage rights have been reviewed.

## Language

English is the official language for source code, identifiers, comments, API responses, documentation, and administration terminology.

## License

All rights reserved. Audio assets may have separate licenses and usage restrictions; those terms must be recorded per asset.
