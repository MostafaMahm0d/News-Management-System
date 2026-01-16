# Article Management System - DDD Implementation

A Domain-Driven Design (DDD) implementation for fetching articles from GNews API and storing them in a database with complete value object implementation.

## Architecture Overview

This project follows DDD principles with the following layers:

### 1. **Domain Layer** (`src/Domain/`)
Contains the core business logic and rules:
- **Entity**: `Article` - The main domain entity with full value object integration
- **Value Objects**: 
  - `ArticleId` - Unique identifier
  - `Title` - Article title with validation (max 500 chars)
  - `Description` - Article description with validation
  - `Content` - Article content with validation
  - `Url` - Validated URL format
  - `ImageUrl` - Validated image URL (nullable)
  - `PublishedAt` - Immutable publication date
  - `SourceName` - News source name (max 255 chars)
  - `Language` - Language code validation (23 supported languages)
- **Repository Interface**: `ArticleRepositoryInterface` - Contract for data persistence
- **Exceptions**: Domain-specific exceptions

### 2. **Infrastructure Layer** (`src/Infrastructure/`)
Contains implementation details:
- **Doctrine Entities**: `ArticleEntity` - ORM mapped entity
- **Repositories**: `DoctrineArticleRepository` - Concrete repository implementation with update support
- **External API**: `GNewsApiClient` - HTTP client for GNews API with dedicated logging

### 3. **Application Layer** (`src/Application/`)
Contains use cases and application logic:
- **Use Cases**: 
  - `FetchArticlesUseCase` - Fetch and save articles with pagination
  - `GetArticleListUseCase` - Retrieve articles list
  - `GetArticleByIdUseCase` - Retrieve single article by ID
  - `ResyncArticlesUseCase` - Resync existing articles and update if changed
- **DTOs**: `ArticleDTO` - Data transfer objects

### 4. **Presentation Layer** (`src/Presentation/`)
Contains user interface components:
- **Controllers**: `ArticleController` - REST API endpoints
- **Console Commands**: 
  - `FetchArticlesCommand` - Fetch new articles
  - `ListArticlesCommand` - List articles
  - `ResyncArticlesCommand` - Resync and update existing articles

## Setup Instructions

### 1. Install Dependencies
Dependencies are already installed. If you need to reinstall:
```bash
composer install
```

### 2. Configure Environment Variables
Edit `.env` file and set your GNews API key:
```env
GNEWS_API_KEY=your_actual_api_key_here
```

Get your free API key from: https://gnews.io/

### 3. Start Docker Containers
```bash
docker-compose up -d
```

### 4. Create Database
```bash
docker-compose exec php bin/console doctrine:database:create
```

### 5. Run Migrations
```bash
docker-compose exec php bin/console doctrine:migrations:migrate
```

## Usage

### Fetch Articles from GNews API

**Using Console Command:**
```bash
# Fetch general articles (default) - fetches all available pages
docker-compose exec app php bin/console app:articles:fetch

# Fetch business articles in English
docker-compose exec app php bin/console app:articles:fetch --category=business --lang=en --max=100

# Fetch technology articles
docker-compose exec app php bin/console app:articles:fetch -c technology -m 100
```

**Note:** The system automatically fetches all available pages with a 2-second delay between requests to avoid rate limiting. It stops when an empty page is received.

Available categories: general, world, nation, business, technology, entertainment, sports, science, health

### Resync Articles

Update existing articles if their content has changed:

```bash
# Resync all articles
docker-compose exec app php bin/console app:articles:resync

# Resync specific category
docker-compose exec app php bin/console app:articles:resync --category=technology --lang=en --max=100
```

### List Articles from Database

**Using Console Command:**
```bash
# List 10 latest articles
docker-compose exec app php bin/console app:articles:list

# List with custom limit and offset
docker-compose exec app php bin/console app:articles:list --limit=20 --offset=10
```

### REST API Endpoints

