# Prueba técnica Facturify — API de mensajería (Inbox)

> Documento de requerimientos para agentes de desarrollo. Describe qué construir, en qué orden y qué queda fuera de alcance en cada fase.

## Contexto

Prueba técnica de **Back-End**: construir una API RESTful con Laravel que demuestre buenas prácticas del framework, código limpio, escalable y fácil de mantener.

El sistema es un **módulo de mensajería tipo Inbox** (similar a soporte, tickets o correo interno). Los usuarios autenticados podrán consultar conversaciones, ver hilos y responder mensajes.

**Documento fuente:** [`descripción de la prueba.txt`](./descripción%20de%20la%20prueba.txt)

**Reglas de desarrollo:** [`DEVELOPMENT_RULES.md`](./DEVELOPMENT_RULES.md)

---

## Objetivo del proyecto

Construir una API REST que permita administrar conversaciones y mensajes entre usuarios autenticados, priorizando organización del código, reutilización de componentes y mantenibilidad.

---

## Alcance funcional

La API debe permitir:

| Funcionalidad | Descripción |
|---|---|
| Autenticación JWT | Login con email y contraseña; respuesta con Bearer Token |
| Usuario autenticado | Consultar información del usuario logueado |
| Listado de conversaciones | Paginación, filtros y búsqueda |
| Detalle de hilo | Obtener conversación completa con sus mensajes |
| Crear conversación | Registrar hilo + primer mensaje en una sola operación |
| Responder conversación | Agregar mensajes a un hilo existente |
| Mensajes no leídos | Endpoint para consultar notificaciones de no leídos |

---

## Stack tecnológico

| Tecnología | Uso |
|---|---|
| Laravel 13 | Framework principal |
| PHP 8.3 | Lenguaje |
| MySQL | Base de datos |
| JWT | Autenticación stateless |
| Eloquent ORM | Acceso a datos |
| Pest | Pruebas automatizadas |
| Git | Control de versiones |
| Swagger | Documentación de API (fase final) |

---

## Arquitectura

Seguir **MVC de Laravel** con separación clara de responsabilidades:

```
routes/          → Definición de endpoints
Controllers/     → Recibir solicitudes y delegar lógica
Models/          → Entidades y relaciones Eloquent
Form Requests/   → Validación de entrada
API Resources/   → Formato uniforme de respuestas
Middleware/      → Protección de endpoints
Migrations/      → Esquema de base de datos
Factories/       → Datos de prueba para tests
Seeders/         → Datos de ejemplo
tests/           → Pruebas automatizadas
```

**Regla:** evitar controladores grandes y lógica duplicada.

---

## Módulos del sistema

### 1. Autenticación

- Acceso protegido con **JWT**.
- Login: email + password → `Bearer Token`.
- El token se envía en el header `Authorization: Bearer {token}` en todas las solicitudes protegidas.
- Endpoint para obtener el usuario autenticado.

### 2. Conversaciones (threads)

- Representan un **hilo de mensajes**.
- Campos clave: asunto, usuario creador, estado, fecha del último mensaje.
- Listado con paginación, filtros y búsqueda.
- Detalle completo de un hilo específico.

### 3. Mensajes

- Múltiples mensajes por conversación.
- Al crear una conversación se registra automáticamente el **primer mensaje**.
- Responder una conversación = agregar un nuevo mensaje al hilo.

### 4. Notificaciones

- Endpoint para consultar **mensajes no leídos**.

---

## Modelo de datos

Usar la tabla `users` de Laravel para autenticación. Crear tablas `threads` y `messages`.

### Diagrama entidad-relación

```
                    +----------------------+
                    |        users         |
                    +----------------------+
                    | PK id                |
                    | name                 |
                    | email                |
                    | password             |
                    | timestamps           |
                    +----------+-----------+
                               |
                               | 1 crea N
                               |
                    +----------v-----------+
                    |       threads        |
                    +----------------------+
                    | PK id                |
                    | subject              |
                    | FK created_by        |
                    | status               |
                    | last_message_at      |
                    | timestamps           |
                    +----------+-----------+
                               |
                               | 1 contiene N
                               |
                    +----------v-----------+
                    |      messages        |
                    +----------------------+
                    | PK id                |
                    | FK thread_id         |
                    | FK user_id           |
                    | body                 |
                    | is_read              |
                    | read_at              |
                    | timestamps           |
                    +----------+-----------+
                               ^
                               | N escribe 1
                               |
                    +----------+-----------+
                    |        users         |
                    +----------------------+
```

### Relaciones Eloquent (referencia)

| Modelo | Relación | Destino |
|---|---|---|
| `User` | `hasMany` | `Thread` (como creador) |
| `User` | `hasMany` | `Message` (como autor) |
| `Thread` | `belongsTo` | `User` (`created_by`) |
| `Thread` | `hasMany` | `Message` |
| `Message` | `belongsTo` | `Thread` |
| `Message` | `belongsTo` | `User` |

---

## Validaciones

Todas las entradas se validan con **Form Requests**. Controladores sin reglas de validación inline.

| Acción | Reglas mínimas |
|---|---|
| Login | Email y contraseña obligatorios |
| Crear conversación | Asunto obligatorio |
| Crear/responder mensaje | Contenido (`body`) obligatorio |

---

## Formato de respuestas

Todas las respuestas deben seguir un **formato uniforme**:

```json
{
  "success": true,
  "message": "Descripción del resultado",
  "data": {}
}
```

En errores, incluir mensajes de validación o descripción del error con el código HTTP correspondiente.

> La estandarización completa se implementa en la **Fase 6**. En fases tempranas, mantener consistencia desde el inicio.

---

## Códigos HTTP

