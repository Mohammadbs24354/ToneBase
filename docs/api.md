# ToneBase API

Base path: `/api/v1`

All responses use JSON. Collection responses contain `data` and `meta` with `page`, `limit`, and `total`. Pagination uses `?page=1&limit=25`; maximum limit is 100.

## Public read endpoints

| Method | Endpoint | Description |
| --- | --- | --- |
| GET | `/health` | API liveness |
| GET | `/families` | Active instrument families |
| GET | `/instruments` | Active instruments; optional `familyId` |
| GET | `/libraries` | Published public sound libraries |
| GET | `/presets` | Published presets |
| GET | `/samples` | Published samples; optional `instrumentId` and `libraryId` |

## Admin write endpoints

All admin write endpoints require `Authorization: Bearer <API_ADMIN_TOKEN>`.

| Method | Endpoint | Description |
| --- | --- | --- |
| POST | `/admin/families` | Create an instrument family |
| POST | `/admin/instruments` | Create an instrument |
| POST | `/admin/libraries` | Create a sound library |
| POST | `/admin/presets` | Create a preset |
| POST | `/admin/sources` | Register an audio source |
| POST | `/admin/licenses` | Register an audio license |
| POST | `/admin/uploads` | Upload an audio file as multipart field `file` |
| POST | `/admin/samples` | Register sample metadata using a previously uploaded `storageKey` |
| PATCH | `/admin/samples/{id}/status` | Review, publish, or reject a sample; publishing enforces redistribution and attribution requirements |

## Upload workflow

1. Upload the file to `POST /admin/uploads` as multipart form data.
2. Save the returned `storageKey`, `format`, `fileSizeBytes`, and `checksumSha256`.
3. Register metadata through `POST /admin/samples`, including instrument, library, source, and license IDs.
4. Samples start as `pending_review`; only samples explicitly marked `published` are returned by the public endpoint. Use `PATCH /admin/samples/{id}/status` to review them. Publishing is blocked unless the license permits redistribution and required attribution text is present.

Supported extensions: WAV, FLAC, OGG, MP3, SF2, SFZ. Files are stored outside the public web root. MIDI note mappings and sample metadata describe recorded audio; they are not MIDI event streams.

## Example: create a family

```bash
curl -X POST http://localhost:8080/api/v1/admin/families \
  -H "Authorization: Bearer $API_ADMIN_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"Eastern Strings","slug":"eastern-strings","category":"eastern"}'
```

## Licensing

Every sample requires a source and a license record. Redistribution is not assumed. Review the license and attribution requirements before publishing an asset.
