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

> La estandarización completa se implementa en la **Fase 7**. En fases tempranas, mantener consistencia desde el inicio.

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

## Documentación final (Fase 9)

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

| Fase | Nombre | Estado | Commit sugerido |
|---|---|---|---|
| 1 | Inicio del proyecto y requerimientos | ✅ Completada | — |
| 2 | Configuración inicial del proyecto | 🔵 **Actual** | `feat: configure authentication with JWT` |
| 3 | Diseño de la base de datos | ⏳ Pendiente | `feat: create database structure` |
| 4 | API de autenticación | ⏳ Pendiente | `feat: implement authentication endpoints` |
| 5 | API de conversaciones | ⏳ Pendiente | `feat: implement thread endpoints` |
| 6 | API de mensajes | ⏳ Pendiente | `feat: implement messaging endpoints` |
| 7 | Manejo de errores | ⏳ Pendiente | `refactor: standardize api responses` |
| 8 | Pruebas | ⏳ Pendiente | `test: add feature tests` |
| 9 | Documentación | ⏳ Pendiente | `docs: complete project documentation` |

---

## Fase 2 — Configuración inicial (alcance actual)

> **Objetivo:** dejar la base del proyecto lista para desarrollar la API. Al finalizar se debe poder **iniciar sesión mediante la API**.

### Tareas de esta fase

- [ ] Configurar base de datos (MySQL)
- [ ] Instalar y configurar JWT
- [ ] Configurar autenticación API (guard `api` con driver JWT)
- [ ] Configurar CORS para consumo desde clientes externos
- [ ] Definir variables de entorno necesarias
- [ ] Organizar estructura inicial de carpetas para la API

### Entregables esperados

1. Proyecto Laravel funcional con conexión a MySQL
2. Paquete JWT instalado y configurado (`JWT_SECRET` en `.env`)
3. Guard de autenticación `api` operativo
4. CORS habilitado para rutas API
5. Estructura de carpetas preparada (ver `DEVELOPMENT_RULES.md`)
6. Capacidad de autenticarse vía API (login básico funcional o infraestructura lista para Fase 4)

### Fuera de alcance en Fase 2

- Migraciones de `threads` y `messages` → Fase 3
- Endpoints completos de autenticación con validaciones y tests → Fase 4
- Módulos de conversaciones y mensajes → Fases 5 y 6
- Estandarización de respuestas → Fase 7
- Suite de pruebas → Fase 8
- Swagger y documentación final → Fase 9

---

## Fases futuras (referencia)

### Fase 3 — Diseño de la base de datos

Solo modelo de datos: migraciones, modelos, relaciones Eloquent, factories y seeders. **Sin controladores.**

### Fase 4 — API de autenticación

Login, usuario autenticado, middleware, validaciones y pruebas.

### Fase 5 — API de conversaciones

Listado (paginación, búsqueda, filtros) y detalle de hilo. Sin responder mensajes aún.

### Fase 6 — API de mensajes

Crear conversación (con primer mensaje) y responder conversación. Transacciones y validaciones.

### Fase 7 — Manejo de errores

Estandarizar respuestas, excepciones, HTTP status, mensajes y API Resources.

### Fase 8 — Pruebas

Feature tests: validaciones, autenticación, permisos y casos de error.

### Fase 9 — Documentación

README final, Swagger, Postman, arquitectura y decisiones técnicas.

---

## Endpoints previstos (referencia completa)

> No implementar todos en Fase 2. Esta tabla es la visión final del proyecto.

| Método | Ruta | Descripción | Fase |
|---|---|---|---|
| `POST` | `/api/auth/login` | Iniciar sesión, obtener JWT | 4 |
| `GET` | `/api/auth/me` | Usuario autenticado | 4 |
| `GET` | `/api/threads` | Listar conversaciones | 5 |
| `GET` | `/api/threads/{id}` | Detalle de hilo | 5 |
| `POST` | `/api/threads` | Crear conversación + primer mensaje | 6 |
| `POST` | `/api/threads/{id}/messages` | Responder conversación | 6 |
| `GET` | `/api/notifications/unread` | Mensajes no leídos | 6 |

---

## Criterios de aceptación por fase

### Fase 2 ✅ cuando:

1. `php artisan migrate` ejecuta sin errores (tablas base de Laravel + users)
2. JWT está instalado y `php artisan jwt:secret` configurado
3. Existe guard `api` con driver JWT en `config/auth.php`
4. CORS permite solicitudes a rutas `/api/*`
5. Variables de entorno documentadas en `.env.example`
6. Estructura de carpetas API creada
7. Es posible obtener un token JWT mediante login API (endpoint básico o infraestructura verificada)
