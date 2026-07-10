# Facturify Inbox API

API REST de mensajería tipo Inbox (conversaciones / tickets) construida con **Laravel 13** y autenticación **JWT**.

Prueba técnica Back-End: autenticar usuarios, listar conversaciones, ver hilos, crear/responder mensajes y consultar no leídos.

---

## Requisitos

| Requisito | Versión |
|---|---|
| PHP | 8.3+ |
| Composer | 2.x |
| MySQL | 8.x |
| Extensiones PHP | `openssl`, `pdo_mysql`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath` |

---

## Instalación

```bash
git clone <repo>
cd test-facturify

composer install

cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

Configura MySQL en `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=facturify_inbox
DB_USERNAME=root
DB_PASSWORD=
```

Crea la base y carga el esquema + datos de ejemplo:

```bash
# Crear la BD (ejemplo MySQL)
mysql -u root -e "CREATE DATABASE IF NOT EXISTS facturify_inbox CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

php artisan migrate --seed
php artisan serve
```

API base: `http://localhost:8000/api`

---

## Variables de entorno relevantes

| Variable | Descripción |
|---|---|
| `APP_URL` | URL base de la app |
| `DB_*` | Conexión MySQL |
| `JWT_SECRET` | Clave para firmar tokens (`php artisan jwt:secret`) |
| `JWT_TTL` | Minutos de vida del access token (default `60`) |
| `JWT_REFRESH_TTL` | Minutos para refresh (default `20160`) |

Detalle completo en [`.env.example`](.env.example).

---

## Usuarios de prueba (seeder)

Tras `php artisan migrate --seed`:

| Email | Password |
|---|---|
| `test@example.com` | `password` |
| `agent@example.com` | `password` |
| `customer@example.com` | `password` |

También se crean conversaciones y mensajes de ejemplo.

---

## Endpoints

Todas las rutas (excepto login) requieren:

```http
Authorization: Bearer {access_token}
Accept: application/json
```

| Método | Ruta | Descripción |
|---|---|---|
| `POST` | `/api/auth/login` | Login → JWT |
| `GET` | `/api/auth/me` | Usuario autenticado |
| `GET` | `/api/threads` | Listado (`search`, `status`, `per_page`, `page`) |
| `GET` | `/api/threads/{id}` | Detalle + mensajes |
| `POST` | `/api/threads` | Crear hilo + primer mensaje |
| `POST` | `/api/threads/{id}/messages` | Responder hilo |
| `GET` | `/api/notifications/unread` | Mensajes no leídos |

### Ejemplos rápidos

**Login**

```http
POST /api/auth/login
Content-Type: application/json

{
  "email": "test@example.com",
  "password": "password"
}
```

**Crear conversación**

```http
POST /api/threads
Authorization: Bearer {token}

{
  "subject": "Consulta de facturación",
  "body": "Tengo un problema con mi factura"
}
```

**Responder**

```http
POST /api/threads/1/messages
Authorization: Bearer {token}

{
  "body": "Esta es mi respuesta"
}
```

Especificación completa: [`docs/openapi.yaml`](docs/openapi.yaml)  
Colección Postman: [`docs/postman/Facturify_Inbox_API.postman_collection.json`](docs/postman/Facturify_Inbox_API.postman_collection.json)

---

## Formato de respuestas

**Éxito**

```json
{
  "success": true,
  "message": "Descripción del resultado",
  "data": {}
}
```

**Error**

```json
{
  "success": false,
  "message": "Descripción del error",
  "errors": {}
}
```

| Código | Uso |
|---|---|
| `200` | Consulta OK |
| `201` | Recurso creado |
| `401` | No autenticado |
| `403` | Sin permisos |
| `404` | No encontrado |
| `422` | Validación / regla de negocio |
| `500` | Error interno |

---

## Pruebas

```bash
# Toda la suite API
php artisan test --compact tests/Feature/Api/

# Por área
php artisan test --compact tests/Feature/Api/AuthTest.php
php artisan test --compact tests/Feature/Api/ThreadTest.php
php artisan test --compact tests/Feature/Api/MessageTest.php
php artisan test --compact tests/Feature/Api/NotificationTest.php
php artisan test --compact tests/Feature/Api/ApiErrorResponseTest.php
php artisan test --compact tests/Feature/DatabaseStructureTest.php
```

---

## Documentación del proyecto

| Documento | Contenido |
|---|---|
| [`docs/ARCHITECTURE.md`](docs/ARCHITECTURE.md) | Arquitectura y capas |
| [`docs/TECHNICAL_DECISIONS.md`](docs/TECHNICAL_DECISIONS.md) | Decisiones técnicas y uso de IA |
| [`docs/openapi.yaml`](docs/openapi.yaml) | Swagger / OpenAPI 3 |
| [`docs/postman/...`](docs/postman/Facturify_Inbox_API.postman_collection.json) | Colección Postman |
| [`resources/prueba/FASES_PRUEBA.md`](resources/prueba/FASES_PRUEBA.md) | Requerimientos y fases |
| [`resources/prueba/DEVELOPMENT_RULES.md`](resources/prueba/DEVELOPMENT_RULES.md) | Reglas de desarrollo |

---

## Estructura relevante

```
app/
├── Actions/                 # CreateThreadAction, ReplyToThreadAction
├── Exceptions/              # ApiExceptionRenderer + excepciones de dominio
├── Http/
│   ├── Controllers/Api/
│   ├── Requests/Api/
│   ├── Resources/Api/
│   └── Responses/ApiResponse.php
├── Models/                  # User, Thread, Message
└── Traits/ApiResponse.php
routes/api.php
database/migrations|factories|seeders/
tests/Feature/Api/
docs/
```

---

## Licencia

Proyecto de prueba técnica. Ver `LICENSE` del repositorio base si aplica.