| Código | Uso |
|---|---|
| `200` | Consultas exitosas |
| `201` | Creación de recursos |
| `401` | Usuario no autenticado |
| `403` | Sin permisos suficientes |
| `404` | Recurso no encontrado |
| `422` | Errores de validación |
| `500` | Error interno inesperado |

---

## Pruebas automatizadas

Cubrir como mínimo:

- Inicio de sesión
- Consulta de conversaciones
- Detalle de un hilo
- Creación de conversaciones
- Respuesta a conversaciones

Usar **Factories y Seeders** para generar datos de ejemplo realistas.

---

## Documentación final (Fase 7)

Al terminar el desarrollo, incluir:

- Requisitos e instalación
- Variables de entorno
- Migraciones y seeders
- Ejecución de pruebas
- Descripción de arquitectura
- Decisiones técnicas
- Descripción de cada endpoint con ejemplos
- Swagger y colección Postman

---

## Plan de desarrollo por fases

El desarrollo es **incremental**. Cada fase debe quedar funcional y commiteable antes de pasar a la siguiente.

> **Nota:** La autenticación JWT (login + `/me`) se implementó en la Fase 2. Las pruebas se escriben **en cada fase**, no en una fase aparte.

| Fase | Nombre | Estado | Commit sugerido |
|---|---|---|---|
| 1 | Inicio del proyecto y requerimientos | ✅ Completada | — |
| 2 | Configuración inicial + autenticación JWT | ✅ Completada | `feat: configure authentication with JWT` |
| 3 | Diseño de la base de datos | ✅ Completada | `feat: create database structure` |
| 4 | API de conversaciones | ✅ Completada | `feat: implement thread endpoints` |
| 5 | API de mensajes | ✅ Completada | `feat: implement messaging endpoints` |
| 6 | Manejo de errores | 🔵 **Siguiente** | `refactor: standardize api responses` |
| 7 | Documentación | ⏳ Pendiente | `docs: complete project documentation` |

---

## Fase 2 — Configuración inicial + autenticación JWT ✅

> **Objetivo:** base del proyecto + login API con JWT.

### Tareas completadas

- [x] Configurar base de datos (MySQL)
- [x] Instalar y configurar JWT
- [x] Guard `api` + modelo `User` como `JWTSubject`
- [x] CORS, variables de entorno, estructura de carpetas
- [x] Endpoints `POST /api/auth/login` y `GET /api/auth/me`
- [x] Form Request, API Resource y tests de autenticación

---

## Fase 3 — Diseño de la base de datos ✅

Solo modelo de datos: migraciones, modelos, relaciones Eloquent, factories y seeders. **Sin controladores de negocio.**

### Tareas completadas

- [x] Migración `threads` / `messages`
- [x] Enum `ThreadStatus`
- [x] Modelos y relaciones Eloquent
- [x] Factories, seeders y tests de estructura

---

## Fase 4 — API de conversaciones ✅

> **Objetivo:** listar conversaciones (paginación, búsqueda, filtros) y obtener el detalle de un hilo. **Sin crear ni responder mensajes.**

### Tareas completadas

- [x] `GET /api/threads` — listado paginado
- [x] Búsqueda por asunto (`search`)
- [x] Filtro por estado (`status`)
- [x] `GET /api/threads/{thread}` — detalle con mensajes
- [x] Protección con `auth:api`
- [x] API Resources y tests

### Fuera de alcance en Fase 4

- Crear conversación / responder mensajes → Fase 5
- Mensajes no leídos → Fase 5
- Estandarización global de errores → Fase 6
- Swagger / docs finales → Fase 7

---

## Fase 5 — API de mensajes ✅

> **Objetivo:** crear conversación (con primer mensaje), responder hilos y consultar no leídos. Con transacciones y validaciones.

### Tareas completadas

- [x] `POST /api/threads` — crear conversación + primer mensaje (transacción)
- [x] `POST /api/threads/{thread}/messages` — responder conversación
- [x] `GET /api/notifications/unread` — mensajes no leídos
- [x] Form Requests (`StoreThreadRequest`, `StoreMessageRequest`)
- [x] Actions (`CreateThreadAction`, `ReplyToThreadAction`)
- [x] Tests de creación, respuesta, validación y notificaciones

### Fuera de alcance en Fase 5

- Estandarización global de errores → Fase 6
- Swagger / docs finales → Fase 7

---

## Fases futuras (referencia)

### Fase 6 — Manejo de errores

Estandarizar respuestas, excepciones, HTTP status, mensajes y API Resources.

### Fase 7 — Documentación

README final, Swagger, Postman, arquitectura y decisiones técnicas.

---

## Endpoints previstos (referencia completa)

| Método | Ruta | Descripción | Fase |
|---|---|---|---|
| `POST` | `/api/auth/login` | Iniciar sesión, obtener JWT | 2 ✅ |
| `GET` | `/api/auth/me` | Usuario autenticado | 2 ✅ |
| `GET` | `/api/threads` | Listar conversaciones | 4 ✅ |
| `GET` | `/api/threads/{id}` | Detalle de hilo | 4 ✅ |
| `POST` | `/api/threads` | Crear conversación + primer mensaje | 5 ✅ |
| `POST` | `/api/threads/{id}/messages` | Responder conversación | 5 ✅ |
| `GET` | `/api/notifications/unread` | Mensajes no leídos | 5 ✅ |

---

## Criterios de aceptación

### Fase 5 ✅ cuando:

1. `POST /api/threads` crea hilo + primer mensaje en una transacción
2. `POST /api/threads/{id}/messages` agrega respuesta y actualiza `last_message_at`
3. No se puede responder un hilo cerrado → `422`
4. `GET /api/notifications/unread` lista mensajes no leídos de otros en hilos relacionados
5. Validaciones de `subject`/`body` → `422`
6. Sin token → `401`
7. Tests de la fase pasan
