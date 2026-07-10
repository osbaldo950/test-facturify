# Documentación API

## Archivos

| Archivo | Uso |
|---|---|
| [`openapi.yaml`](./openapi.yaml) | Especificación OpenAPI 3 (Swagger) |
| [`ARCHITECTURE.md`](./ARCHITECTURE.md) | Arquitectura de la aplicación |
| [`TECHNICAL_DECISIONS.md`](./TECHNICAL_DECISIONS.md) | Decisiones técnicas y uso de IA |
| [`postman/Facturify_Inbox_API.postman_collection.json`](./postman/Facturify_Inbox_API.postman_collection.json) | Colección Postman |

## Ver Swagger UI

Opción rápida online:

1. Abre [https://editor.swagger.io](https://editor.swagger.io)
2. File → Import file → selecciona `docs/openapi.yaml`

O con Docker:

```bash
docker run -p 8080:8080 -e SWAGGER_JSON=/openapi.yaml -v "%cd%/docs/openapi.yaml:/openapi.yaml" swaggerapi/swagger-ui
```

Luego visita `http://localhost:8080`.

## Importar Postman

1. Postman → Import → elige `docs/postman/Facturify_Inbox_API.postman_collection.json`
2. Ejecuta **Auth → Login** (guarda `access_token` solo)
3. Usa el resto de requests

Variable útil: `base_url` = `http://localhost:8000/api`