**1. List Articles:**
```bash
# Basic list
GET /api/articles?limit=20&offset=0

# Filter by language
GET /api/articles?language=en&limit=20&offset=0

# Sort by published date (ascending)
GET /api/articles?orderBy=publishedAt&orderDirection=ASC

# Sort by created date (descending)
GET /api/articles?orderBy=createdAt&orderDirection=DESC

# Combined: filter by language and sort
GET /api/articles?language=fr&orderBy=publishedAt&orderDirection=DESC&limit=50
```

**Query Parameters:**
- `limit` (int, default: 20) - Number of articles per page
- `offset` (int, default: 0) - Pagination offset
- `language` (string, optional) - Filter by language code (en, fr, es, etc.)
- `orderBy` (string, default: publishedAt) - Sort field: `publishedAt`, `createdAt`, `updatedAt`, `title`
- `orderDirection` (string, default: DESC) - Sort direction: `ASC` or `DESC`

**2. Show Single Article:**
```bash
GET /api/articles/{id}
```

**3. Fetch Articles:**
```bash
POST /api/articles/fetch
Content-Type: application/json

{
    "category": "technology",
    "lang": "en",
    "max": 100
}
```

**4. Resync Articles:**
```bash
POST /api/articles/resync
Content-Type: application/json

{
    "category": "technology",
    "lang": "en",
    "max": 100
}
```

## Database Schema

The `articles` table has the following structure:
- `id` (string, primary key) - MD5 hash of the URL
- `title` (string, 500 chars)
- `description` (text)
- `content` (text)
- `url` (string, 500 chars, unique)
- `image_url` (text, nullable)
- `published_at` (datetime)
- `source_name` (string, 255 chars)
- `language` (string, 10 chars)
- `created_at` (datetime_immutable) - Auto-generated on creation
- `updated_at` (datetime_immutable) - Auto-updated on modification

Indexes:
- `idx_articles_url` on `url`
- `idx_articles_published_at` on `published_at`
- `idx_articles_language` on `language`

## Project Structure

```
src/
├── Application/
│   └── Article/
│       ├── DTO/
│       │   └── ArticleDTO.php
│       └── UseCase/
│           ├── FetchArticlesUseCase.php
│           ├── GetArticleListUseCase.php
│           ├── GetArticleByIdUseCase.php
│           └── ResyncArticlesUseCase.php
├── Domain/
│   └── Article/
│       ├── Entity/
│       │   └── Article.php
│       ├── Exception/
│       │   └── ArticleNotFoundException.php
│       ├── Repository/
│       │   └── ArticleRepositoryInterface.php
│       └── ValueObject/
│           ├── ArticleId.php
│           ├── Title.php
│           ├── Description.php
│           ├── Content.php
│           ├── Url.php
│           ├── ImageUrl.php
│           ├── PublishedAt.php
│           ├── SourceName.php
│           └── Language.php
├── Infrastructure/
│   ├── ExternalApi/
│   │   └── GNews/
│   │       └── GNewsApiClient.php
│   └── Persistence/
│       └── Doctrine/
│           ├── Entity/
│           │   └── ArticleEntity.php
│           └── Repository/
│               └── DoctrineArticleRepository.php
└── Presentation/
    ├── Console/
    │   ├── FetchArticlesCommand.php
    │   ├── ListArticlesCommand.php
    │   └── ResyncArticlesCommand.php
    └── Controller/
        └── ArticleController.php
```

## DDD Principles Applied

1. **Separation of Concerns**: Each layer has a specific responsibility
2. **Dependency Inversion**: Domain doesn't depend on infrastructure
3. **Repository Pattern**: Abstract data access behind interfaces with update support
4. **Value Objects**: Complete value object implementation for all properties with validation
   - Type safety and immutability
   - Business rules enforcement (URL validation, language codes, length limits)
   - Self-documenting code
5. **Use Cases**: Clear application logic separated from presentation
6. **DTOs**: Transfer data between layers without exposing domain entities
7. **Dedicated Logging**: Separate logger channel for GNews API integration with performance metrics

## Features

