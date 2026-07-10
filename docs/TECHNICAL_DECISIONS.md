# Decisiones técnicas y uso de IA

## Decisiones técnicas

| Decisión | Justificación |
|---|---|
| **JWT** (`tymon/jwt-auth`) | API stateless, consumible por cualquier cliente sin cookies/sesión. |
| **MySQL** (no SQLite en runtime) | Alineado con un entorno de producción típico de Laravel. |
| **Pest** | Convención actual del ecosistema Laravel; tests legibles y rápidos. |
| **Form Requests** | Validación reutilizable y controladores limpios. |
| **API Resources** | Desacoplan el JSON del esquema interno de los modelos. |
| **Action classes** | Encapsulan crear hilo / responder con transacciones DB. |
| **ApiResponse + ApiExceptionRenderer** | Un solo contrato de éxito/error en toda la API. |
| **Desarrollo por fases** | Entregables verificables (Postman + tests) en cada commit. |
| **OpenAPI estático + Postman** | Documentación portable sin acoplar un paquete Swagger extra al runtime. |
| **Enum `ThreadStatus`** | Estados tipados (`open`, `closed`, `pending`) en PHP y base de datos. |

## Alcance consciente

- La prueba se centró en la **API REST**. El starter kit incluye Inertia/React/Fortify para web; la mensajería Inbox no depende de ese frontend.
- No se implementó refresh token ni roles/ACL complejos: el objetivo era un Inbox claro y mantenible.
- Las notificaciones de no leídos listan mensajes de **otros usuarios** en hilos donde el autenticado participa (creador o autor de mensajes).

## Uso de IA

Durante el desarrollo se utilizó un agente de coding (Cursor) de forma asistida para:

1. Convertir el enunciado de la prueba a documentos accionables (`FASES_PRUEBA.md`, `DEVELOPMENT_RULES.md`).
2. Implementar cada fase con Artisan, Pest, Pint y convenciones Laravel.
3. Revisar y estandarizar respuestas/errores.
4. Generar la documentación final (README, OpenAPI, Postman, arquitectura).

La IA **no sustituyó** la validación manual: cada fase se verificó con tests automatizados y pruebas en Postman antes del commit correspondiente.

## Commits por fase

| Fase | Commit |
|---|---|
| 2 | `feat: configure authentication with JWT` |
| 3 | `feat: create database structure` |
| 4 | `feat: implement thread endpoints` |
| 5 | `feat: implement messaging endpoints` |
| 6 | `refactor: standardize api responses` |
| 7 | `docs: complete project documentation` |
