# Reglas generales de desarrollo — Backend API

> Guía para agentes de desarrollo. Define convenciones, estándares y restricciones del proyecto. Consultar junto con [`FASES_PRUEBA.md`](./FASES_PRUEBA.md).

---

## Principios fundamentales

1. **API REST pura** — Este proyecto es back-end exclusivo. No construir frontend ni vistas Inertia/React para la prueba.
2. **Desarrollo por fases** — Completar y verificar cada fase antes de avanzar. No adelantar funcionalidades de fases posteriores.
3. **Código limpio y mantenible** — Controladores delgados, validación en Form Requests, respuestas con API Resources.
4. **Convenciones de Laravel** — Seguir patrones del framework; no reinventar lo que Laravel ya resuelve.
5. **Commits atómicos** — Un commit por fase con el mensaje sugerido en el README.

---

## Stack y versiones

| Componente | Versión / Paquete |
|---|---|
| PHP | 8.3 |
| Laravel | 13 |
| Base de datos | MySQL |
| Autenticación | JWT (`tymon/jwt-auth` o equivalente compatible con Laravel 13) |
| Testing | Pest 4 |
| Formateo | Laravel Pint |
| Análisis estático | Larastan (opcional, no bloqueante) |

---

## Fase actual: 4 — API de autenticación

> La Fase 3 está completada. Ver detalle en [`FASES_PRUEBA.md`](./FASES_PRUEBA.md).

### Fase 3 completada — resumen

```
[x] Migraciones threads y messages
[x] Enum ThreadStatus
[x] Modelos Thread y Message con relaciones
[x] Relaciones User → threads / messages
[x] Factories (ThreadFactory, MessageFactory)
[x] ThreadSeeder + DatabaseSeeder
[x] Tests de estructura de datos
```

### Commit de la Fase 3

```
feat: create database structure
```

### Fase 2 completada — resumen

```
[x] Base de datos MySQL configurada y conectada
[x] Paquete JWT instalado y publicado
[x] JWT_SECRET generado en .env
[x] Guard 'api' configurado con driver JWT
[x] Modelo User implementa JWTSubject
[x] CORS configurado para rutas /api/*
[x] Variables de entorno documentadas en .env.example
[x] Estructura de carpetas API creada
[x] Rutas API registradas en routes/api.php
[x] Login API funcional (token JWT retornado)
```

### Commit de la Fase 2

```
feat: configure authentication with JWT
```

---

## Configuración de base de datos

### Variables de entorno requeridas

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=facturify_inbox
DB_USERNAME=root
DB_PASSWORD=
```

### Reglas

- Usar MySQL, no SQLite, para alinear con producción.
- Documentar todas las variables en `.env.example` sin valores sensibles.
- Ejecutar `php artisan migrate` para verificar conexión.

---

## Configuración JWT

### Instalación

```bash
composer require tymon/jwt-auth
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"
php artisan jwt:secret
```

### Configuración de auth (`config/auth.php`)

- Agregar guard `api` con driver `jwt`.
- El provider debe apuntar al modelo `User`.

```php
'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',
    ],
    'api' => [
        'driver' => 'jwt',
        'provider' => 'users',
    ],
],
```

### Modelo User

El modelo `User` debe implementar `Tymon\JWTAuth\Contracts\JWTSubject`:

```php
public function getJWTIdentifier(): mixed
{
    return $this->getKey();
}

/**
 * @return array<string, mixed>
 */
