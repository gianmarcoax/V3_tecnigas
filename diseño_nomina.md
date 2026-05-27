# 💡 Diseño — Módulo Nómina & Bonos Mensuales

## 1. Mejora inmediata — Modal de Justificación

**Problema actual:** el campo de motivo es un `<input>` tiny inline.

**Solución:** al clic en "Justificar" se abre un modal flotante con:
```
┌────────────────────────────────┐
│  Justificar asistencia         │
│  Juan Pérez — Lun 12/05        │
│  Tipo: Falta / Tardanza (X min)│
│                                │
│  Motivo:                       │
│  ┌──────────────────────────┐  │
│  │ (textarea 3 líneas)      │  │
│  └──────────────────────────┘  │
│                                │
│       [Cancelar]  [Justificar] │
└────────────────────────────────┘
```
- Si ya está justificada, aparece el motivo guardado con opción "Quitar justificación"
- Al guardar, refresca el detalle de asistencia

---

## 2. Estados correctos — Semana en curso

**Problema actual:** días futuros de la semana actual se marcan como "Falta".

**Regla propuesta:**

| Condición | Estado mostrado |
|---|---|
| Día futuro (aún no llegó) | `en_curso` → "Pendiente" (azul) |
| Día de hoy, antes de la hora de entrada | `en_curso` → "Pendiente" |
| Día de hoy, después de la hora + tolerancia + sin registro | `falta` (rojo) |
| Semana pasada, sin registro | `falta` (rojo) |
| Registró entrada | `puntual` o `tardanza` |
| Día libre según calendario Odoo | `dia_libre` (gris) |

> El backend ya tiene `$isToday` y `$nowMin >= $ti`. La mejora es que los días **futuros** (no solo el de hoy) queden como `pendiente`, no como `falta`.

---

## 3. Panel de Nómina Mensual (nuevo tab)

### Lógica de semanas por mes

Una semana (Lun–Dom) **pertenece al mes donde cae su domingo**.

**Ejemplo Mayo 2026:**
| # | Rango | Domingo pertenece a |
|---|---|---|
| S1 | 27 Abr – 3 May | **Mayo** (Dom 3/05) |
| S2 | 4 May – 10 May | Mayo |
| S3 | 11 May – 17 May | Mayo (semana actual) |
| S4 | 18 May – 24 May | Mayo |
| S5 | 25 May – 31 May | Mayo (Dom 31/05) |

Si la semana siguiente fuera 1–7 Jun → pertenece a **Junio**.

> **Día de pago:** 1º de cada mes (Mayo se paga el 1 de Junio).

---

### Estructura del panel

```
┌─ Tabs ────────────────────────────────────────────────────┐
│  Resumen │ Configuración │ [Nómina Mensual]               │
└───────────────────────────────────────────────────────────┘

┌─ Selector de mes ─────────────────────────────────────────┐
│  ◀  Mayo 2026  ▶        Pago: 1 Jun 2026                  │
└───────────────────────────────────────────────────────────┘

┌─ Semanas del mes ─────────────────────────────────────────┐
│  [S1: 27 Abr – 3 May]  [S2: 4-10]  [S3: 11-17 ●]  ...   │
│   ● = semana actual                                        │
└───────────────────────────────────────────────────────────┘

┌─ Tabla de empleados ──────────────────────────────────────┐
│  Empleado  │ Bono S1 │ S2 │ S3 │ S4 │ S5 │ Total bonos  │
│            │ ✓ entregado / S/ X pendiente / — sin bono    │
│  Juan      │  ✓ 50  │ 80 │ 60 │ —  │ —  │  S/ 190      │
│  Ana       │   —    │ —  │ 30 │ —  │ —  │  S/ 30       │
└───────────────────────────────────────────────────────────┘

┌─ Acciones por semana (al seleccionar semana) ─────────────┐
│  Semana S2: 4–10 Mayo                                      │
│  [Marcar todos como entregado]                             │
│  Juan Pérez   S/ 80  [✓ Marcar entregado] / [Ya entregado]│
│  Ana García   Sin bono esta semana                         │
└───────────────────────────────────────────────────────────┘
```

---

### Reporte del día 1 (cierre mensual)

Al llegar al día de pago (o consultando un mes pasado), aparece un resumen:

