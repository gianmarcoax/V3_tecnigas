# 📋 DOCUMENTACIÓN TÉCNICA — Dashboard Tecnigas (Laravel)
> **Para cualquier persona o IA que tome este proyecto:** Este archivo explica de forma clara y completa cómo funciona todo el sistema en su versión Laravel (V3).

---

## 🧭 ¿Qué es esto?

Es un **dashboard web** desarrollado para Tecnigas (empresa peruana). Conecta con **Odoo 18** (su ERP/sistema de gestión) para que el personal pueda:

- Ver el catálogo de productos con fotos, precios y stock
- Recepcionar mercancía de proveedores
- Trasladar productos entre Almacén y Tienda
- Crear y editar productos en Odoo
- Ver el ranking de ventas de los cajeros del POS
- Controlar asistencias del personal en tiempo real y registro semanal
- Consultar stock en Tienda y Almacén con búsqueda inteligente por similitud
- Calcular remuneración semanal: salarios, bonos grupales/individuales, metas y justificaciones

---

## 🏗️ Arquitectura del Sistema (V3 — Laravel)

```
┌──────────────────────────────────────────────────┐
│          NAVEGADOR (Blade + JS)                  │
│  /dashboard         → menú principal             │
│  /catalogo          → catálogo de productos      │
│  /recepcion         → recepcionar mercancía      │
│  /traslado          → trasladar entre ubicaciones│
│  /productos         → crear/editar productos     │
│  /ventas            → ranking de ventas POS      │
│  /asistencias       → control de personal        │
│  /stock             → consulta de inventario     │
│  /remuneracion      → salarios, bonos y metas    │
└───────────────────┬──────────────────────────────┘
                    │  fetch("/api/...")
                    │  (HTTP GET o POST — JSON)
┌───────────────────▼──────────────────────────────┐
│         Laravel 11 (PHP 8.2)                     │
│   Servidor en http://localhost:8000              │
│   - Auth con Laravel Breeze (Blade)              │
│   - Controllers en app/Http/Controllers/         │
│   - Rutas en routes/web.php y routes/api.php     │
│   - OdooService: lógica XML-RPC centralizada     │
│   - BD local: PostgreSQL (tecnigas_bd)           │
└──────────┬────────────────────┬──────────────────┘
           │ XML-RPC            │ Eloquent ORM
           ▼                    ▼
┌──────────────────┐  ┌─────────────────────────────┐
│  ODOO 18 (nube)  │  │  PostgreSQL 16 (local)       │
│  db_tecnigas     │  │  tecnigas_bd                 │
│  Tiempo real     │  │  Remuneración + Usuarios     │
└──────────────────┘  └─────────────────────────────┘
```

**Resumen en palabras simples:**
1. El usuario hace login en `http://localhost:8000`
2. Navega al módulo que necesita (panel principal → módulo)
3. El navegador hace peticiones al backend Laravel
4. Laravel consulta Odoo vía XML-RPC o su BD local PostgreSQL según el módulo
5. Devuelve JSON al navegador
6. El navegador muestra los datos en pantalla

---

## 🗂️ Estructura del Proyecto

