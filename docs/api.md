# ToneBase API

Base path: `/api/v1`

All responses use JSON. Collection responses contain `data` and `meta` with `page`, `limit`, and `total`. Pagination uses `?page=1&limit=25`; maximum limit is 100.

## Public read endpoints

| Method | Endpoint | Description |
| --- | --- | --- |
| GET | `/health` | API liveness |
| GET | `/health/ready` | Database readiness check |
| GET | `/families` | Active instrument families |
| GET | `/instruments` | Active instruments; optional `familyId` |
| GET | `/libraries` | Published public sound libraries |
| GET | `/presets` | Published presets |
| GET | `/samples` | Published samples in public libraries; optional `instrumentId` and `libraryId` |
| GET | `/samples/{id}/audio` | Stream a published audio asset |

## Admin endpoints

All `/admin/*` endpoints, including GET requests, require `Authorization: Bearer <API_ADMIN_TOKEN>`.

| Method | Endpoint | Description |
| --- | --- | --- |
| POST | `/admin/families` | Create an instrument family |
| POST | `/admin/instruments` | Create an instrument |
| GET | `/admin/libraries` | List all libraries, including drafts and private libraries |
| POST | `/admin/libraries` | Create a sound library |
| GET | `/admin/presets` | List all presets |
| POST | `/admin/presets` | Create a preset |
| GET | `/admin/sources` | List registered audio sources |
| POST | `/admin/sources` | Register an audio source |
| GET | `/admin/licenses` | List registered audio licenses |
| POST | `/admin/licenses` | Register an audio license |
| POST | `/admin/uploads` | Upload an audio file as multipart field `file` |
| GET | `/admin/samples` | List samples in all review states |
| POST | `/admin/samples` | Register sample metadata using a previously uploaded `storageKey` |
| PATCH | `/admin/samples/{id}/status` | Review, publish, or reject a sample; publishing enforces redistribution and attribution requirements |

## Upload workflow

1. Upload the file to `POST /admin/uploads` as multipart form data.
2. Save the returned `storageKey`, `format`, `fileSizeBytes`, and `checksumSha256`.
3. Register metadata through `POST /admin/samples`, including instrument, library, source, and license IDs.
4. Samples start as `pending_review`; only samples explicitly marked `published` are returned by the public endpoint. Use `PATCH /admin/samples/{id}/status` to review them. Publishing is blocked unless the license permits redistribution and required attribution text is present.

Supported extensions: WAV, FLAC, OGG, MP3, SF2, SFZ. Upload size is limited to 100 MB. Published sample records include an `audioUrl` for playback/download. Files are stored outside the public web root. MIDI note mappings and sample metadata describe recorded audio; they are not MIDI event streams.

## Example: create a family

```bash
curl -X POST http://localhost:8080/api/v1/admin/families \
  -H "Authorization: Bearer $API_ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"Eastern Strings","slug":"eastern-strings","category":"eastern"}'
```

## Licensing

Every sample requires a source and a license record. Redistribution is not assumed. Review the license and attribution requirements before publishing an asset.