public function getJWTCustomClaims(): array
{
    return [];
}
```

### Variables JWT en `.env`

```env
JWT_SECRET=           # Generado con php artisan jwt:secret
JWT_TTL=60            # Minutos de validez del token
JWT_REFRESH_TTL=20160 # Minutos para refresh (opcional en Fase 2)
```

---

## Configuración CORS

- Configurar en `config/cors.php` o mediante el middleware de CORS de Laravel.
- Permitir rutas `api/*`.
- Habilitar headers: `Authorization`, `Content-Type`, `Accept`.
- En desarrollo, permitir `*` o el origen del cliente de pruebas (Postman no requiere CORS; sí un frontend futuro).

```php
'paths' => ['api/*'],
'allowed_methods' => ['*'],
'allowed_headers' => ['*'],
'supports_credentials' => false,
```

---

## Organización de carpetas

Estructura objetivo para la API. Crear carpetas vacías o con clases base en Fase 2; poblar en fases siguientes.

```
app/
├── Http/
│   ├── Controllers/
│   │   └── Api/
│   │       ├── AuthController.php      # Fase 4
│   │       ├── ThreadController.php    # Fase 5
│   │       ├── MessageController.php   # Fase 6
│   │       └── NotificationController.php
│   ├── Requests/
│   │   └── Api/
│   │       ├── Auth/
│   │       │   └── LoginRequest.php    # Fase 4
│   │       ├── Thread/
│   │       │   └── StoreThreadRequest.php  # Fase 6
│   │       └── Message/
│   │           └── StoreMessageRequest.php # Fase 6
│   ├── Resources/
│   │   └── Api/
│   │       ├── UserResource.php
│   │       ├── ThreadResource.php
│   │       └── MessageResource.php
│   └── Middleware/
│       └── (usar auth:api de Laravel)
├── Models/
│   ├── User.php
│   ├── Thread.php      # Fase 3
│   └── Message.php     # Fase 3
routes/
└── api.php             # Todas las rutas API aquí
```

### Reglas de organización

| Regla | Detalle |
|---|---|
| Rutas API | Solo en `routes/api.php`, prefijo `/api` automático |
| Controladores | Namespace `App\Http\Controllers\Api` |
| Form Requests | Namespace `App\Http\Requests\Api\{Módulo}` |
| API Resources | Namespace `App\Http\Resources\Api` |
| Sin lógica de negocio en rutas | Usar controladores |
| Sin validación en controladores | Usar Form Requests |

---

## Convenciones de código PHP

### Estilo

- Ejecutar `vendor/bin/pint --dirty` después de modificar archivos PHP.
- Usar **constructor property promotion** cuando aplique.
- Tipos de retorno explícitos en todos los métodos.
- PHPDoc con array shapes cuando sea útil.
- Llaves obligatorias en estructuras de control, incluso de una línea.

### Nomenclatura

| Elemento | Convención | Ejemplo |
|---|---|---|
| Modelos | Singular, PascalCase | `Thread`, `Message` |
| Tablas | Plural, snake_case | `threads`, `messages` |
| Controladores | PascalCase + Controller | `ThreadController` |
| Form Requests | Acción + Request | `StoreThreadRequest` |
| API Resources | Modelo + Resource | `ThreadResource` |
| Rutas | kebab-case, plural | `/api/threads` |
| Métodos de controlador | camelCase, descriptivos | `index`, `show`, `store` |

### Ejemplo de controlador delgado

```php
public function store(StoreThreadRequest $request): JsonResponse
{
    $thread = $this->threadService->create($request->validated());

    return $this->successResponse(
        new ThreadResource($thread),
        'Conversación creada exitosamente',
        201
    );
}
```

---

## Rutas API

### Registro

En `bootstrap/app.php` o `RouteServiceProvider`, asegurar que `routes/api.php` esté registrado.

### Prefijo y middleware

Todas las rutas en `routes/api.php` tienen prefijo `/api` y middleware `api` por defecto.

```php
// routes/api.php — ejemplo Fase 2/4
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::middleware('auth:api')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
    });
});
```

### Protección

- Rutas públicas: solo `POST /api/auth/login`.
- Todas las demás rutas: middleware `auth:api`.

---

## Formato de respuestas API

Estandarizar desde el inicio. Implementación completa en Fase 7; usar este contrato desde Fase 2:

### Respuesta exitosa

```json
{
  "success": true,
  "message": "Operación exitosa",
  "data": {}
}
```

### Respuesta de error

```json
{
  "success": false,
  "message": "Descripción del error",
  "errors": {}
}
```

### Helper recomendado (crear en Fase 2 o 7)

```php
// app/Traits/ApiResponse.php o app/Http/Responses/ApiResponse.php
protected function successResponse(mixed $data, string $message = 'OK', int $code = 200): JsonResponse
protected function errorResponse(string $message, int $code = 400, array $errors = []): JsonResponse
```

---

## Validación

- **Siempre** usar Form Requests; nunca `$request->validate()` en controladores.
- Mensajes de error en español (coherente con el dominio del proyecto).
- Respuesta `422` con detalle de errores por campo.

---

## Seguridad

| Regla | Implementación |
|---|---|
| Contraseñas | Hash con `bcrypt` (default de Laravel) |
| Tokens | JWT con TTL configurado; no almacenar en sesión |
| Rutas protegidas | Middleware `auth:api` |
| Mass assignment | `$fillable` explícito en modelos |
| CORS | Solo orígenes necesarios en producción |

---

## Manejo de errores

| Situación | Código HTTP |
|---|---|
| Éxito en consulta | 200 |
| Recurso creado | 201 |
| No autenticado | 401 |
| Sin permisos | 403 |
| No encontrado | 404 |
| Validación fallida | 422 |
| Error interno | 500 |

No exponer stack traces en producción. Usar `APP_DEBUG=false` en producción.

---

## Pruebas

- Framework: **Pest** con `pestphp/pest-plugin-laravel`.
- Crear tests en Fase 4+ (login en Fase 4; suite completa en Fase 8).
- Usar `RefreshDatabase` en feature tests.
- Usar factories, no datos hardcodeados.
- Ejecutar: `php artisan test --compact --filter=NombreTest`.

### Convención de nombres de test

```php
it('returns jwt token on valid login credentials', function () {
    // ...
});

it('returns 401 when token is missing', function () {
    // ...
});
```

---

## Git y control de versiones

### Mensajes de commit por fase

| Fase | Mensaje |
|---|---|
| 2 | `feat: configure authentication with JWT` |
| 3 | `feat: create database structure` |
| 4 | `feat: implement authentication endpoints` |
| 5 | `feat: implement thread endpoints` |
| 6 | `feat: implement messaging endpoints` |
| 7 | `refactor: standardize api responses` |
| 8 | `test: add feature tests` |
| 9 | `docs: complete project documentation` |

### Reglas de commit

- Un commit por fase completada.
- No commitear `.env` (solo `.env.example`).
- No commitear `vendor/` ni `node_modules/`.

---

## Restricciones por fase

### Fase 2 — Solo configuración

| Permitido | Prohibido |
|---|---|
| Configurar DB, JWT, CORS, .env | Migraciones de threads/messages |
| Estructura de carpetas | Endpoints de conversaciones/mensajes |
| Login básico / infraestructura auth | Tests de funcionalidad de negocio |
| User con JWTSubject | API Resources de Thread/Message |
| routes/api.php con auth | Seeders de datos de negocio |

### Regla general

> Si una tarea pertenece a una fase posterior, **no implementarla**. Documentar como pendiente y continuar con el alcance actual.

---

## Comandos útiles

```bash
# Verificar conexión y migraciones
php artisan migrate

# Generar secret JWT
php artisan jwt:secret

# Listar rutas API
php artisan route:list --path=api

# Formatear código
vendor/bin/pint --dirty

# Ejecutar tests (cuando existan)
php artisan test --compact

# Crear archivos con Artisan
php artisan make:controller Api/AuthController
php artisan make:request Api/Auth/LoginRequest
php artisan make:resource Api/UserResource
```

---

## Decisiones técnicas documentadas

| Decisión | Justificación |
|---|---|
| JWT sobre sesiones | API stateless, consumible por cualquier cliente |
| MySQL sobre SQLite | Alineado con entorno de producción |
| Pest sobre PHPUnit puro | Convención del ecosistema Laravel actual |
| Form Requests | Validación reutilizable y controladores limpios |
| API Resources | Respuestas consistentes y desacopladas del modelo |
| Desarrollo por fases | Verificación incremental, commits revisables |

---

## Referencias

- Requerimientos completos: [`README.md`](./README.md)
- Documento original: [`descripción de la prueba.txt`](./descripción%20de%20la%20prueba.txt)
- Guías del proyecto: [`AGENTS.md`](../../AGENTS.md) (reglas Laravel Boost del workspace)

---

## Instrucciones para el agente

1. Leer `README.md` para entender **qué** construir.
2. Leer este archivo para entender **cómo** construirlo.
3. Verificar la **fase actual** antes de escribir código.
4. Completar el checklist de la fase y verificar criterios de aceptación.
5. Ejecutar Pint y tests relevantes antes de commitear.
6. No avanzar a la siguiente fase sin confirmación del usuario.