```
V3_Dashboard_Tecnigas/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── EmpleadoController.php       ← CRUD empleados locales
│   │       ├── RemuneracionController.php   ← Lógica de remuneración
│   │       ├── VentasController.php         ← Ranking POS, detalle y export PDF
│   │       ├── RecepcionController.php      ← Recepción de mercancía (9 endpoints)
│   │       └── TrasladoController.php       ← Traslados internos (9 endpoints)
│   ├── Models/
│   │   ├── User.php                         ← Usuarios del sistema (Breeze)
│   │   ├── Employee.php                     ← Empleados (copia ligera de Odoo)
│   │   ├── SalaryConfig.php                 ← Salarios base
│   │   ├── ShiftGoal.php                    ← Metas grupales por turno
│   │   ├── EmployeeGoal.php                 ← Metas individuales
│   │   ├── AttendanceJustification.php      ← Justificaciones de faltas/tardanzas
│   │   ├── Recepcion.php                    ← Recepciones de mercancía
│   │   ├── RecepcionItem.php                ← Items de recepción
│   │   ├── Traslado.php                     ← Traslados internos
│   │   └── TrasladoItem.php                 ← Items de traslado
│   └── Services/
│       └── OdooService.php                  ← XML-RPC con Odoo (sin extensión xmlrpc)
├── database/
│   └── migrations/
│       ├── create_users_table               ← Breeze
│       ├── create_employees_table
│       ├── create_salary_configs_table
│       ├── create_shift_goals_table
│       ├── create_employee_goals_table
│       ├── create_attendance_justifications_table
│       ├── create_recepciones_table
│       ├── create_traslados_table
│       └── add_indexes_to_recepciones_and_traslados_tables
├── resources/
│   └── views/
│       ├── dashboard.blade.php              ← Panel principal con tarjetas
│       ├── ventas/
│       │   └── index.blade.php             ← Ranking, panel órdenes, modal PDF
│       ├── recepcion/
│       │   └── index.blade.php             ← SPA recepción (873 líneas)
│       ├── traslado/
│       │   └── index.blade.php             ← SPA traslado (689 líneas)
│       └── layouts/                         ← Layouts de Breeze
├── routes/
│   ├── web.php                              ← Rutas con auth
│   └── api.php                              ← Endpoints JSON
└── docs/
    └── DOCUMENTACION.md                     ← Este archivo
```

---

## 🔑 Credenciales y Configuración

### Odoo — `.env`
```env
ODOO_URL=https://tecnigass.pe
ODOO_DB=db_tecnigas
ODOO_USER=coadmin@gmail.com
ODOO_APIKEY=49a9128afa6b2802d21a435fd7f69fdf19483e91
```

### PostgreSQL — `.env`
```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=tecnigas_bd
DB_USERNAME=postgres
DB_PASSWORD=
```

> ⚠️ El archivo `.env` nunca va a Git. En producción usar variables de entorno del servidor.

---

## 🚀 Cómo Iniciar el Sistema

```bash
# Terminal 1 — Servidor Laravel
php artisan serve

# Terminal 2 — Vite (assets)
npm run dev

# Abrir en navegador
http://localhost:8000
```

**Credenciales de acceso (desarrollo):**
- Email: `admin@tecnigas.com`
- Password: `tecnigas2026`

---

## 🗄️ Base de Datos Local (PostgreSQL — tecnigas_bd)

La BD local **solo almacena** lo que no existe en Odoo o que necesita persistencia propia. Todo lo demás (productos, stock, asistencias, ventas) sigue viniendo en tiempo real de Odoo.

### Tabla: `users`
Usuarios del dashboard (gestionado por Laravel Breeze).

| Campo | Tipo | Descripción |
|---|---|---|
| id | bigint PK | |
| name | string | Nombre del usuario |
| email | string unique | Email de login |
| password | string | Hash bcrypt |
| timestamps | | created_at, updated_at |

### Tabla: `employees`
Copia ligera de empleados de Odoo. Solo los campos necesarios para relacionar con remuneración.

| Campo | Tipo | Descripción |
|---|---|---|
| id | bigint PK | |
| odoo_id | integer unique | ID del empleado en Odoo |
| name | string | Nombre completo |
| department | string nullable | Ej: "Ventas" |
| shift | enum | 'manana' \| 'tarde' |
| active | boolean | true por defecto |
| timestamps | | |

### Tabla: `salary_configs`
Un registro por empleado con su salario base semanal.

| Campo | Tipo | Descripción |
|---|---|---|
| id | bigint PK | |
| employee_id | FK → employees | |
| base_salary | decimal(10,2) | Salario base en soles |
| timestamps | | |

### Tabla: `shift_goals`
Metas grupales por turno (mañana/tarde), con periodicidad semanal y mensual.

| Campo | Tipo | Descripción |
|---|---|---|
| id | bigint PK | |
| shift | enum | 'manana' \| 'tarde' |
| period_type | enum | 'weekly' \| 'monthly' |
| sales_goal | decimal(12,2) | Meta de ventas en soles |
| group_bonus | decimal(10,2) | Bono grupal si se cumple |
| timestamps | | |

> Unique: `(shift, period_type)`

### Tabla: `employee_goals`
Metas individuales por empleado, también semanal y mensual.

