# Motor Media Commands

All commands follow the `motor:media:*` naming convention.

## Media Integrity

| Command | Description |
|---------|-------------|
| `motor:media:check` | Check if media files referenced in the database exist on disk and generate a manifest |
| `motor:media:sync` | Download missing media files from a remote server using a manifest |

`check` generates a JSON manifest in `storage/logs/` listing missing files. `sync` consumes that manifest to restore them from a remote server.

Both commands support `--disk` to override the target disk and `--headless` for non-interactive (cron/CI) usage.

`sync` also accepts `--manifest` to specify a custom manifest path and `--dry-run` to preview without downloading. The remote base URL falls back to `config('client.media_sync_remote_base')` if `--remote-base` is not provided.

## S3 Migration

| Command | Description |
|---------|-------------|
| `motor:media:copy-to-s3` | Copy local media library files to S3 storage |
| `motor:media:delete-local` | Delete local copies of media files after S3 migration |

`copy-to-s3` supports:
- `--type=sdk` (default) or `--type=filesystem` for upload method
- `--count` and `--skip` for batched processing
- `--headless` for non-interactive usage

**Typical S3 migration workflow:**
1. `motor:media:check` — identify current state
2. `motor:media:copy-to-s3` — copy files to S3
3. Verify S3 files are accessible
4. `motor:media:delete-local` — remove local copies
