# Docker Deployment

ToneBase is designed to run as a Docker Compose application.

## Services

- **web:** Nginx reverse proxy and static public files.
- **app:** Symfony application running on PHP-FPM.
- **database:** PostgreSQL 16 with persistent storage.

The PostgreSQL service is only reachable on the private Docker network. Do not publish its port on a public server.

## Requirements

- Docker Engine
- Docker Compose plugin

## First-time setup

1. Clone the repository.
2. Create the environment file:

   ```bash
   cp .env.docker.example .env
   ```

3. Edit `.env` and set unique, strong values for `APP_SECRET` and `POSTGRES_PASSWORD`. Keep the database password in `DATABASE_URL` synchronized with `POSTGRES_PASSWORD`.
4. Start the stack:

   ```bash
   docker compose up -d --build
   ```

5. Check service status:

   ```bash
   docker compose ps
   docker compose logs -f
   ```

6. Test the API:

   ```bash
   curl http://localhost:8080/api/v1/health
   ```

## Common operations

Rebuild after code changes:

```bash
docker compose up -d --build
```

View logs:

```bash
docker compose logs -f app web
```

Stop containers without deleting data:

```bash
docker compose down
```

Stop containers and delete the database volume (destructive):

```bash
docker compose down -v
```

## Production notes

- Put a host-level reverse proxy (for example, your server's existing proxy) in front of the web service and configure HTTPS there.
- Set `HTTP_PORT` to an available local port and bind it only behind the host proxy/firewall where possible.
- Never commit the real `.env` file.
- Back up the `tonebase_database` volume regularly.
- Audio files will use a separate persistent object-storage service when the audio asset module is implemented.
- Database migrations and Doctrine integration will be added with the database domain implementation.