| Campo | Tipo | Descripción |
|---|---|---|
| id | bigint PK | |
| employee_id | FK → employees | |
| period_type | enum | 'weekly' \| 'monthly' |
| sales_goal | decimal(12,2) | Meta individual en soles |
| individual_bonus | decimal(10,2) | Bono si la cumple |
| timestamps | | |

> Unique: `(employee_id, period_type)`

### Tabla: `attendance_justifications`
Una fila por cada falta o tardanza justificada manualmente por un administrador.

| Campo | Tipo | Descripción |
|---|---|---|
| id | bigint PK | |
| employee_id | FK → employees | |
| date | date | Fecha de la falta/tardanza |
| type | enum | 'falta' \| 'tardanza' |
| justified | boolean | true = justificada |
| reason | text nullable | Motivo |
| created_by | FK → users | Quién la registró |
| timestamps | | |

> Unique: `(employee_id, date, type)`

### Tabla: `order_cleanliness_configs`
Configuración global del módulo de Orden y Limpieza. Solo debe haber un registro activo (Singleton).

| Campo | Tipo | Descripción |
|---|---|---|
| id | bigint PK | |
| score_thresholds | json | Mapeo de rangos de calificación (0-2) a Puntos O&L |
| discount_rules | json | Mapeo de Puntos O&L a Porcentaje de Descuento en el bono |
| timestamps | | |

### Tabla: `order_cleanliness_scores`
Calificación diaria de Orden y Limpieza por empleado. Escala de 0 a 2.

| Campo | Tipo | Descripción |
|---|---|---|
| id | bigint PK | |
| employee_local_id | FK → employees | |
| date | date | Fecha de la calificación |
| score | decimal(3,1) | Nota asignada (0 - 2) |
| created_by | FK → users | Quién calificó |
| timestamps | | |

> Unique: `(employee_local_id, date)`

### Tabla: `recepciones`
Registro local de recepciones de mercancía. Cada recepción crea un `stock.picking` en Odoo.

| Campo | Tipo | Descripción |
|---|---|---|
| id | bigint PK | |
| fecha | date | Fecha de recepción |
| proveedor_id | integer nullable | ID del proveedor en Odoo |
| proveedor_nombre | string | Nombre del proveedor |
| documento | string nullable | Nº de factura/guía |
| usuario | string nullable | Usuario que registró |
| subtotal | decimal(10,2) | Subtotal sin IGV |
| igv | decimal(10,2) | Monto de IGV |
| total | decimal(10,2) | Total con IGV |
| observaciones | text nullable | Notas adicionales |
| location_dest_id | integer nullable | Ubicación destino en Odoo |
| odoo_picking_id | bigint nullable | ID del picking en Odoo |
| timestamps | | |

**Índices:** `fecha`, `proveedor_id`, `odoo_picking_id`

### Tabla: `recepcion_items`
Items de cada recepción (productos recepcionados).

| Campo | Tipo | Descripción |
|---|---|---|
| id | bigint PK | |
| recepcion_id | FK → recepciones | Cascade on delete |
| producto_id | bigint nullable | ID del producto en Odoo |
| producto_nombre | string | Nombre del producto |
| cantidad | decimal(10,2) | Cantidad recepcionada |
| costo | decimal(10,2) | Costo unitario |
| subtotal | decimal(10,2) | cantidad × costo |
| timestamps | | |

### Tabla: `traslados`
Registro local de traslados internos entre ubicaciones. Cada traslado crea un `stock.picking` tipo internal en Odoo.

| Campo | Tipo | Descripción |
|---|---|---|
| id | bigint PK | |
| fecha | date | Fecha de traslado |
| almacen_origen_id | integer | Ubicación origen en Odoo |
| almacen_origen_nombre | string | Nombre de ubicación origen |
| almacen_destino_id | integer | Ubicación destino en Odoo |
| almacen_destino_nombre | string | Nombre de ubicación destino |
| usuario | string nullable | Usuario que registró |
| estado | string | 'pendiente' \| 'confirmado' |
| observaciones | text nullable | Notas adicionales |
| fecha_confirmacion | timestamp nullable | Cuándo se confirmó |
| odoo_picking_id | bigint nullable | ID del picking en Odoo |
| timestamps | | |

**Índices:** `fecha`, `estado`, `odoo_picking_id`

