# Arquitectura — Facturify Inbox API

## Visión general

La API sigue el patrón **MVC de Laravel** con responsabilidades separadas para mantener controladores delgados y reglas reutilizables.

```
Cliente (Postman / frontend)
        │
        ▼
 routes/api.php  ── auth:api (JWT)
        │
        ▼
 Controllers/Api  ── Form Requests ── Actions
        │                │              │
        │                ▼              ▼
        │         validación      transacciones / reglas
        │
        ▼
 API Resources  →  ApiResponse (JSON uniforme)
        │
        ▼
 Models / Eloquent  →  MySQL
```

## Capas

| Capa | Ubicación | Responsabilidad |
|---|---|---|
| Rutas | `routes/api.php` | Endpoints, middleware `auth:api` |
| Controladores | `app/Http/Controllers/Api` | Orquestar request → action/resource |
| Form Requests | `app/Http/Requests/Api` | Validación de entrada |
| Actions | `app/Actions` | Casos de uso con transacciones |
| Models | `app/Models` | Persistencia y relaciones |
| Resources | `app/Http/Resources/Api` | Forma de salida JSON |
| Responses | `app/Http/Responses/ApiResponse` | Contrato `{success, message, data\|errors}` |
| Exceptions | `app/Exceptions` | Renderizado uniforme para `api/*` |

## Autenticación

- Guard `api` con driver **JWT** (`tymon/jwt-auth`).
- Login público: `POST /api/auth/login`.
- Resto de rutas: header `Authorization: Bearer {token}`.
- `User` implementa `JWTSubject`.

## Dominio de mensajería

```
User 1 ──crea──> N Thread 1 ──contiene──> N Message
User 1 ──escribe──> N Message
```

- `Thread`: asunto, creador (`created_by`), `status`, `last_message_at`.
- `Message`: cuerpo, autor, `is_read` / `read_at`.
- Al crear un hilo se registra el **primer mensaje** en la misma transacción.
- Responder un hilo cerrado lanza `CannotReplyToClosedThreadException` (`422`).

## Flujo de errores

1. Excepción o validación ocurre.
2. `ApiExceptionRenderer` (registrado en `bootstrap/app.php`) detecta rutas `api/*`.
3. Responde con el contrato estándar vía `ApiResponse::error()`.
4. Excepciones de dominio con `render()` propio (p. ej. hilo cerrado) se respetan.

## Pruebas

Feature tests con Pest por módulo (`tests/Feature/Api/*`), usando factories y `RefreshDatabase`. La cobertura se fue agregando **por fase**, no en un bloque final.

## Documentación de fases

El desarrollo incremental está documentado en:

- [`resources/prueba/FASES_PRUEBA.md`](../resources/prueba/FASES_PRUEBA.md)
- [`resources/prueba/DEVELOPMENT_RULES.md`](../resources/prueba/DEVELOPMENT_RULES.md)
