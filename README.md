# Motor Media

Media management package for the Motor CMS framework. Provides file uploads, directory management, and media library functionality.

## Installation

```bash
composer require motor-cms/motor-media
```

## Features

- **File Management** -- Upload, download, and manage media files
- **File Associations** -- Polymorphic links between files and any model
- **Directory Support** -- Organize files in directories
- **Media Sync** -- Sync media from remote servers

## Models

| Model | Description |
|-------|-------------|
| `File` | Media file records (images, documents, etc.) |
| `FileAssociation` | Polymorphic file-to-model links |

## API Endpoints

```
/api/files
/api/files/{id}
```

## Package Structure

```
src/
├── Console/       # Media management commands (sync)
├── Events/        # File operation events
├── Helpers/       # Helper functions
├── Http/          # Controllers, Requests, Resources
├── Models/        # File, FileAssociation
├── Policies/      # Authorization policies
├── Providers/     # Service providers
└── Services/      # FileService
```

## Dependencies

- `motor-cms/motor-core`
- `motor-cms/motor-admin`

## Credits

- [Reza Esmaili](https://github.com/dfox288)