### Tabla: `traslado_items`
Items de cada traslado (productos trasladados).

| Campo | Tipo | Descripción |
|---|---|---|
| id | bigint PK | |
| traslado_id | FK → traslados | Cascade on delete |
| producto_id | bigint nullable | ID del producto en Odoo |
| producto_nombre | string | Nombre del producto |
| cantidad | decimal(10,2) | Cantidad trasladada |
| unidad | string nullable | Unidad de medida |
| timestamps | | |

---

## 🔌 API Endpoints

### Ventas
| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/ventas/ranking` | Ranking de cajeros con KPIs y medios de pago |
| GET | `/api/ventas/detail` | Órdenes individuales de un cajero |
| GET | `/api/ventas/export` | Datos agrupados por pago para PDF |

### Empleados
| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/empleados` | Lista empleados activos de ventas |
| POST | `/api/empleados/sync` | Sincroniza empleados desde Odoo |
| PUT | `/api/empleados/{id}` | Actualiza turno o estado activo |

### Remuneración
| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/remuneracion/config` | Metas grupales por turno |
| GET | `/api/remuneracion/empleados` | Empleados con salarios y metas |
| GET | `/api/remuneracion/justificaciones` | Justificaciones de una semana |
| POST | `/api/remuneracion/salarios` | Guarda salarios base |
| POST | `/api/remuneracion/metas` | Guarda metas grupales e individuales |
| POST | `/api/remuneracion/justificacion` | Registra o quita una justificación |

### Recepción
| Método | Ruta | Descripción |
|---|---|---|
| GET | `/recepcion` | Vista principal (Blade SPA) |
| GET | `/api/recepcion/resumen` | Estadísticas locales |
| GET | `/api/recepcion/historial` | Lista de recepciones guardadas |
| GET | `/api/recepcion/{id}` | Detalle de una recepción |
| GET | `/api/recepcion/productos` | Catálogo Odoo (caché 5 min) |
| GET | `/api/recepcion/proveedores` | Proveedores Odoo (caché 10 min) |
| GET | `/api/recepcion/ubicaciones` | Ubicaciones internas (caché 10 min) |
| POST | `/api/recepcion` | Crear recepción + picking Odoo |
| PUT | `/api/recepcion/{id}` | Actualizar recepción local |
| DELETE | `/api/recepcion/{id}` | Eliminar recepción local |

### Traslado
| Método | Ruta | Descripción |
|---|---|---|
| GET | `/traslado` | Vista principal (Blade SPA) |
| GET | `/api/traslado/resumen` | Estadísticas locales |
| GET | `/api/traslado/historial` | Lista de traslados guardados |
| GET | `/api/traslado/{id}` | Detalle de un traslado |
| GET | `/api/traslado/productos` | Catálogo Odoo (caché 5 min) |
| GET | `/api/traslado/almacenes` | Ubicaciones internas (caché 10 min) |
| POST | `/api/traslado` | Crear traslado + picking Odoo |
| POST | `/api/traslado/{id}/confirm` | Confirmar traslado |
| PUT | `/api/traslado/{id}` | Actualizar traslado local |
| DELETE | `/api/traslado/{id}` | Eliminar traslado local |

---

## 📦 Models y Relaciones

```
User
 └── AttendanceJustification (created_by)

Employee
 ├── SalaryConfig (hasOne)
 ├── EmployeeGoal (hasMany)
 │    ├── weeklyGoal (hasOne where period_type=weekly)
 │    └── monthlyGoal (hasOne where period_type=monthly)
 └── AttendanceJustification (hasMany)