- ✅ **Automatic Pagination**: Fetches all available pages from GNews API
- ✅ **Rate Limiting**: 2-second delay between API requests
- ✅ **Duplicate Detection**: Skips articles that already exist in database
- ✅ **Article Resyncing**: Update existing articles when content changes
- ✅ **Advanced Filtering**: Filter articles by language
- ✅ **Flexible Sorting**: Sort by publishedAt, createdAt, updatedAt, or title (ASC/DESC)
- ✅ **Value Object Validation**: Complete type safety and domain validation
- ✅ **Performance Monitoring**: Request duration logging in milliseconds
- ✅ **Multi-language Support**: 23 languages supported with validation
- ✅ **Automatic Timestamps**: created_at and updated_at with lifecycle callbacks
- ✅ **RESTful API**: Complete REST endpoints for all operations
- ✅ **Console Commands**: CLI interface for all operations

## Testing the API

### Using cURL:

**List Articles:**
```bash
# Basic list
curl http://localhost:8080/api/articles?limit=10&offset=0

# Filter by English articles
curl http://localhost:8080/api/articles?language=en&limit=20

# Sort by published date ascending
curl "http://localhost:8080/api/articles?orderBy=publishedAt&orderDirection=ASC"

# Filter French articles, sort by created date
curl "http://localhost:8080/api/articles?language=fr&orderBy=createdAt&orderDirection=DESC&limit=50"
```

**Show Single Article:**
```bash
# Replace {id} with actual article ID (MD5 hash)
curl http://localhost:8080/api/articles/{id}
```

**Fetch Articles:**
```bash
curl -X POST http://localhost:8080/api/articles/fetch \
  -H "Content-Type: application/json" \
  -d '{"category": "technology", "lang": "en", "max": 100}'
```

**Resync Articles:**
```bash
curl -X POST http://localhost:8080/api/articles/resync \
  -H "Content-Type: application/json" \
  -d '{"category": "technology", "lang": "en", "max": 100}'
```

## Logging

The system uses dedicated logging channels:

- **Main Log**: `var/log/dev.log` - General application logs
- **GNews Log**: `var/log/gnews.log` - Dedicated GNews API integration logs
  - Request parameters
  - Response article counts
  - API call duration in milliseconds
  - Error details with context

View logs:
```bash
# View GNews API logs
docker-compose exec app tail -f var/log/gnews.log

# View main application logs
docker-compose exec app tail -f var/log/dev.log
```

## Supported Languages

The system supports 23 languages with validation:
- ar (Arabic), bn (Bengali), zh (Chinese), nl (Dutch), en (English)
- fr (French), de (German), el (Greek), he (Hebrew), hi (Hindi)
- it (Italian), ja (Japanese), ml (Malayalam), mr (Marathi), no (Norwegian)
- pt (Portuguese), ro (Romanian), ru (Russian), es (Spanish), sv (Swedish)
- ta (Tamil), te (Telugu), uk (Ukrainian)

## Development Commands

```bash
# Clear cache
docker-compose exec app php bin/console cache:clear

# Check available commands
docker-compose exec app php bin/console list

# Generate a new migration
docker-compose exec app php bin/console doctrine:migrations:diff

# Run migrations
docker-compose exec app php bin/console doctrine:migrations:migrate

# View Doctrine schema
docker-compose exec app php bin/console doctrine:schema:validate

# View GNews API logs
docker-compose exec app tail -f var/log/gnews.log
```

## Troubleshooting

### Database Connection Issues
Make sure the database container is running:
```bash
docker-compose ps
```

### API Key Issues
Verify your GNews API key is correctly set in `.env` file.

### Cache Issues
If you experience issues, try clearing the cache:
```bash
docker-compose exec app php bin/console cache:clear
```

### Permission Issues
The Docker setup automatically handles permissions for `var/` directory via entrypoint script.

### Rate Limiting
The system includes a 2-second delay between API requests. If you hit rate limits, consider:
- Reducing the `--max` parameter
- Checking your GNews API plan limits
- Reviewing the `var/log/gnews.log` for API errors