```
┌─ Reporte Mayo → Pago 1 Jun ───────────────────────────────┐
│ Empleado  │ Sueldo neto │ Bonos pend │ Desc. │ TOTAL     │
│ Juan      │   S/ 1,044  │   S/ 190   │ -S/40 │ S/ 1,194  │
│ Ana       │   S/ 870    │   S/ 30    │  —    │ S/ 900    │
│                              NÓMINA TOTAL: S/ 2,094       │
│                                                            │
│ [Exportar PDF]  [Cerrar mes]                               │
└───────────────────────────────────────────────────────────┘
```

- **Sueldo neto** = `(base × 0.87) + bono_fijo`
- **Bonos pend** = suma de bonos semanales **no marcados como entregados**
- **Desc.** = descuentos por faltas/tardanzas del mes

> Si un bono **ya fue entregado** (marcado ✓), **no aparece** en el total del día 1 (ya se pagó).

---

## 4. Tab Historial por Empleado (modal/panel)

Al clic en una card de empleado en el Resumen, o desde Nómina Mensual:

```
┌─ Juan Pérez — Historial Mayo 2026 ────────────────────────┐
│  [← Semana anterior]  Sem 11–17 Mayo  [Semana siguiente →]│
│                                                            │
│  Lun 11/05  09:02 → 13:05  Esperado 08:45  [Puntual ✓]  │
│  Mar 12/05  —              Esperado 08:45  [Falta] [Justif]│
│  Mié 13/05  08:55 → 13:10  Esperado 08:45  [Puntual ✓]  │
│  Jue 14/05  09:18 → 13:02  Esperado 08:45  [Tardanza 33m]│
│             → [Justificar tardanza]                        │
│  Vie 15/05  Pendiente (hoy)                               │
│  Sáb 16/05  Día libre                                      │
│  Dom 17/05  Día libre                                      │
│                                                            │
│  Bono esta semana: S/ 80 (si llega a meta)                │
│  Descuentos: S/ 40 (1 falta)                              │
└───────────────────────────────────────────────────────────┘
```

El botón "Justificar" abre el **modal de justificación** (punto 1).

---

## 5. BD propuesta (nuevas tablas)

### `monthly_bonus_deliveries`
Registra si el bono semanal de un empleado fue entregado.

| Campo | Tipo | Descripción |
|---|---|---|
| id | bigint PK | |
| employee_local_id | FK → employees | |
| week_start | date | Lunes de la semana |
| amount | decimal(10,2) | Bono entregado |
| delivered | boolean | true = ya se pagó |
| delivered_at | timestamp nullable | Cuándo se marcó |
| delivered_by | FK → users | Quién lo marcó |
| timestamps | | |
> Unique: `(employee_local_id, week_start)`

### `monthly_payroll_closes`
Cierre mensual (cuando se hace el pago del día 1).

| Campo | Tipo | Descripción |
|---|---|---|
| id | bigint PK | |
| year | smallint | |
| month | tinyint | 1–12 |
| closed_at | timestamp nullable | Fecha de cierre |
| closed_by | FK → users | |
| timestamps | | |
> Unique: `(year, month)`

---

## 6. Endpoints nuevos propuestos

| Método | Ruta | Descripción |
|---|---|---|
| GET | `/api/nomina/mes?year=&month=` | Semanas del mes + estado de bonos por empleado |
| POST | `/api/nomina/entrega` | Marcar/desmarcar bono entregado de una semana |
| GET | `/api/nomina/reporte?year=&month=` | Reporte del día de pago |
| POST | `/api/nomina/cerrar` | Cerrar el mes (marcar pagado) |
| GET | `/api/empleado/historial?emp_id=&week_start=` | Historial semanal individual |

---

## ✅ Orden de implementación sugerido

1. **Fix rápido** — Modal de justificación (1h)
2. **Fix rápido** — Días futuros como `pendiente` en backend (30min)
3. **Migraciones** — Crear las 2 tablas nuevas (30min)
4. **Backend** — Endpoints de nómina mensual (2–3h)
5. **Vista** — Tab "Nómina Mensual" con tabla de semanas (2–3h)
6. **Vista** — Panel historial individual + modal justificación (1–2h)

---

> [!IMPORTANT]
> **Punto de decisión:** ¿El bono que "ya fue entregado" desaparece del total del día 1 (ya pagado)? ¿O se muestra siempre en el reporte aunque esté pagado, marcado en gris? Confirmar antes de implementar el reporte.

> [!NOTE]
> Las plantillas de bonos (`rem_plantillas`) actualmente se guardan en `localStorage`. Si quieres que persistan en BD (para compartirlas entre usuarios del sistema), habría que añadir una tabla `bonus_templates`. ¿Lo hacemos?