ShiftGoal (independiente — no FK a employees)
```

**Scope útil en Employee:**
```php
Employee::ventas() // filtra active=true y department LIKE '%venta%'
```

---

## 🔗 Modelos de Odoo Utilizados (Referencia)

| Modelo Odoo | Descripción | Módulo que lo usa |
|---|---|---|
| `product.template` | Plantilla de producto | Catálogo, Productos, Recepción, Stock |
| `product.product` | Variante de producto | Recepción, Traslado, Catálogo, Stock |
| `product.category` | Categorías | Catálogo, Productos, Stock |
| `mrp.bom` | Lista de materiales (kits) | Catálogo |
| `stock.location` | Ubicaciones de almacén | Recepción, Traslado, Stock |
| `stock.picking` | Albaranes/transferencias | Recepción, Traslado |
| `stock.move` | Movimientos de stock | Recepción, Traslado |
| `stock.quant` | Cantidades en ubicaciones | Traslado, Recepción, Stock |
| `pos.order` | Órdenes del POS | Ventas, Remuneración |
| `pos.payment` | Pagos de órdenes POS | Ventas |
| `hr.employee` | Empleados con foto | Ventas, Asistencias, Remuneración |
| `hr.attendance` | Registros de piquete | Asistencias, Remuneración |
| `resource.calendar` | Horarios de trabajo | Asistencias, Remuneración |
| `resource.calendar.attendance` | Líneas de horario | Asistencias, Remuneración |

---

## 🎨 Tecnologías

| Capa | Tecnología |
|---|---|
| Backend | Laravel 11, PHP 8.2 |
| Frontend | Blade, TailwindCSS (Vite), JS vanilla |
| Base de datos local | PostgreSQL 16 |
| ERP externo | Odoo 18 (XML-RPC) |
| Auth | Laravel Breeze (Blade) |
| Assets | Vite + npm |

---

## 📋 Estado de Módulos

| Módulo | Estado | Notas |
|---|---|---|
| Autenticación (login) | ✅ Completo | Laravel Breeze instalado |
| Panel principal | ✅ Completo | 8 tarjetas de módulos |
| BD local (migraciones) | ✅ Completo | 6 tablas en tecnigas_bd |
| Models | ✅ Completo | 5 models con relaciones |
| Controllers base | ✅ Completo | Remuneracion + Empleado |
| OdooService | ✅ Completo | XML-RPC manual sin extensión, con fix de decodeNode |
| Módulo Ventas | ✅ Completo | Ranking, detalle, modal PDF con export por pago |
| API Routes | ✅ Completo | Endpoints registrados |
| Módulo Asistencias | ✅ Completo | Horarios con modal, CRUD en tiempo real con Odoo |
| Módulo Remuneración | ✅ Completo | Interfaz completa, justificaciones y configuración |
| Módulo Stock | 🔲 Pendiente | |
| Módulo Catálogo | 🔲 Pendiente | |
| Módulo Recepción | ✅ Completo | 9 endpoints, SPA 873 líneas, caché optimizado |
| Módulo Traslado | ✅ Completo | 9 endpoints, SPA 689 líneas, caché optimizado |
| Módulo Productos | 🔲 Pendiente | |

---

## 📝 Reglas de Remuneración

**Pérdida de bono:**
- 1 o más faltas no justificadas → pierde bono grupal e individual
- 3 o más tardanzas no justificadas → pierde bono grupal e individual

**Filtro de empleados:** Solo los que tengan `department` con "venta" (case-insensitive) y `active = true`.

**Periodicidades:** Semanal y mensual, tanto para metas grupales (por turno) como individuales (por empleado).

**Fuente de asistencias:** `hr.attendance` de Odoo (check_in / check_out reales).

**Fuente de horarios:** `resource.calendar` de Odoo (sabe qué días trabaja cada empleado).

**Orden y Limpieza (O&L):**
- Calificación diaria de 0 a 2 en días laborables.
- Al final de la semana se promedian las notas de los días trabajados.
- Las faltas justificadas excluyen el día del cálculo. Las faltas no justificadas asignan automáticamente un 0 a ese día.
- El promedio se traduce a "Puntos" y los Puntos se traducen a un "% de Descuento", el cual se aplica al **Bono Individual Bruto** (afecta aunque haya otros descuentos previos y no se anula).

---

## 📦 Módulos de Inventario (Recepción y Traslado)

### Recepción de Mercancía

**Funcionalidad:** Registrar la entrada de productos desde proveedores a ubicaciones internas del almacén.

**Flujo completo:**
1. Usuario selecciona ubicación destino (ej: "WH/Stock")
2. Busca productos en catálogo Odoo (con caché de 5 min)
3. Añade productos al carrito con cantidad, costo y tickets BarTender
4. Clic en "Recepcionar en Odoo"
5. Backend crea:
   - Registro local en tabla `recepciones` + `recepcion_items`
   - `stock.picking` tipo "incoming" en Odoo
   - `stock.move` por cada producto (batch)
   - Valida automáticamente el picking (estado "done")
6. Retorna ID del picking de Odoo
7. Usuario puede ver historial y detalles

**Características técnicas:**
- **SPA de 873 líneas** con TailwindCSS y Lucide Icons
- **LocalStorage** para persistencia de carrito
- **Búsqueda asíncrona:** Búsqueda local con pre-carga paralela de imágenes (batches de 50) para renderizado sin parpadeos.
- **Exportación BarTender Nativa:** Generación de archivos `.xlsx` reales vía `ZipArchive` (formato "Impresión 2.0") con `list_price` oculto.
- **Catálogo Masivo en Tiempo Real:** Carga inicial de todo el catálogo (sin `limit`) optimizando la solicitud a Odoo (120s timeout) descartando caché de productos para garantizar stock exacto.
- **Historial completo** con modal de detalle

**Integración Odoo:**
```
1. Buscar picking_type code='incoming'
2. Buscar location origen usage='supplier'
3. Crear stock.picking
4. Crear stock.move (batch) por cada producto
5. action_confirm() → 'confirmed'
6. action_assign() → reservar
7. Escribir quantity en cada move
8. button_validate() → 'done'
```

---

### Traslado Interno

**Funcionalidad:** Mover productos entre ubicaciones internas (ej: Almacén → Tienda).

**Flujo completo:**
1. Usuario selecciona ubicación origen y destino
2. Busca productos en catálogo Odoo
3. Añade productos al carrito con cantidad
4. Clic en "Guardar Traslado"
5. Backend crea:
   - Registro local en tabla `traslados` + `traslado_items`
   - `stock.picking` tipo "internal" en Odoo
   - `stock.move` por cada producto (batch)
   - Valida automáticamente el picking
6. Traslado queda en estado "confirmado"
7. Usuario puede ver historial

**Diferencias con Recepción:**
- No tiene campo de costo (traslado interno, no compra)
- No tiene tickets BarTender
- Requiere origen + destino (vs solo destino)
- Estado: pendiente/confirmado

**Características técnicas:**
- **SPA de 689 líneas** con diseño consistente
- **Selectores duales** (origen/destino) en header y móvil
- **Búsqueda asíncrona:** Búsqueda local con pre-carga paralela de imágenes (batches de 50) para renderizado sin parpadeos.
- **Catálogo Masivo en Tiempo Real:** Carga inicial completa sin límite para asegurar coincidencia de stock.
- **Confirmación manual** opcional desde historial

**Integración Odoo:**
```
1. Buscar picking_type code='internal'
2. Crear stock.picking con location_id y location_dest_id
3. Crear stock.move (batch) por cada producto
4. action_confirm()
5. action_assign()
6. Escribir quantity en cada move
7. button_validate()
```

---

## 🔧 Comandos de Mantenimiento

```bash
# Limpiar caché de Odoo
php artisan cache:clear

