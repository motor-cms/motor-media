# AGENT.md -- motor-media

Instructions for AI agents working on this package.

## Purpose

Media management package providing file uploads, directories, file associations, and media library functionality. Relatively simple compared to other packages.

## Models

| Model | Purpose | Key Relationships |
|-------|---------|-------------------|
| `File` | Media file records (images, documents, etc.) | associations, categories, tags, media (Spatie), client |
| `FileAssociation` | Links files to other models (polymorphic) | file, model (morphTo) |

### File Model Details

- Uses **Spatie MediaLibrary** (`InteractsWithMedia`) for physical file storage and conversions
- Auto-generates `thumb` (400x400) and `preview` (1920x1080) conversions
- Special handling for GIFs (no resizing) and videos (frame extraction at 10s)
- Supports `HasTags` (Spatie Tags) for file tagging
- Scout search index: `motor_media_files_index`

## Controllers (API)

```
src/Http/Controllers/Api/
├── FilesController.php     # File CRUD, upload, download
└── V2/                     # V2 versioned controllers
```

## Services

| Service | Purpose |
|---------|---------|
| `FileService` | File CRUD, upload handling, file processing |

## Events

| Event | Trigger | Side Effect |
|-------|---------|-------------|
| `FileUploaded` | After file create/update with metadata changes | Runs `motor:builder:file-update` to sync metadata to builder pages |
| `FileDeleted` | Before file deletion | Runs `motor:builder:file-delete` to remove references from builder pages |

## Console Commands

| Command | Purpose |
|---------|---------|
| `motor:media:copy-to-s3` | Bulk copy local media to S3 |
| `motor:media:delete-local` | Clean up local files after S3 migration |
| `motor:media:check` | Verify file integrity |
| `motor:media:sync` | Sync media between storage locations |

## File Upload Flow

1. Client sends base64 data URL to `POST /api/files` (format: `{ files: [{ dataUrl, name }] }`)
2. `FilesController` validates via `FilePostRequest`
3. `FileService::create()` handles storage via Spatie MediaLibrary
4. Auto-generates `thumb` and `preview` conversions
5. Syncs categories and tags
6. Optionally copies to S3 via `S3Helper`
7. Updates Scout search index

## Route Convention

**snake_case** for routes:

```
/api/files
/api/files/{id}
```

## Policies

`src/Policies/` -- authorization for file operations.

## Adding a New Media Feature

1. If adding a new model, follow standard Motor pattern (Model, Service, Controller, Requests, Resources)
2. File associations use polymorphic relationships -- use `FileAssociation` model
3. Storage configuration is in `config/filesystems.php` (app-level)

## V2 Endpoints

```
GET    /api/v2/files                # List files (paginated, filterable)
POST   /api/v2/files                # Upload file(s)
GET    /api/v2/files/{id}           # Show file
PATCH  /api/v2/files/{id}           # Update metadata
DELETE /api/v2/files/{id}           # Delete file
GET    /api/v2/files/{id}/usage     # Show where file is used in builder pages
```

The `/usage` endpoint queries `FileAssociation` to find all builder pages referencing the file, deduplicates by UUID across revisions, and returns block types.

## Architecture Notes

- Depends on motor-core and motor-admin
- motor-builder depends on this package for page media management
- Uses **Spatie MediaLibrary** for physical file storage and conversions
- Storage disks: `media` (local) and `media-s3` (AWS S3) -- configurable
- `FileAssociation` enables any model in any package to link to media files (polymorphic)
- `S3Helper` (`src/Helpers/S3Helper.php`) handles S3 uploads and URL generation
- The `FileService` handles both metadata (database) and physical file operations

## Testing

Run with: `./vendor/bin/pest --filter Motor\\Media`
