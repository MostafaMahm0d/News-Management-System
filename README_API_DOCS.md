# NelmioApiDocBundle Setup Guide

## Overview

NelmioApiDocBundle has been successfully configured for your News Management System API. This bundle generates OpenAPI (Swagger) documentation for your REST API.

## Installation

The following packages have been added to `composer.json`:

```json
{
  "require": {
    "nelmio/api-doc-bundle": "^5.9",
    "symfony/asset": "7.4.*",
    "symfony/property-info": "7.4.*",
    "symfony/serializer": "7.4.*",
    "symfony/twig-bundle": "7.4.*",
    "symfony/validator": "7.4.*",
    "twig/twig": "^3.0"
  }
}
```

To install these dependencies, run:

```bash
composer install
```

## Configuration Files

### 1. Bundle Registration
The bundle has been registered in `config/bundles.php`:

```php
Nelmio\ApiDocBundle\NelmioApiDocBundle::class => ['all' => true],
```

### 2. Bundle Configuration
Configuration file created at `config/packages/nelmio_api_doc.yaml`:

```yaml
nelmio_api_doc:
    documentation:
        info:
            title: News Management System API
            description: API documentation for the News Management System
            version: 1.0.0
        paths:
            /api: ~
        components:
            securitySchemes:
                Bearer:
                    type: http
                    scheme: bearer
                    bearerFormat: JWT
    areas:
        path_patterns:
            - ^/api(?!/doc$)
```

### 3. Routes Configuration
API documentation routes added to `config/routes.yaml`:

```yaml
# NelmioApiDocBundle routes
app.swagger_ui:
    path: /api/doc
    methods: GET
    defaults: { _controller: nelmio_api_doc.controller.swagger_ui }

app.swagger:
    path: /api/doc.json
    methods: GET
    defaults: { _controller: nelmio_api_doc.controller.swagger }
```

## Accessing the Documentation

After running `composer install`, you can access:

- **Swagger UI**: http://localhost:8080/api/doc
- **OpenAPI JSON**: http://localhost:8080/api/doc.json

## Using OpenAPI Annotations

The `ArticleController` has been updated with OpenAPI attributes as an example:

```php
use OpenApi\Attributes as OA;

#[Route('/api/articles', name: 'api_articles_')]
#[OA\Tag(name: 'Articles')]
class ArticleController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    #[OA\Get(
        path: '/api/articles',
        summary: 'Get list of articles',
        description: 'Returns a paginated list of articles with filtering and sorting options'
    )]
    #[OA\Parameter(
        name: 'limit',
        in: 'query',
        description: 'Number of articles to return',
        required: false,
        schema: new OA\Schema(type: 'integer', default: 20)
    )]
    #[OA\Response(
        response: 200,
        description: 'Successful operation',
        content: new OA\JsonContent(...)
    )]
    public function list(Request $request, GetArticleListUseCase $getArticleListUseCase): JsonResponse
    {
        // ...
    }
}
```

## Available Annotations

### Common Annotations:
- `#[OA\Tag()]` - Group endpoints by tags
- `#[OA\Get()]`, `#[OA\Post()]`, `#[OA\Put()]`, `#[OA\Delete()]` - HTTP methods
- `#[OA\Parameter()]` - Request parameters (query, path, header)
- `#[OA\RequestBody()]` - Request body schema
- `#[OA\Response()]` - Response schemas
- `#[OA\Schema()]` - Data models
- `#[OA\Property()]` - Object properties

### Example for POST Endpoint:

```php
#[Route('', name: 'create', methods: ['POST'])]
#[OA\Post(
    path: '/api/articles',
    summary: 'Create a new article',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            properties: [
                new OA\Property(property: 'title', type: 'string'),
                new OA\Property(property: 'content', type: 'string'),
                new OA\Property(property: 'language', type: 'string')
            ]
        )
    )
)]
#[OA\Response(
    response: 201,
    description: 'Article created successfully'
)]
public function create(Request $request): JsonResponse
{
    // Implementation
}
```

## Security Configuration

If you want to add authentication to your API documentation, the Bearer token security scheme is already configured. You can add it to specific endpoints:

```php
#[OA\Get(
    path: '/api/articles',
    security: [['Bearer' => []]]
)]
```

## Clearing Cache

After making changes to annotations, clear the cache:

```bash
bin/console cache:clear
```

## Customization

You can further customize the documentation in `config/packages/nelmio_api_doc.yaml`:

- Change API title, description, and version
- Add contact information
- Configure multiple documentation areas
- Add global security schemes
- Customize path patterns

## Next Steps

1. Run `composer install` to install all dependencies
2. Clear cache: `bin/console cache:clear`
3. Access http://localhost:8080/api/doc to view your API documentation
4. Add OpenAPI annotations to other controllers as needed
5. Consider adding request/response DTOs with proper schema annotations

## Troubleshooting

If you encounter issues:

1. Ensure all dependencies are installed: `composer install`
2. Clear cache: `bin/console cache:clear`
3. Check that routes are properly loaded: `bin/console debug:router`
4. Verify bundle is registered: `bin/console debug:container nelmio_api_doc`

## Resources

- [NelmioApiDocBundle Documentation](https://symfony.com/bundles/NelmioApiDocBundle/current/index.html)
- [OpenAPI Specification](https://swagger.io/specification/)
- [PHP Attributes (OpenAPI)](https://github.com/zircote/swagger-php)
