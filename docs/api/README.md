# API Documentation - Sistema de Talleres de Verano

## Endpoints

### Autenticación
```
POST /api/auth/login
POST /api/auth/logout
POST /api/auth/refresh
```

### Estudiantes
```
GET /api/estudiantes
POST /api/estudiantes
GET /api/estudiantes/{id}
PUT /api/estudiantes/{id}
DELETE /api/estudiantes/{id}
```

### Matrículas
```
GET /api/matriculas
POST /api/matriculas
GET /api/matriculas/{id}
```

### Pagos
```
POST /api/pagos/iniciar
GET /api/pagos/{id}/estado
POST /api/pagos/webhook
```

## Autenticación

Todas las peticiones deben incluir:
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

## Ejemplos de Uso

### Login
```json
POST /api/auth/login
{
    "email": "usuario@ejemplo.com",
    "password": "contraseña"
}
```

### Crear Matrícula
```json
POST /api/matriculas
{
    "estudiante_id": 1,
    "taller_id": 2,
    "turno": "mañana",
    "horario_id": 3
}
```

## Códigos de Estado

- 200: OK
- 201: Created
- 400: Bad Request
- 401: Unauthorized
- 403: Forbidden
- 404: Not Found
- 422: Unprocessable Entity
- 500: Internal Server Error

## Manejo de Errores

Los errores son devueltos en el siguiente formato:
```json
{
    "error": true,
    "message": "Descripción del error",
    "code": "ERROR_CODE"
}
```

## Límites de Uso

- Rate Limit: 100 requests/minute
- Max Upload Size: 10MB
- Timeout: 30 seconds

## Seguridad

- HTTPS requerido
- CORS habilitado para dominios autorizados
- Rate limiting por IP
- Validación de tokens JWT

## Webhooks

Los webhooks son enviados con:
```
X-Signature: {HMAC signature}
Content-Type: application/json
```

## Integración con Yape

### Iniciar Pago
```json
POST /api/pagos/yape/iniciar
{
    "amount": 100.00,
    "concept": "Matrícula Taller",
    "matricula_id": 1
}
```

### Webhook Yape
```json
POST /api/pagos/yape/webhook
{
    "transaction_id": "...",
    "status": "completed",
    "amount": 100.00
}
```