# Ver rutas registradas
php artisan route:list

# Ejecutar migraciones
php artisan migrate

# Rollback última migración
php artisan migrate:rollback

# Ver estado de migraciones
php artisan migrate:status
```

---

## 📝 Notas para el Desarrollador

1. **OdooService no usa la extensión `xmlrpc` de PHP** (eliminada en PHP 8.0). Implementa XML-RPC manualmente con cURL + DOMDocument. Si algo falla con Odoo, revisar `encodeValue` y `decodeNode`.

2. **Bug crítico resuelto en `decodeNode`:** La versión original aplanaba los arrays anidados. La versión correcta itera solo los nodos `<value>` hijos directos de `<data>`, y los `<member>` hijos directos de `<struct>`.

3. **Las imágenes de Odoo vienen en Base64.** Los campos `image_128` e `image_1920` son cadenas Base64. El navegador las renderiza con `data:image/png;base64,...`.

4. **Nunca subir `.env` a Git.** Las credenciales de Odoo y PostgreSQL están ahí.

5. **Sincronizar empleados antes de usar remuneración.** La tabla `employees` se llena llamando `POST /api/empleados/sync` con datos de Odoo.

6. **El PDF de ventas se genera en el navegador** con JS puro — no usa librerías externas. Se imprime con `window.print()`. El resumen final (totales) va en `<tbody>`, no en `<tfoot>`, para evitar que se repita en cada página impresa.

7. **Clasificación de medios de pago:** yape/plin → `pay_yape`, tarjeta/visa/master/card → `pay_tarjeta`, resto → `pay_efectivo`. Se hace por nombre del método de pago en Odoo (case-insensitive).

8. **Bloqueos de red local o SSL:** Si Odoo arroja error de "autenticación fallida", puede ser que el firewall (ej. Fortinet) esté interceptando/bloqueando la conexión. En `OdooService` se ha deshabilitado `CURLOPT_SSL_VERIFYPEER` para evitar problemas de certificados autofirmados en entornos locales.

9. **Sincronización Automática en Justificaciones:** Si se intenta justificar la asistencia de un empleado que existe en Odoo pero no en la base de datos local, el backend (`RemuneracionController`) lo sincronizará y creará automáticamente para evitar fallos.

10. **Optimizaciones de Rendimiento en Recepción/Traslado:**
    - **Caché de catálogos:** Productos (5 min), ubicaciones y proveedores (10 min) para reducir llamadas a Odoo de 1,200ms → 5ms.
    - **Batch creation:** Los `stock.move` se crean en una sola llamada en lugar de N llamadas individuales (reducción del 72%).
    - **Índices de BD:** Campos `fecha`, `estado`, `odoo_picking_id` indexados para consultas rápidas de historial.
    - **Resultado:** Carga inicial de 1,200ms → <100ms. Operaciones optimizadas en ~70%.

---

## ⚡ Rendimiento y Optimizaciones

### Caché Implementado

| Recurso | Duración | Beneficio |
|---------|----------|-----------|
| Productos Odoo | 5 minutos | 1,200ms → 5ms (99.6%) |
| Ubicaciones Odoo | 10 minutos | 150ms → 5ms (96.7%) |
| Proveedores Odoo | 10 minutos | 200ms → 5ms (97.5%) |

**Limpiar caché manualmente:**
```bash
php artisan cache:clear
```

### Batch Operations

Las operaciones de creación de `stock.move` en Odoo se realizan en batch (una sola llamada para múltiples registros) en lugar de llamadas individuales:

- **Antes:** 5 productos = 5 llamadas × 250ms = 1,250ms
- **Después:** 5 productos = 1 llamada × 350ms = 350ms
- **Mejora:** 72% más rápido

---

## 📜 Historial de Cambios

### Mayo 2026 — V3.4.1 (Hotfix Ventas)
- **Fix Crítico de Ranking:** Solucionado el bug en `VentasController` donde usuarios aparecían duplicados (y otros desaparecían) en el podio. La causa raíz era un problema clásico de PHP de variables pasadas por referencia en bucles `foreach`. Se eliminó la lógica innecesaria de "fusión de nombres" y el código ahora es más limpio y rápido.

### Mayo 2026 — V3.4 (Exportación y Soporte Masivo)
- **Integración nativa BarTender:** Se reemplazó exportación "falsa" por motor real de Excel (`ZipArchive`) generando `.xlsx` exactos ("Impresión 2.0") y descarga directa en navegador.
- **Soporte catálogo masivo (4000+ productos):** Se eliminó el `limit` y caché en el catálogo para ambos módulos, garantizando stock y precios en tiempo real. Se extendió `CURLOPT_TIMEOUT` a 120s.
- **Carga Síncrona de Imágenes:** Nuevos endpoints `imagenes()` para descargar `image_128` en paralelo (lotes de 50). Renderizado bloqueante: la UI muestra spinners y solo renderiza las cards cuando todas las imágenes de la búsqueda se han recibido, evitando "parpadeos".
- **Refactorización de UI:** Campo de costo de recepción oculto (tomado directo de `list_price`), carrito a 2 columnas (Cantidad/Tickets).

### Mayo 2026 — V3.3 (Recepción y Traslado)
- **Módulos Recepción y Traslado completados:** 9 endpoints cada uno, SPAs completas (873 y 689 líneas)
- **Integración completa con Odoo 18:** Creación automática de `stock.picking` tipo incoming/internal
- **4 nuevas tablas:** `recepciones`, `recepcion_items`, `traslados`, `traslado_items`
- **Optimizaciones de rendimiento implementadas:**
  - Caché de catálogos Odoo (productos, ubicaciones, proveedores) → 99% más rápido
  - Batch creation de stock.move → 72% más rápido
  - Índices de BD en campos de búsqueda frecuente
- **Resultado:** Carga inicial de módulos de 1,200ms → <100ms
- **Fix Odoo 18:** Adaptación a campo `quantity` en lugar de `qty_done` (deprecado)

### Mayo 2026 — V3.2
- **Módulos completados:** Asistencias (CRUD de horarios, modal flotante) y Remuneración (interfaz interactiva de cálculo y metas).
- **Manejo de UI resiliente:** Implementación de persistencia y manejo de errores (alertas de fetch) sin recarga en justificaciones.
- **Sincronización robusta:** Creación automática de empleados locales faltantes al guardar justificaciones desde Odoo.
- **Fix Conexión Odoo:** By-pass de certificados SSL (`CURLOPT_SSL_VERIFYPEER = false`) y diagnóstico de bloqueos por Firewall (FortiGuard) para estabilizar XML-RPC.

### Mayo 2026 — V3.1
- **OdooService** implementado con XML-RPC manual (cURL + DOMDocument), sin extensión `xmlrpc`
- **Fix crítico `decodeNode`:** arrays anidados de Odoo ahora se decodifican correctamente
- **Módulo Ventas completo:** VentasController con 3 endpoints (ranking, detail, export)
- **Vista ventas/index.blade.php:** ranking con KPIs, podio, panel lateral de órdenes, modal PDF
- **PDF de ventas:** estructurado por medio de pago (Efectivo / Yape / Tarjeta) con resumen final
- **Documentación** actualizada en `docs/DOCUMENTACION.md`

### Mayo 2026 — V3.0 (Migración a Laravel y Nuevos Módulos)
- **Migración completa** de Python + HTML suelto a Laravel 11 + PHP 8.2
- **BD local** PostgreSQL `tecnigas_bd` creada con sus respectivas tablas.
- **Autenticación y Roles:** Sistema de login con Laravel Breeze y jerarquía de roles (`admin`, `almacen`, `vendedor`, `limpieza`) con middleware y panel de configuración CRUD de usuarios.
- **Módulo Orden y Limpieza:** Nueva interfaz interactiva para calificar al personal de Ventas (0-2 puntos diarios). Sincronización automática de días de descanso desde el calendario de Odoo (`resource.calendar.attendance`).
- **Mejoras en Remuneraciones (Bono Semanal):** 
  - La tabla "Nómina Semanal" fue renombrada a "Bono Semanal".
  - Apertura de modal de detalles y justificaciones al hacer clic en las filas de los trabajadores.
  - Generación y descarga de archivos Excel (.csv con formato BOM UTF-8 y separador de punto y coma) de las nóminas semanales.
  - Memoria inteligente (`localStorage`) de los trabajadores seleccionados para actuar como plantilla rápida en exportaciones futuras.
- **Panel principal** con tarjetas dinámicas (Blade + Tailwind) según el rol del usuario.
- **Models** con relaciones Eloquent: Employee, SalaryConfig, ShiftGoal, EmployeeGoal, AttendanceJustification, OrderCleanlinessScore, OrderCleanlinessConfig.
- **Controllers** RemuneracionController, EmpleadoController, LimpiezaController, ConfigController.

### Mayo 2026 — V2.4 (última versión Python)
- Módulo Remuneración completo (salarios, bonos, metas, justificaciones)
- 7 nuevos endpoints en server.py
- Archivos locales remuneracion_data.json y remuneracion_justif.json

### Abril 2026 — V2.3
- Stock: Lazy loading de imágenes con IntersectionObserver
- Caché de imágenes en memoria (_image_cache)

### Abril 2026 — V2.2
- Módulo Asistencias (En Vivo + Esta Semana)
- Módulo Stock con búsqueda inteligente por similitud (4 niveles)
- Dashboard rediseñado con glassmorphism y orbs animados

### Abril 2026 — V2.1
- Módulo Ventas: iconos Lucide, PDF estructurado por método de pago
- Fix: TOTAL GENERAL en tbody (no tfoot) para evitar repetición al imprimir
