<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Remuneración</h2>
    </x-slot>

    <div class="py-6 px-4 max-w-7xl mx-auto">

        {{-- Nav semana + tabs principales --}}
        <div class="bg-white rounded-2xl shadow p-4 mb-6 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <button onclick="cambiarSemana(-1)" class="p-2 rounded-lg hover:bg-gray-100 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <span id="lblSemana" class="font-semibold text-gray-800 text-sm min-w-52 text-center"></span>
                <button onclick="cambiarSemana(1)" class="p-2 rounded-lg hover:bg-gray-100 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
            <div class="flex gap-2">
                <button onclick="switchMainTab(0,this)" id="mtab0" class="tab-btn tab-active">Resumen</button>
                <button onclick="switchMainTab(1,this)" id="mtab1" class="tab-btn tab-inactive">Configuración</button>
                <button onclick="switchMainTab(2,this)" id="mtab2" class="tab-btn tab-inactive">Nómina Mensual</button>
                <button onclick="switchMainTab(3,this)" id="mtab3" class="tab-btn tab-inactive">Nómina Semanal</button>
                <button onclick="switchMainTab(4,this)" id="mtab4" class="tab-btn tab-inactive">Orden y Limpieza</button>
            </div>
        </div>

        {{-- ═══ TAB RESUMEN ═══ --}}
        <div id="mpanel0">
            <div id="loadingResumen" class="hidden text-center py-16 text-gray-400">
                <svg class="animate-spin h-8 w-8 mx-auto mb-3 text-violet-500" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z" />
                </svg>
                Calculando remuneración...
            </div>
            <div id="empCards" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4"></div>
        </div>

        {{-- ═══ TAB CONFIGURACIÓN ═══ --}}
        <div id="mpanel1" class="hidden">

            {{-- Sub-tabs configuración --}}
            <div class="bg-white rounded-2xl shadow mb-6 flex overflow-hidden">
                <button onclick="switchCfgTab(0,this)" id="ctab0"
                    class="flex-1 py-3 text-sm font-medium transition bg-violet-600 text-white">Sueldo</button>
                <button onclick="switchCfgTab(1,this)" id="ctab1"
                    class="flex-1 py-3 text-sm font-medium transition text-gray-600 hover:bg-gray-50">Bono</button>
                <button onclick="switchCfgTab(2,this)" id="ctab2"
                    class="flex-1 py-3 text-sm font-medium transition text-gray-600 hover:bg-gray-50">Descuento</button>
                <button onclick="switchCfgTab(3,this)" id="ctab3"
                    class="flex-1 py-3 text-sm font-medium transition text-gray-600 hover:bg-gray-50">Orden y Limpieza</button>
            </div>

            {{-- ── Sub-tab Sueldo ── --}}
            <div id="cpanel0">
                <div class="bg-white rounded-2xl shadow overflow-hidden">
                    <div class="p-4 border-b">
                        <h3 class="font-bold text-gray-800">Configuración de Sueldo</h3>
                        <p class="text-xs text-gray-400 mt-1">El AFP (13%) se descuenta del sueldo base. El bono fijo se
                            suma después del AFP.</p>
                    </div>
                    <div id="sueldoList" class="divide-y">
                        <p class="p-6 text-center text-gray-400 text-sm">Cargando empleados...</p>
                    </div>
                </div>
            </div>

            {{-- ── Sub-tab Bono ── --}}
            <div id="cpanel1" class="hidden space-y-6">

                {{-- Plantillas --}}
                <div class="bg-white rounded-2xl shadow p-5">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-gray-800">Plantillas de Bono</h3>
                        <button onclick="abrirModalPlantilla(null)"
                            class="bg-violet-600 hover:bg-violet-700 text-white px-3 py-1.5 rounded-lg text-sm font-medium transition">
                            + Nueva plantilla
                        </button>
                    </div>
                    <div id="plantillasList" class="space-y-2">
                        <p class="text-center text-gray-400 text-sm py-4">Sin plantillas aún</p>
                    </div>
                </div>

                {{-- Asignación por empleado --}}
                <div class="bg-white rounded-2xl shadow overflow-hidden">
                    <div class="p-4 border-b">
                        <h3 class="font-bold text-gray-800">Bonos por Empleado</h3>
                        <p class="text-xs text-gray-400 mt-1">Selecciona un empleado para asignar o editar sus niveles
                            de bono.</p>
                    </div>
                    <div id="bonoEmpList" class="divide-y">
                        <p class="p-6 text-center text-gray-400 text-sm">Cargando empleados...</p>
                    </div>
                </div>
            </div>

            {{-- ── Sub-tab Descuento ── --}}
            <div id="cpanel2" class="hidden">
                <div class="bg-white rounded-2xl shadow p-6 max-w-md">
                    <h3 class="font-bold text-gray-800 mb-4">Descuento por Tardanza</h3>
                    <p class="text-xs text-gray-500 mb-4">Si no se configura un descuento, las tardanzas solo afectan al
                        bono (3 o más = pierde bono semanal) pero no generan descuento monetario.</p>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Tolerancia en minutos</label>
                            <input id="cfgTolerance" type="number" min="0" max="60" class="inp w-32" value="10">
                            <p class="text-xs text-gray-400 mt-1">Minutos de gracia antes de marcar tardanza</p>
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Descuento fijo por tardanza (S/) —
                                opcional</label>
                            <input id="cfgDeduction" type="number" min="0" step="0.50" class="inp w-40"
                                placeholder="Sin descuento">
                            <p class="text-xs text-gray-400 mt-1">Dejar vacío para no aplicar descuento monetario</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <button onclick="guardarDescuento()"
                                class="bg-violet-600 hover:bg-violet-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">
                                Guardar
                            </button>
                            <p id="msgDescuento" class="text-xs text-green-600 hidden">Guardado correctamente</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ── Sub-tab Orden y Limpieza ── --}}
            <div id="cpanel3" class="hidden space-y-6">
                <div class="bg-white rounded-2xl shadow p-6">
                    <h3 class="font-bold text-gray-800 mb-1">Configuración — Orden y Limpieza</h3>
                    <p class="text-xs text-gray-500 mb-5">La calificación diaria (0–2) se promedia al final de la semana sobre los días laborables. Los días con falta justificada se excluyen del promedio.</p>

                    {{-- Rangos de calificación --}}
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-semibold text-gray-700">Rangos de calificación → Puntos</h4>
                            <button onclick="addOlThreshold()" class="text-xs text-violet-600 hover:text-violet-800 font-medium">+ Agregar rango</button>
                        </div>
                        <p class="text-xs text-gray-400 mb-3">Define a qué rango de promedio corresponde cada puntaje entero.</p>
                        <table class="w-full text-sm max-w-lg">
                            <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs text-gray-500">Desde</th>
                                    <th class="px-3 py-2 text-left text-xs text-gray-500">Hasta</th>
                                    <th class="px-3 py-2 text-left text-xs text-gray-500">Puntos</th>
                                    <th class="px-3 py-2"></th>
                                </tr>
                            </thead>
                            <tbody id="olThresholdsBody"></tbody>
                        </table>
                    </div>

                    {{-- Descuentos por puntaje --}}
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-semibold text-gray-700">Descuento al bono por puntaje O&L</h4>
                            <button onclick="addOlDiscountRule()" class="text-xs text-violet-600 hover:text-violet-800 font-medium">+ Agregar regla</button>
                        </div>
                        <p class="text-xs text-gray-400 mb-3">Define qué % del bono de ventas se descuenta según el puntaje promedio obtenido.</p>
                        <table class="w-full text-sm max-w-lg">
                            <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs text-gray-500">Puntos O&L</th>
                                    <th class="px-3 py-2 text-left text-xs text-gray-500">% Descuento del bono</th>
                                    <th class="px-3 py-2"></th>
                                </tr>
                            </thead>
                            <tbody id="olDiscountBody"></tbody>
                        </table>
                    </div>

                    <div class="flex items-center gap-3">
                        <button onclick="guardarOlConfig()"
                            class="bg-violet-600 hover:bg-violet-700 text-white px-5 py-2 rounded-lg text-sm font-semibold transition">
                            Guardar configuración
                        </button>
                        <p id="msgOlConfig" class="text-xs text-green-600 hidden">Guardado correctamente</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══ TAB NÓMINA MENSUAL ═══ --}}
        <div id="mpanel2" class="hidden">
            <div class="bg-white rounded-2xl shadow p-4 mb-4 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <button onclick="cambiarMes(-1)" class="p-2 rounded-lg hover:bg-gray-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <span id="lblMes" class="font-semibold text-gray-800 text-sm min-w-36 text-center"></span>
                    <button onclick="cambiarMes(1)" class="p-2 rounded-lg hover:bg-gray-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
                <span id="lblPayDate" class="text-xs text-violet-600 font-medium"></span>
            </div>

            <div class="bg-white rounded-2xl shadow p-4 mb-4">
                <div id="semanaTabs" class="flex flex-wrap gap-2 mb-4"></div>
                <div id="nominaSemanaCont">
                    <p class="text-center text-gray-400 text-sm py-6">Selecciona una semana</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl shadow overflow-hidden">
                <div class="p-4 border-b flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-gray-800">Reporte de Pago</h3>
                        <p id="lblReportePago" class="text-xs text-gray-400 mt-0.5"></p>
                    </div>
                    <span class="text-xs bg-violet-100 text-violet-700 px-2 py-1 rounded-full font-medium">Día 1</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs text-gray-500">Empleado</th>
                                <th class="px-4 py-3 text-right text-xs text-gray-500">Sueldo neto</th>
                                <th class="px-4 py-3 text-right text-xs text-gray-500">Bonos entregados</th>
                                <th class="px-4 py-3 text-right text-xs text-gray-500">Bonos pendientes</th>
                                <th class="px-4 py-3 text-right text-xs text-gray-500 font-bold">TOTAL día 1</th>
                            </tr>
                        </thead>
                        <tbody id="nominaReporteBody">
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-gray-400 text-sm">Cargando...</td>
                            </tr>
                        </tbody>
                        <tfoot id="nominaReporteFoot" class="border-t bg-gray-50"></tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- ═══ TAB NÓMINA SEMANAL ═══ --}}
        <div id="mpanel3" class="hidden">
            <div class="bg-white rounded-2xl shadow p-4 mb-4 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <button onclick="cambiarSemanaSlip(-1)" class="p-2 rounded-lg hover:bg-gray-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <span id="lblSemanaSlip" class="font-semibold text-gray-800 text-sm min-w-52 text-center"></span>
                    <button onclick="cambiarSemanaSlip(1)" class="p-2 rounded-lg hover:bg-gray-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
                <button onclick="cargarNominaSemanal()" class="text-xs bg-violet-100 text-violet-700 hover:bg-violet-200 px-3 py-1.5 rounded-lg font-medium transition">
                    Actualizar
                </button>
            </div>
            <div id="nominaSemanalCont">
                <p class="text-center text-gray-400 text-sm py-10">Cargando nómina semanal...</p>
            </div>
        </div>

        {{-- ═══ TAB ORDEN Y LIMPIEZA ═══ --}}
        <div id="mpanel4" class="hidden">
            <div class="bg-white rounded-2xl shadow p-4 mb-4 flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <button onclick="cambiarSemanaOL(-1)" class="p-2 rounded-lg hover:bg-gray-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                    </button>
                    <span id="lblSemanaOL" class="font-semibold text-gray-800 text-sm min-w-52 text-center"></span>
                    <button onclick="cambiarSemanaOL(1)" class="p-2 rounded-lg hover:bg-gray-100 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </button>
                </div>
                <div class="flex items-center gap-2">
                    <button id="btnGuardarOL" onclick="guardarGridOL()" class="text-xs bg-violet-600 text-white hover:bg-violet-700 px-4 py-2 rounded-lg font-medium transition shadow">
                        Guardar Calificaciones
                    </button>
                    <button onclick="cargarPanelOL()" class="text-xs bg-violet-100 text-violet-700 hover:bg-violet-200 px-3 py-2 rounded-lg font-medium transition">
                        Actualizar
                    </button>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow p-5">
                <h3 class="font-bold text-gray-800 mb-2">Calificación Diaria</h3>
                <p class="text-xs text-gray-500 mb-4">Ingresa el puntaje (0 a 2) por día laborable. Al terminar, haz clic en "Guardar Calificaciones". Los días libres se marcan con un guion.</p>
                <div id="olGridCont" class="overflow-x-auto">
                    <p class="text-center text-gray-400 py-8 text-sm">Cargando...</p>
                </div>
            </div>
        </div>

    </div>

    {{-- ═══ MODAL JUSTIFICACIÓN ═══ --}}
    <div id="modalJustif" class="hidden fixed inset-0 bg-black/60 z-[70] flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md">
            <div class="flex items-center justify-between p-4 border-b">
                <div>
                    <h3 class="font-bold text-gray-800 text-sm" id="justifModalTitle">Justificar asistencia</h3>
                    <p class="text-xs text-gray-400 mt-0.5" id="justifModalSub"></p>
                </div>
                <button onclick="cerrarModalJustif()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-5 space-y-4">
                <input type="hidden" id="justifEmpId">
                <input type="hidden" id="justifDate">
                <input type="hidden" id="justifType">
                <input type="hidden" id="justifWeekStart">
                <div id="justifCurrentBlock" class="hidden bg-green-50 border border-green-200 rounded-xl p-3">
                    <p class="text-xs font-medium text-green-700 mb-1">Ya justificado:</p>
                    <p id="justifCurrentReason" class="text-sm text-green-800"></p>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Motivo de justificación</label>
                    <textarea id="justifReason" rows="3" class="inp w-full resize-none"
                        placeholder="Ej: Permiso por cita médica, emergencia familiar..."></textarea>
                </div>
                <div class="flex gap-2">
                    <button onclick="cerrarModalJustif()"
                        class="flex-1 border border-gray-200 text-gray-600 py-2 rounded-xl text-sm font-medium hover:bg-gray-50 transition">Cancelar</button>
                    <button id="justifBtnConfirmar" onclick="confirmarJustif(true)"
                        class="flex-1 bg-green-500 hover:bg-green-600 text-white py-2 rounded-xl text-sm font-semibold transition">Justificar</button>
                </div>
                <div id="justifQuitarBlock" class="hidden">
                    <button onclick="confirmarJustif(false)"
                        class="w-full border border-red-300 text-red-500 hover:bg-red-50 py-2 rounded-xl text-sm font-medium transition">Quitar
                        justificación</button>
                </div>
            </div>
        </div>
    </div>


    <div id="panelSueldo" class="hidden fixed inset-y-0 right-0 w-full sm:w-96 bg-white shadow-2xl z-40 flex flex-col">
        <div class="flex items-center justify-between p-4 border-b">
            <div>
                <h3 id="sueldoPanelName" class="font-bold text-gray-800 text-sm"></h3>
                <p id="sueldoPanelJob" class="text-xs text-gray-400"></p>
            </div>
            <button onclick="cerrarPanelSueldo()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-5 space-y-4">
            <input type="hidden" id="sueldoEmpId">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Sueldo base (S/)</label>
                <input id="sueldoBase" type="number" min="0" step="10" class="inp w-full" placeholder="0">
                <p class="text-xs text-gray-400 mt-1">AFP (13%): <span id="sueldoAFP"
                        class="font-semibold text-red-500">S/ 0.00</span> — Neto tras AFP: <span id="sueldoNeto"
                        class="font-semibold text-green-600">S/ 0.00</span></p>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Bono fijo adicional (S/) — opcional</label>
                <input id="sueldoBono" type="number" min="0" step="10" class="inp w-full" placeholder="0">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Motivo del bono</label>
                <input id="sueldoRazon" type="text" class="inp w-full" placeholder="Ej: Cargo de supervisor">
            </div>
            <div class="bg-gray-50 rounded-xl p-4 text-sm space-y-2 border">
                <div class="flex justify-between"><span class="text-gray-500">Sueldo base</span><span id="resBase"
                        class="font-semibold">S/ 0.00</span></div>
                <div class="flex justify-between text-red-500"><span>AFP (13%)</span><span id="resAfp">- S/ 0.00</span>
                </div>
                <div class="flex justify-between border-t pt-2"><span class="text-gray-500">Neto tras AFP</span><span
                        id="resNeto" class="font-semibold">S/ 0.00</span></div>
                <div class="flex justify-between text-blue-600"><span id="resBonoLabel">Bono fijo</span><span
                        id="resBono">+ S/ 0.00</span></div>
                <div class="flex justify-between border-t pt-2 font-bold"><span>Total fijo</span><span id="resTotal"
                        class="text-violet-700">S/ 0.00</span></div>
            </div>
        </div>
        <div class="p-4 border-t">
            <button onclick="guardarSueldo()"
                class="w-full bg-violet-600 hover:bg-violet-700 text-white py-2.5 rounded-xl text-sm font-semibold transition">
                Guardar sueldo
            </button>
            <p id="msgSueldo" class="text-xs text-center text-green-600 mt-2 hidden">Guardado correctamente</p>
        </div>
    </div>

    {{-- ═══ MODAL PLANTILLA DE BONO ═══ --}}
    <div id="modalPlantilla" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg flex flex-col max-h-[90vh]">
            <div class="flex items-center justify-between p-4 border-b">
                <h3 class="font-bold text-gray-800">Plantilla de Bono</h3>
                <button onclick="cerrarModalPlantilla()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto p-5 space-y-4">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Nombre de la plantilla</label>
                    <input id="plantillaNombre" type="text" class="inp w-full" placeholder="Ej: Bono Ventas Tarde">
                </div>
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="text-xs text-gray-500 font-medium">Niveles de meta</label>
                        <button onclick="addPlantillaRow()"
                            class="text-xs text-violet-600 hover:text-violet-800 font-medium">+ Agregar nivel</button>
                    </div>
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-2 py-2 text-left text-xs text-gray-500">Nivel</th>
                                <th class="px-2 py-2 text-right text-xs text-gray-500">Meta (S/)</th>
                                <th class="px-2 py-2 text-right text-xs text-gray-500">% Bono</th>
                                <th class="px-2 py-2"></th>
                            </tr>
                        </thead>
                        <tbody id="plantillaRows"></tbody>
                    </table>
                </div>
            </div>
            <div class="p-4 border-t flex gap-3">
                <button onclick="guardarPlantilla()"
                    class="flex-1 bg-violet-600 hover:bg-violet-700 text-white py-2 rounded-xl text-sm font-semibold transition">Guardar
                    plantilla</button>
                <button onclick="cerrarModalPlantilla()"
                    class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700">Cancelar</button>
            </div>
        </div>
    </div>

    {{-- ═══ PANEL FLOTANTE — Bono empleado ═══ --}}
    <div id="panelBono" class="hidden fixed inset-y-0 right-0 w-full sm:w-96 bg-white shadow-2xl z-40 flex flex-col">
        <div class="flex items-center justify-between p-4 border-b">
            <div>
                <h3 id="bonoPanelName" class="font-bold text-gray-800 text-sm"></h3>
                <p class="text-xs text-gray-400">Configuración de bonos individuales</p>
            </div>
            <button onclick="cerrarPanelBono()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="flex-1 overflow-y-auto p-5 space-y-4">
            <input type="hidden" id="bonoEmpId">
            <div class="flex items-center justify-between">
                <label class="text-xs text-gray-500 font-medium">Niveles de bono</label>
                <div class="flex gap-2">
                    <select id="importarPlantilla" class="inp text-xs">
                        <option value="">Importar plantilla...</option>
                    </select>
                    <button onclick="importarPlantillaEmp()"
                        class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded-lg">Importar</button>
                </div>
            </div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b">
                    <tr>
                        <th class="px-2 py-2 text-left text-xs text-gray-500">Nivel</th>
                        <th class="px-2 py-2 text-right text-xs text-gray-500">Meta (S/)</th>
                        <th class="px-2 py-2 text-right text-xs text-gray-500">% Bono</th>
                        <th class="px-2 py-2"></th>
                    </tr>
                </thead>
                <tbody id="bonoEmpRows"></tbody>
            </table>
            <button onclick="addBonoEmpRow()" class="text-xs text-violet-600 hover:text-violet-800 font-medium">+
                Agregar nivel</button>
        </div>
        <div class="p-4 border-t">
            <button onclick="guardarBonoEmp()"
                class="w-full bg-violet-600 hover:bg-violet-700 text-white py-2.5 rounded-xl text-sm font-semibold transition">
                Guardar bonos
            </button>
            <p id="msgBonoEmp" class="text-xs text-center text-green-600 mt-2 hidden">Guardado correctamente</p>
        </div>
    </div>

    {{-- ═══ MODAL DETALLE DÍA ═══ --}}
    <div id="modalDetalle" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col">
            <div class="flex items-center justify-between p-4 border-b">
                <div>
                    <h3 id="detalleTitle" class="font-bold text-gray-800"></h3>
                    <p id="detalleCalendar" class="text-xs text-gray-500"></p>
                </div>
                <button onclick="cerrarDetalle()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div id="detalleBody" class="flex-1 overflow-y-auto p-4"></div>
        </div>
    </div>

    {{-- Overlay para paneles flotantes --}}
    <div id="panelOverlay" class="hidden fixed inset-0 bg-black/20 z-30" onclick="cerrarPaneles()"></div>

    <style>
        .inp {
            @apply border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-violet-400;
        }

        .tab-btn {
            @apply px-4 py-2 rounded-lg text-sm font-medium transition;
        }

        .tab-active {
            @apply bg-violet-600 text-white;
        }

        .tab-inactive {
            @apply bg-gray-100 text-gray-600 hover:bg-gray-200;
        }
    </style>

    <script>
        const AVATAR_SVG = (sz) => `<div class="${sz} rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0"><svg class="w-3/5 h-3/5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></div>`;
        function avatarError(img) {
            const cls = img.className;
            const sz = cls.match(/(w-\S+)/)?.[1] ?? 'w-8';
            const h = cls.match(/(h-\S+)/)?.[1] ?? 'h-8';
            img.outerHTML = `<div class="${sz} ${h} rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0"><svg class="w-3/5 h-3/5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg></div>`;
        }

        let semanaOffset = 0;
        let semanaSlipOffset = 0;  // Nómina Semanal
        let semanaOlOffset = 0;    // Panel Orden y Limpieza
        let empDataCache = [];
        let plantillas = JSON.parse(localStorage.getItem('rem_plantillas') || '[]');
        let bonoEmpTiers = [];
        let editingPlantillaIdx = null;
        // Nómina mensual
        let nominaYear = new Date().getFullYear();
        let nominaMonth = new Date().getMonth() + 1;
        let nominaData = null;
        let selectedWeekStart = null;
        let weekSemanaCache = {};  // cache: weekStart -> array de empleados con bono_individual
        // Orden y Limpieza
        let olConfigData = null;   // config global O&L
        let olThresholds = [];     // copia editable de score_thresholds
        let olDiscountRules = [];  // copia editable de discount_rules

        const fmt = n => 'S/ ' + Number(n).toLocaleString('es-PE', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        const csrfToken = () => document.querySelector('meta[name=csrf-token]')?.content ?? '';

        // ── Semana ───────────────────────────────────────────────────────
        function getWeekStart(offset) {
            const now = new Date();
            const day = now.getDay() || 7;
            const mon = new Date(now);
            mon.setDate(now.getDate() - day + 1 + offset * 7);
            return mon.toISOString().split('T')[0];
        }
        function fmtSemana(ws) {
            const d = new Date(ws + 'T12:00:00');
            const fin = new Date(d); fin.setDate(d.getDate() + 6);
            const opts = { day: 'numeric', month: 'short' };
            return d.toLocaleDateString('es-PE', opts) + ' — ' + fin.toLocaleDateString('es-PE', opts);
        }
        function cambiarSemana(dir) {
            semanaOffset += dir;
            document.getElementById('lblSemana').textContent = fmtSemana(getWeekStart(semanaOffset));
            cargarResumen();
        }

        // ── Tabs ───────────────────────────────────────────────────────
        function switchMainTab(n, btn) {
            [0, 1, 2, 3, 4].forEach(i => {
                document.getElementById('mpanel' + i)?.classList.toggle('hidden', i !== n);
                document.getElementById('mtab' + i)?.classList.replace(
                    i === n ? 'tab-inactive' : 'tab-active',
                    i === n ? 'tab-active' : 'tab-inactive'
                );
            });
            if (n === 1) cargarConfiguracion();
            if (n === 2) { renderMesLabel(); cargarNomina(); }
            if (n === 3) { cargarNominaSemanal(); }
            if (n === 4) { cargarPanelOL(); }
        }

        function switchCfgTab(n, btn) {
            ['cpanel0', 'cpanel1', 'cpanel2', 'cpanel3'].forEach((id, i) => {
                document.getElementById(id).classList.toggle('hidden', i !== n);
            });
            ['ctab0', 'ctab1', 'ctab2', 'ctab3'].forEach((id, i) => {
                document.getElementById(id).className = `flex-1 py-3 text-sm font-medium transition ${i === n ? 'bg-violet-600 text-white' : 'text-gray-600 hover:bg-gray-50'}`;
            });
            if (n === 3) cargarOlConfig();
        }

        // ── Orden y Limpieza ───────────────────────────────────────
        async function cargarOlConfig() {
            try {
                const d = await fetch('/api/orden-limpieza/config').then(r => r.json());
                olConfigData = d;
                olThresholds = JSON.parse(JSON.stringify(d.score_thresholds ?? []));
                olDiscountRules = JSON.parse(JSON.stringify(d.discount_rules ?? []));
                renderOlThresholds();
                renderOlDiscountRules();
            } catch (e) { console.error('Error cargando config O&L', e); }
        }

        function renderOlThresholds() {
            const body = document.getElementById('olThresholdsBody');
            if (!body) return;
            body.innerHTML = olThresholds.map((t, i) => `
                <tr>
                    <td class="px-3 py-1.5"><input type="number" min="0" max="2" step="0.1" value="${t.from}"
                        class="inp w-20 text-xs" oninput="olThresholds[${i}].from=parseFloat(this.value)||0"></td>
                    <td class="px-3 py-1.5"><input type="number" min="0" max="2" step="0.1" value="${t.to}"
                        class="inp w-20 text-xs" oninput="olThresholds[${i}].to=parseFloat(this.value)||0"></td>
                    <td class="px-3 py-1.5"><input type="number" min="0" max="10" step="1" value="${t.points}"
                        class="inp w-20 text-xs" oninput="olThresholds[${i}].points=parseInt(this.value)||0"></td>
                    <td class="px-3 py-1.5 text-center"><button onclick="olThresholds.splice(${i},1);renderOlThresholds()" class="text-red-400 hover:text-red-600 text-xs">✕</button></td>
                </tr>`).join('')
            || '<tr><td colspan="4" class="px-3 py-3 text-center text-xs text-gray-400">Sin rangos definidos</td></tr>';
        }

        function addOlThreshold() {
            olThresholds.push({ from: 0, to: 0, points: 0 });
            renderOlThresholds();
        }

        function renderOlDiscountRules() {
            const body = document.getElementById('olDiscountBody');
            if (!body) return;
            body.innerHTML = olDiscountRules.map((r, i) => `
                <tr>
                    <td class="px-3 py-1.5"><input type="number" min="0" step="1" value="${r.points}"
                        class="inp w-20 text-xs" oninput="olDiscountRules[${i}].points=parseInt(this.value)||0"></td>
                    <td class="px-3 py-1.5"><input type="number" min="0" max="100" step="1" value="${r.discount_pct}"
                        class="inp w-24 text-xs" oninput="olDiscountRules[${i}].discount_pct=parseInt(this.value)||0"></td>
                    <td class="px-3 py-1.5 text-center"><button onclick="olDiscountRules.splice(${i},1);renderOlDiscountRules()" class="text-red-400 hover:text-red-600 text-xs">✕</button></td>
                </tr>`).join('')
            || '<tr><td colspan="3" class="px-3 py-3 text-center text-xs text-gray-400">Sin reglas definidas</td></tr>';
        }

        function addOlDiscountRule() {
            olDiscountRules.push({ points: 0, discount_pct: 0 });
            renderOlDiscountRules();
        }

        async function guardarOlConfig() {
            const payload = { score_thresholds: olThresholds, discount_rules: olDiscountRules };
            const res = await fetch('/api/orden-limpieza/config', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
                body: JSON.stringify(payload)
            });
            const d = await res.json();
            if (!res.ok) { alert('Error al guardar configuración O&L'); return; }
            olConfigData = d.config;
            const msg = document.getElementById('msgOlConfig');
            msg.classList.remove('hidden');
            setTimeout(() => msg.classList.add('hidden'), 3000);
        }

        // Guardar calificación O&L de un empleado en una fecha desde el modal detalle
        async function guardarOlScore(localId, date, inputEl) {
            const score = parseFloat(inputEl.value);
            if (isNaN(score) || score < 0 || score > 2) { inputEl.classList.add('ring-red-400'); return; }
            inputEl.classList.remove('ring-red-400');
            inputEl.disabled = true;
            try {
                await fetch('/api/orden-limpieza/score', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
                    body: JSON.stringify({ employee_local_id: localId, date, score })
                });
                inputEl.classList.add('ring-2', 'ring-green-400');
                setTimeout(() => inputEl.classList.remove('ring-2', 'ring-green-400'), 1500);
            } catch (e) {
                alert('Error al guardar calificación');
            } finally {
                inputEl.disabled = false;
            }
        }

        // ── Panel Grid Orden y Limpieza ─────────────────────────────
        function cambiarSemanaOL(dir) {
            semanaOlOffset += dir;
            document.getElementById('lblSemanaOL').textContent = fmtSemana(getWeekStart(semanaOlOffset));
            cargarPanelOL();
        }

        async function cargarPanelOL() {
            const ws = getWeekStart(semanaOlOffset);
            document.getElementById('lblSemanaOL').textContent = fmtSemana(ws);
            const cont = document.getElementById('olGridCont');
            cont.innerHTML = '<p class="text-center text-gray-400 py-8 text-sm">Calculando...</p>';

            try {
                const [d, olScoresRaw] = await Promise.all([
                    fetch(`/api/remuneracion/semana?week_start=${ws}`).then(r => r.json()),
                    fetch(`/api/orden-limpieza/scores?week_start=${ws}`).then(r => r.json()).catch(() => ({}))
                ]);

                if (d.error) { cont.innerHTML = `<p class="text-red-400 text-center py-8">${d.error}</p>`; return; }

                const emps = d.empleados ?? [];
                if (!emps.length) { cont.innerHTML = '<p class="text-center text-gray-400 py-8">Sin empleados para esta semana</p>'; return; }

                // Generar los 7 días de la semana a partir de week_start
                const weekStartObj = new Date(d.week_start + 'T12:00:00');
                const daysOfWeek = [];
                const dayLabels = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];
                for (let i = 0; i < 7; i++) {
                    const current = new Date(weekStartObj);
                    current.setDate(weekStartObj.getDate() + i);
                    daysOfWeek.push({
                        date: current.toISOString().split('T')[0],
                        label: dayLabels[i] + ' ' + current.getDate()
                    });
                }

                const ths = daysOfWeek.map(d => `<th class="px-2 py-2 text-center text-xs text-gray-500 font-medium">${d.label}</th>`).join('');

                const rows = emps.map(e => {
                    const localId = e.local_id;
                    const olScores = localId ? (olScoresRaw[localId] ?? {}) : {};
                    const diasLaborables = e.dias_laborables || [];

                    const cells = daysOfWeek.map(day => {
                        const isLaborable = diasLaborables.includes(day.date);
                        let cellContent = '<span class="text-gray-300">—</span>';
                        if (isLaborable && localId) {
                            const val = olScores[day.date] ?? '';
                            const inputId = `grid_ol_${localId}_${day.date}`;
                            cellContent = `<input id="${inputId}" type="number" min="0" max="2" step="0.1" value="${val}"
                                class="inp w-16 text-xs text-center py-1 px-1 ${val !== '' ? 'ring-2 ring-violet-200' : ''}"
                                title="0 a 2">`;
                        }
                        return `<td class="px-2 py-2 text-center">${cellContent}</td>`;
                    }).join('');

                    const promLabel = e.ol_promedio !== null ? `<span class="font-bold text-violet-700">${e.ol_promedio.toFixed(1)}</span>` : '<span class="text-gray-400">—</span>';
                    const ptsLabel = e.ol_puntos !== null ? `<span class="font-bold text-gray-700">${e.ol_puntos} pts</span>` : '<span class="text-gray-400">—</span>';

                    return `<tr class="border-b hover:bg-gray-50">
                        <td class="px-3 py-2 text-sm font-medium text-gray-800 whitespace-nowrap">${e.name}</td>
                        <td class="px-3 py-2 text-xs text-gray-500 whitespace-nowrap">${e.turno === 'manana' ? 'Mañana' : e.turno === 'tarde' ? 'Tarde' : '—'}</td>
                        ${cells}
                        <td class="px-3 py-2 text-center text-sm">${promLabel}</td>
                        <td class="px-3 py-2 text-center text-sm">${ptsLabel}</td>
                    </tr>`;
                }).join('');

                cont.innerHTML = `
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th class="px-3 py-2 text-left text-xs text-gray-500 font-medium">Empleado</th>
                                <th class="px-3 py-2 text-left text-xs text-gray-500 font-medium">Turno</th>
                                ${ths}
                                <th class="px-3 py-2 text-center text-xs text-gray-500 font-medium">Promedio</th>
                                <th class="px-3 py-2 text-center text-xs text-gray-500 font-medium">Puntos O&L</th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                `;
            } catch (e) {
                console.error(e);
                cont.innerHTML = '<p class="text-red-400 text-center py-8">Error al cargar panel de Orden y Limpieza</p>';
            }
        }

        async function guardarGridOL() {
            const inputs = document.querySelectorAll('#olGridCont input[id^="grid_ol_"]');
            const promises = [];

            inputs.forEach(inp => {
                const val = parseFloat(inp.value);
                if (!isNaN(val)) {
                    // id es: grid_ol_{localId}_{date}
                    const parts = inp.id.split('_');
                    const localId = parts[2];
                    const date = parts.slice(3).join('_'); // soporta Y-m-d

                    promises.push(
                        fetch('/api/orden-limpieza/score', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
                            body: JSON.stringify({ employee_local_id: localId, date, score: val })
                        })
                    );
                }
            });

            if (!promises.length) return;

            const btn = document.getElementById('btnGuardarOL');
            btn.textContent = 'Guardando...';
            btn.disabled = true;
            btn.classList.add('opacity-75');

            try {
                await Promise.all(promises);
                await cargarPanelOL();
            } catch (e) {
                alert('Hubo un error al guardar algunas calificaciones.');
                console.error(e);
            } finally {
                btn.textContent = 'Guardar Calificaciones';
                btn.disabled = false;
                btn.classList.remove('opacity-75');
            }
        }

        // ── Nómina Semanal ────────────────────────────────────────
        function cambiarSemanaSlip(dir) {
            semanaSlipOffset += dir;
            document.getElementById('lblSemanaSlip').textContent = fmtSemana(getWeekStart(semanaSlipOffset));
            cargarNominaSemanal();
        }

        async function cargarNominaSemanal() {
            const ws = getWeekStart(semanaSlipOffset);
            document.getElementById('lblSemanaSlip').textContent = fmtSemana(ws);
            const cont = document.getElementById('nominaSemanalCont');
            cont.innerHTML = '<p class="text-center text-gray-400 text-sm py-10">Calculando...</p>';

            try {
                const d = await fetch(`/api/remuneracion/semana?week_start=${ws}`).then(r => r.json());
                if (d.error) { cont.innerHTML = `<p class="text-red-400 text-center py-8">${d.error}</p>`; return; }

                const emps = d.empleados ?? [];
                const semanaLabel = d.week_start && d.week_end
                    ? `${fmtSemana(d.week_start)}`
                    : fmtSemana(ws);

                // Separar por turno
                const manana = emps.filter(e => e.turno === 'manana').sort((a, b) => b.bono_bruto - a.bono_bruto);
                const tarde  = emps.filter(e => e.turno === 'tarde').sort((a, b) => b.bono_bruto - a.bono_bruto);
                const indef  = emps.filter(e => e.turno !== 'manana' && e.turno !== 'tarde');

                const olLabel = (e) => {
                    if (e.ol_puntos === null || e.ol_puntos === undefined) return '<span class="text-gray-300 text-xs">—</span>';
                    const raw = e.ol_promedio !== null ? e.ol_promedio.toFixed(1) : '?';
                    const pts = e.ol_puntos;
                    return `<span class="text-xs">${raw} = ${pts}P</span>`;
                };

                const slipTable = (lista, titulo, hrs) => {
                    if (!lista.length) return '';
                    let totVentas = 0, totBonoBruto = 0, totBonoFinal = 0;
                    const rows = lista.map((e, i) => {
                        const orden = i + 1;
                        const faltas = e.faltas > 0 ? `${e.faltas}F` : '';
                        const tardanzas = e.tardanzas > 0 ? `${e.tardanzas}T` : '';
                        const ftLabel = [faltas, tardanzas].filter(Boolean).join(' ') || '0';
                        const calificacion = e.pierde_bono ? '<span class="text-red-500 text-xs">Pierde bono</span>'
                            : (e.tier_individual ?? '<span class="text-gray-400 text-xs">—</span>');
                        const bonoPct = e.tier_individual && !e.pierde_bono
                            ? `<span class="text-violet-700 text-xs">${e.bono_bruto > 0 && e.ventas_semana > 0 ? (e.bono_bruto / e.ventas_semana * 100).toFixed(2) + '%' : '—'}</span>`
                            : '<span class="text-gray-400 text-xs">0%</span>';
                        const descPct = e.ol_desc_pct > 0 ? `<span class="text-red-500 text-xs">${e.ol_desc_pct}%</span>` : '<span class="text-gray-400 text-xs">0%</span>';
                        totVentas += e.ventas_semana;
                        totBonoBruto += e.bono_bruto;
                        totBonoFinal += e.bono_individual;
                        return `<tr class="border-b hover:bg-violet-50 cursor-pointer transition-colors" onclick="abrirDetalle(${e.id}, '${d.week_start || ws}')" title="Clic para ver detalle y justificar asistencias">
                            <td class="px-3 py-2 text-xs font-bold text-gray-500">${orden}</td>
                            <td class="px-3 py-2 text-sm font-semibold text-gray-800">${e.name.split(' ')[0]}</td>
                            <td class="px-3 py-2 text-sm text-right font-mono">${e.ventas_semana > 0 ? e.ventas_semana.toLocaleString('es-PE',{minimumFractionDigits:1,maximumFractionDigits:1}) : '0.0'}</td>
                            <td class="px-3 py-2 text-xs text-center ${e.faltas > 0 || e.tardanzas > 0 ? 'text-orange-600 font-medium' : 'text-gray-400'}">${ftLabel}</td>
                            <td class="px-3 py-2 text-xs text-center font-semibold">${calificacion}</td>
                            <td class="px-3 py-2 text-center">${bonoPct}</td>
                            <td class="px-3 py-2 text-right text-xs font-mono text-violet-700">${e.bono_bruto > 0 ? fmt(e.bono_bruto) : '—'}</td>
                            <td class="px-3 py-2 text-center">${olLabel(e)}</td>
                            <td class="px-3 py-2 text-center">${descPct}</td>
                            <td class="px-3 py-2 text-right text-sm font-bold text-violet-800">${e.bono_individual > 0 ? fmt(e.bono_individual) : (e.bono_bruto > 0 ? '<span class="text-red-400 text-xs">' + fmt(0) + '</span>' : '—')}</td>
                        </tr>`;
                    }).join('');
                    const footRow = `<tr class="bg-violet-50 font-bold text-sm">
                        <td colspan="2" class="px-3 py-2 text-violet-700">TOTAL TURNO</td>
                        <td class="px-3 py-2 text-right font-mono text-violet-700">${totVentas.toLocaleString('es-PE',{minimumFractionDigits:1,maximumFractionDigits:1})}</td>
                        <td colspan="3"></td>
                        <td class="px-3 py-2 text-right font-mono text-violet-600">${fmt(totBonoBruto)}</td>
                        <td></td><td></td>
                        <td class="px-3 py-2 text-right text-violet-800">${fmt(totBonoFinal)}</td>
                    </tr>`;
                    return `
                    <div class="bg-white rounded-2xl shadow overflow-hidden mb-6">
                        <div class="px-5 py-3 bg-violet-600 text-white">
                            <span class="font-bold text-sm uppercase tracking-wide">${titulo}</span>
                            <span class="text-violet-200 text-xs ml-2">${hrs}</span>
                        </div>
                        <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th class="px-3 py-2 text-left text-xs text-gray-500 font-medium">Orden</th>
                                    <th class="px-3 py-2 text-left text-xs text-gray-500 font-medium">Vendedor</th>
                                    <th class="px-3 py-2 text-right text-xs text-gray-500 font-medium">Monto S/</th>
                                    <th class="px-3 py-2 text-center text-xs text-gray-500 font-medium">Faltas/Tard.</th>
                                    <th class="px-3 py-2 text-center text-xs text-gray-500 font-medium">Calificación</th>
                                    <th class="px-3 py-2 text-center text-xs text-gray-500 font-medium">Bonifica%</th>
                                    <th class="px-3 py-2 text-right text-xs text-gray-500 font-medium">Sub Bono</th>
                                    <th class="px-3 py-2 text-center text-xs text-gray-500 font-medium">O&amp;L</th>
                                    <th class="px-3 py-2 text-center text-xs text-gray-500 font-medium">Desc%</th>
                                    <th class="px-3 py-2 text-right text-xs text-gray-500 font-medium">Total</th>
                                </tr>
                            </thead>
                            <tbody>${rows}</tbody>
                            <tfoot class="border-t">${footRow}</tfoot>
                        </table>
                        </div>
                    </div>`;
                };

                cont.innerHTML = `
                    <div class="text-xs text-gray-400 mb-4 px-1">BONIFICACIÓN — ${semanaLabel.toUpperCase()}</div>
                    ${slipTable(manana, 'Turno Mañana', '4 HRS')}
                    ${slipTable(tarde, 'Turno Tarde', '8 HRS')}
                    ${indef.length ? slipTable(indef, 'Turno Indefinido', '') : ''}
                    ${!emps.length ? '<p class="text-center text-gray-400 py-8">Sin empleados para esta semana</p>' : ''}
                `;
            } catch (e) {
                cont.innerHTML = '<p class="text-red-400 text-center py-8">Error al cargar nómina semanal</p>';
                console.error(e);
            }
        }

        // ── Resumen ──────────────────────────────────────────────────────
        async function cargarResumen() {
            const ws = getWeekStart(semanaOffset);
            document.getElementById('loadingResumen').classList.remove('hidden');
            document.getElementById('empCards').innerHTML = '';

            try {
                const d = await fetch(`/api/remuneracion/semana?week_start=${ws}`).then(r => r.json());
                if (d.error) {
                    document.getElementById('empCards').innerHTML = `<p class="text-red-400 col-span-3 text-center py-8">${d.error}</p>`;
                    return;
                }
                document.getElementById('empCards').innerHTML = d.empleados.map(e => empCard(e, ws)).join('');
            } catch (e) {
                document.getElementById('empCards').innerHTML = `<p class="text-red-400 col-span-3 text-center py-8">Error al cargar datos</p>`;
            } finally {
                document.getElementById('loadingResumen').classList.add('hidden');
            }
        }

        function empCard(e, weekStart) {
            const photo = (e.photo && e.photo !== false)
                ? `<img src="data:image/png;base64,${e.photo}" class="w-10 h-10 rounded-full object-cover flex-shrink-0" onerror="avatarError(this)">`
                : AVATAR_SVG('w-10 h-10');

            const turnoLabel = e.turno === 'manana' ? 'Mañana' : e.turno === 'tarde' ? 'Tarde' : '—';

            let badge = '';
            if (e.pierde_bono) {
                badge = `<span class="text-xs px-2 py-0.5 rounded-full bg-red-100 text-red-600 font-medium">Pierde bono · ${e.razon_perdida}</span>`;
            } else if ((e.bono_individual || 0) > 0) {
                badge = `<span class="text-xs px-2 py-0.5 rounded-full bg-violet-100 text-violet-600 font-medium">Bono ind. ${e.tier_individual ?? ''}</span>`;
            } else {
                badge = `<span class="text-xs px-2 py-0.5 rounded-full bg-yellow-100 text-yellow-600 font-medium">Sin bono aún</span>`;
            }

            const fBtn = `<button onclick="abrirDetalle(${e.id},'${weekStart}')"
            class="flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium transition
            ${e.faltas > 0 ? 'bg-red-100 text-red-600 hover:bg-red-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'}">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            ${e.faltas}</button>`;
            const tBtn = `<button onclick="abrirDetalle(${e.id},'${weekStart}')"
            class="flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-medium transition
            ${e.tardanzas > 0 ? 'bg-orange-100 text-orange-600 hover:bg-orange-200' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'}">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            ${e.tardanzas}</button>`;

            const afp = e.salario_base * 0.13;
            const neto = e.salario_base - afp;
            const total = neto + (e.extra_bonus || 0) + (e.bono_individual || 0) - (e.descuento_total || 0);

            const olBadge = (() => {
                if (e.ol_puntos === null || e.ol_puntos === undefined) {
                    return `<span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-400">🧹 O&amp;L Sin calificar</span>`;
                }
                const cls = e.ol_puntos === 2 ? 'bg-green-100 text-green-700'
                    : e.ol_puntos === 1 ? 'bg-yellow-100 text-yellow-700'
                    : 'bg-red-100 text-red-600';
                const label = e.ol_puntos === 2 ? 'Sin descuento'
                    : e.ol_puntos === 1 ? `-${e.ol_desc_pct}% bono`
                    : `-${e.ol_desc_pct}% bono`;
                return `<span class="text-xs px-2 py-0.5 rounded-full font-medium ${cls}">🧹 O&amp;L ${e.ol_promedio?.toFixed(1) ?? '?'} = ${e.ol_puntos}P &middot; ${label}</span>`;
            })();

            return `<div class="bg-white rounded-2xl shadow p-5">
            <div class="flex items-center gap-3 mb-3">
                ${photo}
                <div class="flex-1 min-w-0">
                    <p class="font-semibold text-gray-800 truncate text-sm">${e.name}</p>
                    <p class="text-xs text-gray-400">${e.job ?? ''} &middot; ${turnoLabel}</p>
                </div>
                <div class="flex gap-1 flex-shrink-0">${fBtn}${tBtn}</div>
            </div>
            <div class="mb-2 flex flex-wrap gap-1">${badge}</div>
            <div class="mb-3 flex flex-wrap gap-1">${olBadge}</div>
            <div class="text-xs text-violet-600 font-semibold mb-3">Ventas: ${fmt(e.ventas_semana)}</div>
            <div class="border-t pt-3 space-y-1 text-sm">
                <div class="flex justify-between"><span class="text-gray-400">Sueldo base</span><span>${fmt(e.salario_base)}</span></div>
                <div class="flex justify-between text-red-400"><span>AFP (13%)</span><span>- ${fmt(afp)}</span></div>
                <div class="flex justify-between"><span class="text-gray-400">Neto tras AFP</span><span>${fmt(neto)}</span></div>
                ${e.extra_bonus > 0 ? `<div class="flex justify-between text-blue-500"><span>${e.extra_bonus_reason || 'Bono fijo'}</span><span>+ ${fmt(e.extra_bonus)}</span></div>` : ''}
                ${e.bono_individual > 0 ? `<div class="flex justify-between text-violet-600"><span>Bono ind.${e.ol_desc_pct > 0 ? ` <span class="text-xs text-red-400">(-${e.ol_desc_pct}% O&amp;L)</span>` : ''}</span><span>+ ${fmt(e.bono_individual)}</span></div>` : ''}
                ${e.descuento_total > 0 ? `<div class="flex justify-between text-red-500"><span>Desc. faltas</span><span>- ${fmt(e.descuento_total)}</span></div>` : ''}
                <div class="flex justify-between font-bold border-t pt-1.5"><span>Total estimado</span><span class="text-violet-700">${fmt(total)}</span></div>
            </div>
        </div>`;
        }

        // ── Configuración ────────────────────────────────────────────────
        async function cargarConfiguracion() {
            try {
                const [empRes, cfgRes] = await Promise.all([
                    fetch('/api/remuneracion/empleados').then(r => r.json()),
                    fetch('/api/remuneracion/config').then(r => r.json()),
                ]);
                empDataCache = empRes;

                // Tardanzas
                const tard = cfgRes.tardiness ?? {};
                document.getElementById('cfgTolerance').value = tard.threshold_minutes ?? 10;
                document.getElementById('cfgDeduction').value = tard.deduction_amount ?? '';

                renderSueldoList();
                renderBonoEmpList();
                renderPlantillasList();
            } catch (e) { console.error(e); }
        }

        // ── Sueldo ───────────────────────────────────────────────────────
        function renderSueldoList() {
            const list = document.getElementById('sueldoList');
            if (!empDataCache.length) { list.innerHTML = '<p class="p-6 text-center text-gray-400 text-sm">Sin empleados</p>'; return; }
            list.innerHTML = empDataCache.map(e => {
                const afp = (e.base_salary || 0) * 0.13;
                const neto = (e.base_salary || 0) - afp;
                return `<button onclick="abrirPanelSueldo(${e.id})"
                class="w-full flex items-center justify-between px-5 py-4 hover:bg-violet-50 transition text-left">
                <div class="flex items-center gap-3">
                    ${(e.photo && e.photo !== false) ? `<img src="data:image/png;base64,${e.photo}" class="w-9 h-9 rounded-full object-cover flex-shrink-0" onerror="avatarError(this)">` : AVATAR_SVG('w-9 h-9')}
                    <div>
                        <p class="text-sm font-medium text-gray-800">${e.name}</p>
                        <p class="text-xs text-gray-400">${e.job ?? ''}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm font-semibold text-gray-800">${fmt(e.base_salary || 0)}</p>
                    <p class="text-xs text-gray-400">neto: ${fmt(neto)}</p>
                </div>
            </button>`;
            }).join('');
        }

        function abrirPanelSueldo(empId) {
            const e = empDataCache.find(x => x.id === empId);
            if (!e) return;
            document.getElementById('sueldoEmpId').value = empId;
            document.getElementById('sueldoPanelName').textContent = e.name;
            document.getElementById('sueldoPanelJob').textContent = e.job ?? '';
            document.getElementById('sueldoBase').value = e.base_salary ?? 0;
            document.getElementById('sueldoBono').value = e.extra_bonus ?? 0;
            document.getElementById('sueldoRazon').value = e.extra_bonus_reason ?? '';
            calcSueldo();
            document.getElementById('panelSueldo').classList.remove('hidden');
            document.getElementById('panelOverlay').classList.remove('hidden');
        }

        function cerrarPanelSueldo() {
            document.getElementById('panelSueldo').classList.add('hidden');
            document.getElementById('panelOverlay').classList.add('hidden');
        }

        function calcSueldo() {
            const base = parseFloat(document.getElementById('sueldoBase').value) || 0;
            const bono = parseFloat(document.getElementById('sueldoBono').value) || 0;
            const razon = document.getElementById('sueldoRazon').value || 'Bono fijo';
            const afp = base * 0.13;
            const neto = base - afp;
            const total = neto + bono;
            document.getElementById('sueldoAFP').textContent = fmt(afp);
            document.getElementById('sueldoNeto').textContent = fmt(neto);
            document.getElementById('resBase').textContent = fmt(base);
            document.getElementById('resAfp').textContent = '- ' + fmt(afp);
            document.getElementById('resNeto').textContent = fmt(neto);
            document.getElementById('resBonoLabel').textContent = razon || 'Bono fijo';
            document.getElementById('resBono').textContent = '+ ' + fmt(bono);
            document.getElementById('resTotal').textContent = fmt(total);
        }

        document.getElementById('sueldoBase').addEventListener('input', calcSueldo);
        document.getElementById('sueldoBono').addEventListener('input', calcSueldo);
        document.getElementById('sueldoRazon').addEventListener('input', calcSueldo);

        async function guardarSueldo() {
            const empId = parseInt(document.getElementById('sueldoEmpId').value);
            const base = parseFloat(document.getElementById('sueldoBase').value) || 0;
            const bono = parseFloat(document.getElementById('sueldoBono').value) || 0;
            const razon = document.getElementById('sueldoRazon').value;

            const empLocal = empDataCache.find(e => e.id === empId);
            if (!empLocal) return;

            // Enviar local_id (BD) para que el backend no dependa de la tabla employees sincronizada
            const payload = {
                salarios: [{
                    local_id: empLocal.local_id ?? null,
                    employee_id: empId,   // odoo_id como fallback
                    name: empLocal.name,
                    base_salary: base,
                    extra_bonus: bono,
                    extra_bonus_reason: razon
                }]
            };

            await fetch('/api/remuneracion/salarios', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
                body: JSON.stringify(payload)
            });

            // Actualizar cache local
            const idx = empDataCache.findIndex(e => e.id === empId);
            if (idx >= 0) {
                empDataCache[idx].base_salary = base;
                empDataCache[idx].extra_bonus = bono;
                empDataCache[idx].extra_bonus_reason = razon;
            }

            const msg = document.getElementById('msgSueldo');
            msg.classList.remove('hidden');
            setTimeout(() => { msg.classList.add('hidden'); cerrarPanelSueldo(); renderSueldoList(); }, 1500);
        }

        // ── Plantillas ───────────────────────────────────────────────────
        function renderPlantillasList() {
            const list = document.getElementById('plantillasList');
            if (!plantillas.length) { list.innerHTML = '<p class="text-center text-gray-400 text-sm py-4">Sin plantillas aún</p>'; return; }
            list.innerHTML = plantillas.map((p, i) => `
        <div class="flex items-center justify-between bg-gray-50 rounded-xl px-4 py-3">
            <div>
                <p class="text-sm font-medium text-gray-800">${p.name}</p>
                <p class="text-xs text-gray-400">${p.tiers.length} nivel(es) · Máx. meta: ${fmt(Math.max(...p.tiers.map(t => t.meta || 0)))}</p>
            </div>
            <div class="flex gap-2">
                <button onclick="abrirModalPlantilla(${i})" class="text-xs text-violet-600 hover:text-violet-800 font-medium">Editar</button>
                <button onclick="eliminarPlantilla(${i})" class="text-xs text-red-400 hover:text-red-600">Eliminar</button>
            </div>
        </div>`).join('');
        }

        function abrirModalPlantilla(idx) {
            editingPlantillaIdx = idx;
            const p = idx !== null ? plantillas[idx] : { name: '', tiers: [] };
            document.getElementById('plantillaNombre').value = p.name;
            const body = document.getElementById('plantillaRows');
            body.innerHTML = p.tiers.map((t, i) => plantillaRow(t, i)).join('') ||
                '<tr><td colspan="4" class="px-2 py-3 text-center text-xs text-gray-400">Sin niveles — agrega uno</td></tr>';
            document.getElementById('modalPlantilla').classList.remove('hidden');
        }

        function plantillaRow(t, i) {
            return `<tr data-pi="${i}">
            <td class="px-2 py-1.5"><input type="text" value="${t.nivel || ''}" placeholder="A" maxlength="10"
                class="inp w-16 text-xs text-center" oninput="updatePlantillaRow(${i},'nivel',this.value)"></td>
            <td class="px-2 py-1.5 text-right"><input type="number" min="0" step="100" value="${t.meta || 0}"
                class="inp w-24 text-right text-xs" oninput="updatePlantillaRow(${i},'meta',parseFloat(this.value)||0)"></td>
            <td class="px-2 py-1.5 text-right"><input type="number" min="0" step="0.001" value="${t.bono_pct || ''}"
                class="inp w-24 text-right text-xs" placeholder="0.100" oninput="updatePlantillaRow(${i},'bono_pct',parseFloat(this.value)||null)"></td>
            <td class="px-2 py-1.5 text-center"><button onclick="removePlantillaRow(${i})" class="text-red-400 hover:text-red-600 text-xs">✕</button></td>
        </tr>`;
        }

        function getCurrentPlantillaTiers() {
            const rows = document.querySelectorAll('#plantillaRows tr[data-pi]');
            return Array.from(rows).map(r => {
                const inputs = r.querySelectorAll('input');
                return { nivel: inputs[0].value, meta: parseFloat(inputs[1].value) || 0, bono_pct: parseFloat(inputs[2].value) || null };
            });
        }

        function updatePlantillaRow(i, field, val) { /* handled live on save */ }

        function addPlantillaRow() {
            const body = document.getElementById('plantillaRows');
            const existing = getCurrentPlantillaTiers();
            const newRow = { nivel: '', meta: 0, bono_pct: null };
            existing.push(newRow);
            body.innerHTML = existing.map((t, i) => plantillaRow(t, i)).join('');
        }

        function removePlantillaRow(i) {
            const tiers = getCurrentPlantillaTiers();
            tiers.splice(i, 1);
            document.getElementById('plantillaRows').innerHTML = tiers.map((t, j) => plantillaRow(t, j)).join('') ||
                '<tr><td colspan="4" class="px-2 py-3 text-center text-xs text-gray-400">Sin niveles</td></tr>';
        }

        function guardarPlantilla() {
            const name = document.getElementById('plantillaNombre').value.trim();
            if (!name) { alert('Ingresa un nombre para la plantilla.'); return; }
            const tiers = getCurrentPlantillaTiers();
            const metas = tiers.map(t => t.meta);
            if (new Set(metas).size !== metas.length) { alert('Las metas deben ser únicas.'); return; }

            const p = { name, tiers };
            if (editingPlantillaIdx !== null) {
                plantillas[editingPlantillaIdx] = p;
            } else {
                plantillas.push(p);
            }
            localStorage.setItem('rem_plantillas', JSON.stringify(plantillas));
            cerrarModalPlantilla();
            renderPlantillasList();
            actualizarSelectPlantillas();
        }

        function eliminarPlantilla(i) {
            if (!confirm('¿Eliminar esta plantilla?')) return;
            plantillas.splice(i, 1);
            localStorage.setItem('rem_plantillas', JSON.stringify(plantillas));
            renderPlantillasList();
            actualizarSelectPlantillas();
        }

        function cerrarModalPlantilla() {
            document.getElementById('modalPlantilla').classList.add('hidden');
            editingPlantillaIdx = null;
        }

        function actualizarSelectPlantillas() {
            const sel = document.getElementById('importarPlantilla');
            sel.innerHTML = '<option value="">Importar plantilla...</option>' +
                plantillas.map((p, i) => `<option value="${i}">${p.name}</option>`).join('');
        }

        // ── Bono empleado ────────────────────────────────────────────────
        function renderBonoEmpList() {
            const list = document.getElementById('bonoEmpList');
            if (!empDataCache.length) { list.innerHTML = '<p class="p-6 text-center text-gray-400 text-sm">Sin empleados</p>'; return; }
            actualizarSelectPlantillas();
            list.innerHTML = empDataCache.map(e => {
                const tiersCount = (e.emp_tiers ?? []).length;
                return `<button onclick="abrirPanelBono(${e.id})"
                class="w-full flex items-center justify-between px-5 py-4 hover:bg-violet-50 transition text-left">
                <div class="flex items-center gap-3">
                    ${(e.photo && e.photo !== false) ? `<img src="data:image/png;base64,${e.photo}" class="w-9 h-9 rounded-full object-cover flex-shrink-0" onerror="avatarError(this)">` : AVATAR_SVG('w-9 h-9')}
                    <div>
                        <p class="text-sm font-medium text-gray-800">${e.name}</p>
                        <p class="text-xs text-gray-400">${e.job ?? ''}</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-xs ${tiersCount > 0 ? 'text-violet-600 font-medium' : 'text-gray-400'}">${tiersCount > 0 ? tiersCount + ' nivel(es)' : 'Sin bonos'}</p>
                </div>
            </button>`;
            }).join('');
        }

        function abrirPanelBono(empId) {
            const e = empDataCache.find(x => x.id === empId);
            if (!e) return;
            document.getElementById('bonoEmpId').value = empId;
            document.getElementById('bonoPanelName').textContent = e.name;
            bonoEmpTiers = JSON.parse(JSON.stringify(e.emp_tiers ?? []));
            renderBonoEmpRows();
            actualizarSelectPlantillas();
            document.getElementById('panelBono').classList.remove('hidden');
            document.getElementById('panelOverlay').classList.remove('hidden');
        }

        function cerrarPanelBono() {
            document.getElementById('panelBono').classList.add('hidden');
            document.getElementById('panelOverlay').classList.add('hidden');
        }

        function renderBonoEmpRows() {
            const body = document.getElementById('bonoEmpRows');
            body.innerHTML = bonoEmpTiers.map((t, i) => `
        <tr>
            <td class="px-2 py-1.5"><input type="text" value="${t.label || ''}" placeholder="A" maxlength="10"
                class="inp w-16 text-xs text-center" oninput="bonoEmpTiers[${i}].label=this.value"></td>
            <td class="px-2 py-1.5 text-right"><input type="number" min="0" step="100" value="${t.sales_goal || 0}"
                class="inp w-24 text-right text-xs" oninput="bonoEmpTiers[${i}].sales_goal=parseFloat(this.value)||0"></td>
            <td class="px-2 py-1.5 text-right"><input type="number" min="0" step="0.001" value="${t.bonus_pct || ''}"
                class="inp w-24 text-right text-xs" placeholder="0.100" oninput="bonoEmpTiers[${i}].bonus_pct=parseFloat(this.value)||null"></td>
            <td class="px-2 py-1.5 text-center"><button onclick="bonoEmpTiers.splice(${i},1);renderBonoEmpRows()" class="text-red-400 hover:text-red-600 text-xs">✕</button></td>
        </tr>`).join('') || '<tr><td colspan="4" class="px-2 py-4 text-center text-xs text-gray-400">Sin niveles — agrega uno o importa plantilla</td></tr>';
        }

        function addBonoEmpRow() {
            bonoEmpTiers.push({ label: '', sales_goal: 0, bonus_pct: null });
            renderBonoEmpRows();
        }

        function importarPlantillaEmp() {
            const idx = parseInt(document.getElementById('importarPlantilla').value);
            if (isNaN(idx)) return;
            const p = plantillas[idx];
            // Importar sin sobreescribir — merge
            p.tiers.forEach(t => {
                bonoEmpTiers.push({ label: t.nivel, sales_goal: t.meta, bonus_pct: t.bono_pct ?? t.bono });
            });
            renderBonoEmpRows();
            document.getElementById('importarPlantilla').value = '';
        }

        async function guardarBonoEmp() {
            const empOdooId = parseInt(document.getElementById('bonoEmpId').value);
            const metas = bonoEmpTiers.map(t => t.sales_goal);
            if (new Set(metas).size !== metas.length) { alert('Las metas deben ser únicas.'); return; }

            const empCache = empDataCache.find(e => e.id === empOdooId);
            if (!empCache) return;

            // Eliminar tiers viejos de este empleado
            const oldIds = empCache.emp_tier_ids ?? [];

            const payload = {
                tiers: bonoEmpTiers.map((t, i) => ({
                    // si el tier ya tiene id (editando), pasarlo
                    ...(t.id ? { id: t.id } : {}),
                    owner_type: 'individual',
                    area: 'ventas',
                    period_type: 'weekly',
                    shift: null,
                    label: t.label || String.fromCharCode(65 + i),
                    sales_goal: t.sales_goal,
                    bonus_amount: 0,
                    bonus_pct: t.bonus_pct,
                    sort_order: i,
                    // Enviar local_id para vincular al empleado en la BD local
                    local_id: empCache.local_id ?? null,
                    employee_id: empOdooId,   // fallback odoo
                })),
                delete_tier_ids: oldIds,
            };

            await fetch('/api/remuneracion/metas', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
                body: JSON.stringify(payload)
            });

            // Actualizar cache para que al reabrir el panel los tiers estén
            const idx = empDataCache.findIndex(e => e.id === empOdooId);
            if (idx >= 0) {
                empDataCache[idx].emp_tiers = bonoEmpTiers.map((t, i) => ({ ...t, label: t.label || String.fromCharCode(65 + i) }));
                empDataCache[idx].emp_tier_ids = []; // se regenerarán en próximo cargarConfiguracion()
            }

            const msg = document.getElementById('msgBonoEmp');
            msg.classList.remove('hidden');
            setTimeout(() => { msg.classList.add('hidden'); cerrarPanelBono(); renderBonoEmpList(); }, 1500);
        }

        // ── Descuento ────────────────────────────────────────────────────
        async function guardarDescuento() {
            const payload = {
                tardiness: {
                    threshold_minutes: parseInt(document.getElementById('cfgTolerance').value) || 10,
                    deduction_amount: parseFloat(document.getElementById('cfgDeduction').value) || null,
                }
            };
            await fetch('/api/remuneracion/metas', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
                body: JSON.stringify(payload)
            });
            const msg = document.getElementById('msgDescuento');
            msg.classList.remove('hidden');
            setTimeout(() => msg.classList.add('hidden'), 3000);
        }

        // ── Modal detalle ────────────────────────────────────────────────
        async function abrirDetalle(empId, weekStart) {
            document.getElementById('modalDetalle').classList.remove('hidden');
            document.getElementById('detalleBody').innerHTML = '<p class="text-center text-gray-400 py-8">Cargando...</p>';

            const [d, olScoresRaw] = await Promise.all([
                fetch(`/api/remuneracion/detalle?emp_id=${empId}&week_start=${weekStart}`).then(r => r.json()),
                fetch(`/api/orden-limpieza/scores?week_start=${weekStart}`).then(r => r.json()).catch(() => ({})),
            ]);
            if (d.error) { document.getElementById('detalleBody').innerHTML = `<p class="text-red-500 p-4">${d.error}</p>`; return; }

            document.getElementById('detalleTitle').textContent = d.name + ' — ' + weekStart;
            document.getElementById('detalleCalendar').textContent = d.calendar || '';

            // Buscar el local_id del empleado (buscamos en empDataCache o lo pedimos)
            const empCached = empDataCache.find(x => x.id === empId);
            const localId = empCached?.local_id ?? null;

            // Scores O&L de este empleado: {date: score}
            const olScores = localId ? (olScoresRaw[localId] ?? {}) : {};

            const sc = { puntual: 'bg-green-100 text-green-700', tardanza: 'bg-orange-100 text-orange-700', falta: 'bg-red-100 text-red-700', dia_libre: 'bg-gray-100 text-gray-500', pendiente: 'bg-blue-100 text-blue-600' };
            const sl = { puntual: 'Puntual', tardanza: 'Tardanza', falta: 'Falta', dia_libre: 'Día libre', pendiente: 'Pendiente' };

            const rows = d.days.map(day => {
                const color = sc[day.status] ?? 'bg-gray-100 text-gray-500';
                const label = sl[day.status] ?? day.status;
                let actions = '';

                if (day.status === 'falta' && !day.is_day_off) {
                    const justified = day.justif_falta;
                    actions = `<button onclick="abrirModalJustif(${empId},'${day.date}','falta',${justified},'${weekStart}',\`${(day.justif_reason || '').replace(/`/g, "'")}\`)"
                    class="text-xs ${justified ? 'bg-green-100 text-green-700' : 'bg-violet-100 text-violet-700'} hover:opacity-80 px-2 py-1 rounded-lg transition font-medium">
                    ${justified ? '✓ Justificado' : 'Justificar'}
                </button>`;
                }
                if (day.status === 'tardanza') {
                    const justified = day.justif_tardanza;
                    actions = `<button onclick="abrirModalJustif(${empId},'${day.date}','tardanza',${justified},'${weekStart}',\`${(day.justif_reason || '').replace(/`/g, "'")}\`)"
                    class="text-xs ${justified ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700'} hover:opacity-80 px-2 py-1 rounded-lg transition font-medium">
                    ${justified ? '✓ Justificado' : 'Justificar'}
                </button>`;
                }
                if (day.status === 'pendiente') {
                    actions = `<span class="text-xs text-blue-400 italic">Aún en curso</span>`;
                }

                const desc = day.descuento > 0 ? `<span class="text-red-400 text-xs">-${fmt(day.descuento)}</span>` : '';
                const minsLabel = day.minutos_tardanza > 0 ? `<span class="text-xs text-orange-400">${day.minutos_tardanza} min</span>` : '';

                // Columna O&L: solo días laborables (no día libre), no días futuros pendientes
                let olCell = '<td class="px-2 py-2 text-center text-gray-300 text-xs">—</td>';
                if (!day.is_day_off && day.status !== 'pendiente' && localId) {
                    const existingScore = olScores[day.date] ?? '';
                    const inputId = `ol_${localId}_${day.date}`;
                    olCell = `<td class="px-2 py-2 text-center">
                        <input id="${inputId}" type="number" min="0" max="2" step="0.1"
                            value="${existingScore}"
                            placeholder="—"
                            class="inp w-16 text-xs text-center py-1 px-1 ${existingScore !== '' ? 'ring-2 ring-violet-200' : ''}"
                            onblur="guardarOlScore(${localId},'${day.date}',this)"
                            title="Calificación Orden y Limpieza (0 a 2)">
                    </td>`;
                }

                return `<tr class="border-b">
                <td class="px-3 py-2 text-xs font-medium text-gray-700 whitespace-nowrap">${day.label}</td>
                <td class="px-3 py-2 text-xs text-gray-600">${day.check_in ?? '—'}</td>
                <td class="px-3 py-2 text-xs text-gray-600">${day.check_out ?? '—'}</td>
                <td class="px-3 py-2 text-xs text-gray-400">${day.expected_in ?? '—'}</td>
                <td class="px-3 py-2"><span class="text-xs px-2 py-0.5 rounded-full font-medium ${color}">${label}</span>${minsLabel}</td>
                <td class="px-3 py-2">${desc}</td>
                ${olCell}
                <td class="px-3 py-2">${actions}</td>
            </tr>`;
            }).join('');

            document.getElementById('detalleBody').innerHTML = `
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-3 py-2 text-left text-xs text-gray-500">Día</th>
                    <th class="px-3 py-2 text-left text-xs text-gray-500">Entrada</th>
                    <th class="px-3 py-2 text-left text-xs text-gray-500">Salida</th>
                    <th class="px-3 py-2 text-left text-xs text-gray-500">Esperado</th>
                    <th class="px-3 py-2 text-left text-xs text-gray-500">Estado</th>
                    <th class="px-3 py-2 text-left text-xs text-gray-500">Desc.</th>
                    <th class="px-2 py-2 text-center text-xs text-violet-600 font-semibold">O&amp;L (0-2)</th>
                    <th class="px-3 py-2 text-left text-xs text-gray-500">Justificación</th>
                </tr>
            </thead>
            <tbody>${rows}</tbody>
        </table>
        </div>
        <p class="text-xs text-gray-400 mt-2 px-1">🧹 Ingresa el puntaje de Orden y Limpieza (0-2) por día — se guarda al salir del campo.</p>
        ${d.descuento_total > 0 ? `<p class="text-sm text-red-500 font-semibold mt-3 px-1">Descuento total: ${fmt(d.descuento_total)}</p>` : ''}`;
        }


        // ── Modal justificación ──────────────────────────────────────────
        function abrirModalJustif(empId, date, tipo, isJustified, weekStart, currentReason) {
            document.getElementById('justifEmpId').value = empId;
            document.getElementById('justifDate').value = date;
            document.getElementById('justifType').value = tipo;
            document.getElementById('justifWeekStart').value = weekStart;
            document.getElementById('justifReason').value = isJustified ? currentReason : '';

            const tipoLabel = tipo === 'falta' ? 'Falta' : 'Tardanza';
            const dateLabel = new Date(date + 'T12:00:00').toLocaleDateString('es-PE', { weekday: 'long', day: 'numeric', month: 'long' });
            document.getElementById('justifModalTitle').textContent = tipoLabel + ' — ' + dateLabel;
            document.getElementById('justifModalSub').textContent = 'Empleado ID: ' + empId;

            const currBlock = document.getElementById('justifCurrentBlock');
            const quitarBlock = document.getElementById('justifQuitarBlock');
            const btnConf = document.getElementById('justifBtnConfirmar');
            btnConf.disabled = false;

            if (isJustified && currentReason) {
                currBlock.classList.remove('hidden');
                document.getElementById('justifCurrentReason').textContent = currentReason || '(sin motivo registrado)';
            } else {
                currBlock.classList.add('hidden');
            }

            if (isJustified) {
                quitarBlock.classList.remove('hidden');
                btnConf.textContent = 'Actualizar motivo';
            } else {
                quitarBlock.classList.add('hidden');
                btnConf.textContent = 'Justificar';
            }

            document.getElementById('modalJustif').classList.remove('hidden');
        }

        function cerrarModalJustif() {
            document.getElementById('modalJustif').classList.add('hidden');
            const btn = document.getElementById('justifBtnConfirmar');
            btn.disabled = false;
            btn.textContent = 'Justificar';
        }

        async function confirmarJustif(justified) {
            const empId = parseInt(document.getElementById('justifEmpId').value);
            const date = document.getElementById('justifDate').value;
            const tipo = document.getElementById('justifType').value;
            const weekStart = document.getElementById('justifWeekStart').value;
            const reason = document.getElementById('justifReason').value.trim();

            if (justified && !reason) { alert('Por favor ingresa un motivo de justificación.'); return; }

            const btn = document.getElementById('justifBtnConfirmar');
            btn.disabled = true; btn.textContent = 'Guardando...';

            const res = await fetch('/api/remuneracion/justificacion', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
                body: JSON.stringify({ employee_id: empId, date, type: tipo, justified, reason: reason || null })
            });
            const d = await res.json();
            if (!res.ok || d.error) {
                alert(d.error || 'Error al guardar la justificación.');
                btn.disabled = false; btn.textContent = 'Justificar';
                return;
            }

            cerrarModalJustif();
            abrirDetalle(empId, weekStart);
        }

        // ── Nómina Mensual ───────────────────────────────────────────────
        const MESES = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];

        function renderMesLabel() {
            document.getElementById('lblMes').textContent = MESES[nominaMonth - 1] + ' ' + nominaYear;
        }

        function cambiarMes(dir) {
            nominaMonth += dir;
            if (nominaMonth > 12) { nominaMonth = 1; nominaYear++; }
            if (nominaMonth < 1) { nominaMonth = 12; nominaYear--; }
            renderMesLabel();
            cargarNomina();
        }

        async function cargarNomina() {
            renderMesLabel();
            document.getElementById('semanaTabs').innerHTML = '<span class="text-xs text-gray-400">Cargando...</span>';
            document.getElementById('nominaSemanaCont').innerHTML = '<p class="text-center text-gray-400 text-sm py-6">Cargando...</p>';
            document.getElementById('nominaReporteBody').innerHTML = '<tr><td colspan="5" class="px-4 py-8 text-center text-gray-400 text-sm">Cargando...</td></tr>';

            const data = await fetch(`/api/nomina/mes?year=${nominaYear}&month=${nominaMonth}`).then(r => r.json());
            nominaData = data;
            document.getElementById('lblPayDate').textContent = 'Pago: ' + data.pay_date;
            document.getElementById('lblReportePago').textContent = `Total a pagar el ${data.pay_date} (sueldo neto + bonos pendientes)`;

            // Render tabs de semana
            const tabs = document.getElementById('semanaTabs');
            tabs.innerHTML = data.semanas.map(s => {
                const cls = s.is_current
                    ? 'bg-violet-600 text-white'
                    : s.is_past ? 'bg-gray-100 text-gray-600 hover:bg-gray-200'
                        : 'bg-blue-50 text-blue-500 border border-blue-200';
                const dot = s.is_current ? ' ●' : '';
                return `<button onclick="verSemanaNomina('${s.week_start}')"
                class="px-3 py-1.5 rounded-lg text-xs font-medium transition ${cls}">${s.label}${dot}</button>`;
            }).join('');

            // Auto-seleccionar semana actual
            const cur = data.semanas.find(s => s.is_current) ?? data.semanas[data.semanas.length - 1];
            if (cur) verSemanaNomina(cur.week_start);

            // Render reporte del día 1
            renderReporteNomina(data.reporte, data.pay_date);
        }

        // verSemanaNomina: carga el endpoint /semana para mostrar bono calculado
        async function verSemanaNomina(weekStart) {
            selectedWeekStart = weekStart;
            const semana = nominaData?.semanas?.find(s => s.week_start === weekStart);
            const delivMap = nominaData?.deliv_map ?? {};
            const reporte = nominaData?.reporte ?? [];
            if (!semana) return;

            const cont = document.getElementById('nominaSemanaCont');
            cont.innerHTML = `<p class="text-xs text-gray-400 mb-3">${semana.label} — ${semana.is_past ? 'Semana pasada' : semana.is_current ? 'Semana actual ●' : 'Semana futura'}</p><p class="text-xs text-violet-500">Calculando bonos...</p>`;

            // Fetch bono calculado si no está en cache
            if (!weekSemanaCache[weekStart]) {
                try {
                    const sd = await fetch(`/api/remuneracion/semana?week_start=${weekStart}`).then(r => r.json());
                    if (!sd.error) {
                        // Map: local_id -> bono_individual
                        const bonoMap = {};
                        (sd.empleados ?? []).forEach(e => { if (e.local_id) bonoMap[e.local_id] = e.bono_individual ?? 0; });
                        weekSemanaCache[weekStart] = bonoMap;
                    }
                } catch (e) { weekSemanaCache[weekStart] = {}; }
            }
            const bonoMap = weekSemanaCache[weekStart] ?? {};

            cont.innerHTML = `
        <p class="text-xs text-gray-400 mb-3">${semana.label} — ${semana.is_past ? 'Semana pasada' : semana.is_current ? '<span class="text-violet-600 font-semibold">Semana actual</span>' : 'Semana futura'}</p>
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="border-b"><tr>
                <th class="py-2 text-left text-xs text-gray-500 font-medium">Empleado</th>
                <th class="py-2 text-right text-xs text-gray-500 font-medium">Bono calculado</th>
                <th class="py-2 text-center text-xs text-gray-500 font-medium">Estado entrega</th>
                <th class="py-2 text-center text-xs text-gray-500 font-medium">Acción</th>
            </tr></thead>
            <tbody>
                ${reporte.map(emp => {
                const d = (delivMap[weekStart] ?? {})[emp.local_id];
                const bonoCalc = bonoMap[emp.local_id] ?? null;  // null = no cargado
                const delivered = d?.delivered ?? false;
                const delivAt = d?.delivered_at ?? '';
                const delMonto = d?.bonus_amount ?? 0;

                // Monto a mostrar: si hay entrega registrada, ése; si no, el calculado
                const monto = d ? delMonto : (bonoCalc ?? 0);
                const montoLabel = bonoCalc === null
                    ? `<span class="text-gray-300">—</span>`
                    : bonoCalc === 0
                        ? `<span class="text-gray-400 text-xs italic">Sin meta alcanzada</span>`
                        : `<span class="${delivered ? 'text-gray-400 line-through' : 'text-violet-700 font-semibold'}">${fmt(monto)}</span>`;

                const badge = delivered
                    ? `<span class="text-xs bg-gray-100 text-gray-500 px-2 py-0.5 rounded-full">Entregado ${delivAt}</span>`
                    : d
                        ? `<span class="text-xs bg-amber-100 text-amber-700 px-2 py-0.5 rounded-full font-medium">Pendiente</span>`
                        : `<span class="text-xs bg-blue-50 text-blue-500 px-2 py-0.5 rounded-full">No registrado</span>`;

                const btn = delivered
                    ? `<button onclick="marcarEntrega(${emp.local_id},'${weekStart}',${delMonto},false)" class="text-xs text-gray-400 hover:text-gray-600 underline">Desmarcar</button>`
                    : `<button onclick="pedirBonoYMarcar(${emp.local_id},'${weekStart}',${bonoCalc ?? 0})" class="text-xs bg-violet-600 hover:bg-violet-700 text-white px-2 py-1 rounded-lg transition">${d ? 'Marcar entregado' : 'Confirmar entrega'}</button>`;

                return `<tr class="border-b">
                        <td class="py-2 pr-4 text-sm text-gray-800">${emp.name}</td>
                        <td class="py-2 text-right">${montoLabel}</td>
                        <td class="py-2 text-center">${badge}</td>
                        <td class="py-2 text-center">${btn}</td>
                    </tr>`;
            }).join('')}
            </tbody>
        </table></div>`;
        }

        function renderReporteNomina(reporte, payDate) {
            let totNeto = 0, totEnt = 0, totPend = 0, totTotal = 0;
            const rows = reporte.map(emp => {
                totNeto += emp.sueldo_neto;
                totEnt += emp.bonos_entregados;
                totPend += emp.bonos_pendientes;
                totTotal += emp.total_dia1;
                return `<tr class="border-b hover:bg-violet-50 cursor-pointer" onclick="abrirPanelNominaEmp(${emp.local_id})">
                <td class="px-4 py-3 text-sm text-gray-800 font-medium flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 text-violet-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    ${emp.name}
                </td>
                <td class="px-4 py-3 text-right text-sm text-gray-700">${fmt(emp.sueldo_neto)}</td>
                <td class="px-4 py-3 text-right text-sm text-gray-400"><span class="line-through">${emp.bonos_entregados > 0 ? fmt(emp.bonos_entregados) : '—'}</span></td>
                <td class="px-4 py-3 text-right text-sm text-amber-600 font-medium">${emp.bonos_pendientes > 0 ? fmt(emp.bonos_pendientes) : '—'}</td>
                <td class="px-4 py-3 text-right text-sm font-bold text-violet-700">${fmt(emp.total_dia1)}</td>
            </tr>`;
            }).join('');
            document.getElementById('nominaReporteBody').innerHTML = rows || '<tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Sin empleados configurados</td></tr>';
            document.getElementById('nominaReporteFoot').innerHTML = `<tr>
            <td class="px-4 py-3 text-sm font-bold text-gray-700">TOTAL</td>
            <td class="px-4 py-3 text-right text-sm font-semibold">${fmt(totNeto)}</td>
            <td class="px-4 py-3 text-right text-sm text-gray-400">${fmt(totEnt)}</td>
            <td class="px-4 py-3 text-right text-sm text-amber-600 font-semibold">${fmt(totPend)}</td>
            <td class="px-4 py-3 text-right text-sm font-bold text-violet-700 text-base">${fmt(totTotal)}</td>
        </tr>`;
        }

        async function pedirBonoYMarcar(localId, weekStart, bonoSugerido = 0) {
            document.getElementById('modalBonoLocalId').value = localId;
            document.getElementById('modalBonoWeekStart').value = weekStart;
            document.getElementById('modalBonoMonto').value = bonoSugerido > 0 ? bonoSugerido : '';
            document.getElementById('modalBonoMonto').placeholder = bonoSugerido > 0 ? 'Calculado: ' + bonoSugerido : '0.00';
            document.getElementById('modalBono').classList.remove('hidden');
        }

        async function confirmarModalBono() {
            const localId = parseInt(document.getElementById('modalBonoLocalId').value);
            const weekStart = document.getElementById('modalBonoWeekStart').value;
            const amount = parseFloat(document.getElementById('modalBonoMonto').value);
            if (isNaN(amount) || amount < 0) { alert('Monto inválido'); return; }
            document.getElementById('modalBono').classList.add('hidden');
            await marcarEntrega(localId, weekStart, amount, true);
        }

        async function marcarEntrega(localId, weekStart, amount, delivered) {
            await fetch('/api/nomina/entrega', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
                body: JSON.stringify({ employee_local_id: localId, week_start: weekStart, bonus_amount: amount, delivered })
            });
            await cargarNomina();
            verSemanaNomina(weekStart);
        }

        function cerrarDetalle() { document.getElementById('modalDetalle').classList.add('hidden'); }
        function cerrarPaneles() { cerrarPanelSueldo(); cerrarPanelBono(); cerrarPanelNominaEmp(); }

        // ── Panel Nómina Empleado ───────────────────────────────────────
        let panelNominaEmpData = null;

        async function abrirPanelNominaEmp(localId) {
            const panel = document.getElementById('panelNominaEmp');
            panel.classList.remove('hidden');
            document.getElementById('panelOverlay').classList.remove('hidden');
            document.getElementById('panelNominaEmpBody').innerHTML = '<p class="text-center text-gray-400 py-12">Cargando...</p>';
            document.getElementById('panelNominaEmpName').textContent = '...';

            try {
                const d = await fetch(`/api/nomina/emp-mes?local_id=${localId}&year=${nominaYear}&month=${nominaMonth}`).then(r => r.json());
                if (d.error) {
                    document.getElementById('panelNominaEmpBody').innerHTML = `<p class="text-red-500 p-4">${d.error}</p>`;
                    return;
                }
                panelNominaEmpData = d;
                document.getElementById('panelNominaEmpName').textContent = d.name;
                document.getElementById('panelNominaEmpJob').textContent = d.job + (d.calendar ? ' · ' + d.calendar : '');

                // Tabs de semana
                const sc = {
                    puntual: 'bg-green-100 text-green-700', tardanza: 'bg-orange-100 text-orange-700',
                    falta: 'bg-red-100 text-red-700', dia_libre: 'bg-gray-100 text-gray-400',
                    pendiente: 'bg-blue-100 text-blue-500'
                };
                const sl = { puntual: 'Puntual', tardanza: 'Tardanza', falta: 'Falta', dia_libre: 'Libre', pendiente: 'Pendiente' };

                const semTabs = d.semanas.map((s, i) => {
                    const isCur = s.is_current;
                    const cls = isCur ? 'bg-violet-600 text-white' : s.is_past ? 'bg-gray-100 text-gray-600' : 'bg-blue-50 text-blue-400 border border-blue-200';
                    return `<button onclick="renderPanelNominaEmpSemana(${i})" data-semidx="${i}"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium transition ${cls}">${s.label}${isCur ? ' ●' : ''}</button>`;
                }).join('');

                document.getElementById('panelNominaEmpBody').innerHTML = `
            <div id="panelNominaEmpSemTabs" class="flex flex-wrap gap-2 mb-4">${semTabs}</div>
            <div id="panelNominaEmpSemCont"><p class="text-center text-gray-400 text-sm">Selecciona una semana</p></div>
            <div class="mt-4 border-t pt-4">
                <p class="text-xs text-gray-400 mb-2">Resumen del mes (pago el 1/${nominaMonth < 12 ? nominaMonth + 1 : 1}/${nominaMonth < 12 ? nominaYear : nominaYear + 1})</p>
                <div class="space-y-1 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Sueldo neto</span><span class="font-semibold">${fmt(d.sueldo_neto)}</span></div>
                    ${d.semanas.filter(s => s.bono_calculado > 0 || s.delivered).map(s =>
                    `<div class="flex justify-between text-xs">
                            <span class="text-gray-400">${s.label}: bono ${s.pierde_bono ? '<span class="text-red-400">(perd.)</span>' : ''}</span>
                            <span class="${s.delivered ? 'line-through text-gray-400' : 'text-violet-600 font-medium'}">${fmt(s.bono_calculado)}</span>
                        </div>`
                ).join('')}
                    <div class="flex justify-between font-bold border-t pt-1 mt-1">
                        <span>Total estimado día 1</span>
                        <span class="text-violet-700">${fmt(d.sueldo_neto + d.semanas.reduce((a, s) => a + (!s.delivered ? s.bono_calculado : 0), 0))}</span>
                    </div>
                </div>
            </div>`;

                // Auto-seleccionar semana actual o la primera pasada
                const curIdx = d.semanas.findIndex(s => s.is_current);
                renderPanelNominaEmpSemana(curIdx >= 0 ? curIdx : d.semanas.findIndex(s => s.is_past));
            } catch (e) {
                document.getElementById('panelNominaEmpBody').innerHTML = '<p class="text-red-400 p-4">Error al cargar datos.</p>';
            }
        }

        function renderPanelNominaEmpSemana(idx) {
            if (!panelNominaEmpData) return;
            const s = panelNominaEmpData.semanas[idx];
            const empId = panelNominaEmpData.local_id;   // local_id para justificaciones
            // Highlight tab activo
            document.querySelectorAll('#panelNominaEmpSemTabs button').forEach((b, i) => {
                b.classList.toggle('ring-2', i === idx);
                b.classList.toggle('ring-violet-400', i === idx);
            });
            const sc = {
                puntual: 'bg-green-100 text-green-700', tardanza: 'bg-orange-100 text-orange-700',
                falta: 'bg-red-100 text-red-700', dia_libre: 'bg-gray-100 text-gray-400',
                pendiente: 'bg-blue-100 text-blue-500'
            };
            const sl = { puntual: 'Puntual', tardanza: 'Tardanza', falta: 'Falta', dia_libre: 'Libre', pendiente: 'Pendiente' };

            const rows = s.days.map(day => {
                const color = sc[day.status] ?? 'bg-gray-100 text-gray-400';
                const label = sl[day.status] ?? day.status;
                let action = '';
                if (day.is_day_off || day.status === 'pendiente' || day.status === 'puntual') {
                    action = day.justif_reason ? `<span class="text-xs text-green-600 italic">${day.justif_reason}</span>` : '';
                } else if (day.status === 'falta') {
                    const jKey = `nem_${empId}_${day.date}_falta`;
                    action = day.justif_falta
                        ? `<button onclick="quitarJustifEmpMes(${empId},'${day.date}','falta',${idx})" class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-0.5 rounded-lg transition">Quitar justif.</button>`
                        : `<div class="flex gap-1">
                        <input id="${jKey}" type="text" placeholder="Motivo..." class="inp text-xs w-24 py-0.5">
                        <button onclick="justifEmpMes(${empId},'${day.date}','falta',document.getElementById('${jKey}').value,${idx})" class="text-xs bg-green-500 hover:bg-green-600 text-white px-2 py-0.5 rounded-lg">Ok</button>
                      </div>`;
                } else if (day.status === 'tardanza') {
                    const jKey = `nem_${empId}_${day.date}_tard`;
                    action = day.justif_tardanza
                        ? `<button onclick="quitarJustifEmpMes(${empId},'${day.date}','tardanza',${idx})" class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-0.5 rounded-lg transition">Quitar justif.</button>`
                        : `<div class="flex gap-1">
                        <input id="${jKey}" type="text" placeholder="Motivo..." class="inp text-xs w-24 py-0.5">
                        <button onclick="justifEmpMes(${empId},'${day.date}','tardanza',document.getElementById('${jKey}').value,${idx})" class="text-xs bg-orange-400 hover:bg-orange-500 text-white px-2 py-0.5 rounded-lg">Ok</button>
                      </div>`;
                }
                const minsTag = day.minutos_tardanza > 0 ? `<span class="text-xs text-orange-400 ml-1">${day.minutos_tardanza}m</span>` : '';
                return `<tr class="border-b">
                <td class="px-2 py-1.5 text-xs font-medium text-gray-700 whitespace-nowrap">${day.label}</td>
                <td class="px-2 py-1.5 text-xs text-gray-500">${day.check_in ?? '—'}</td>
                <td class="px-2 py-1.5 text-xs text-gray-400">${day.expected_in ?? '—'}</td>
                <td class="px-2 py-1.5"><span class="text-xs px-1.5 py-0.5 rounded-full font-medium ${color}">${label}</span>${minsTag}</td>
                <td class="px-2 py-1.5">${action}</td>
            </tr>`;
            }).join('');

            const bonoInfo = s.is_current
                ? `<p class="text-xs text-blue-500 mt-1">Semana en curso — bono a&uacute;n no finalizado</p>`
                : s.pierde_bono
                    ? `<p class="text-xs text-red-500 mt-1">Pierde bono: ${s.faltas} falta(s), ${s.tardanzas} tardanza(s)</p>`
                    : s.bono_calculado > 0
                        ? `<p class="text-xs text-violet-600 font-semibold mt-1">Bono calculado: ${fmt(s.bono_calculado)} ${s.delivered ? '(entregado ' + s.delivered_at + ')' : '(pendiente)'}</p>`
                        : `<p class="text-xs text-gray-400 mt-1">Sin meta alcanzada esta semana</p>`;

            document.getElementById('panelNominaEmpSemCont').innerHTML = `
        <div class="text-xs text-gray-400 mb-2">${s.label} — Ventas: <span class="text-violet-600 font-semibold">${fmt(s.ventas)}</span>${bonoInfo}</div>
        <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b"><tr>
                <th class="px-2 py-1.5 text-left text-xs text-gray-400">Día</th>
                <th class="px-2 py-1.5 text-left text-xs text-gray-400">Entrada</th>
                <th class="px-2 py-1.5 text-left text-xs text-gray-400">Esperado</th>
                <th class="px-2 py-1.5 text-left text-xs text-gray-400">Estado</th>
                <th class="px-2 py-1.5 text-left text-xs text-gray-400">Justificación</th>
            </tr></thead>
            <tbody>${rows}</tbody>
        </table></div>`;
        }

        async function justifEmpMes(localId, date, tipo, reason, semIdx) {
            if (!reason.trim()) { alert('Ingresa un motivo'); return; }
            const res = await fetch('/api/remuneracion/justificacion', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
                body: JSON.stringify({ employee_id: panelNominaEmpData.local_id || panelNominaEmpData.emp_id, date, type: tipo, justified: true, reason })
            });
            const d = await res.json();
            if (!res.ok || d.error) { alert(d.error || 'Error.'); return; }
            // Recarga datos del empleado
            await abrirPanelNominaEmp(localId);
            renderPanelNominaEmpSemana(semIdx);
        }

        async function quitarJustifEmpMes(localId, date, tipo, semIdx) {
            const res = await fetch('/api/remuneracion/justificacion', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
                body: JSON.stringify({ employee_id: panelNominaEmpData.local_id || panelNominaEmpData.emp_id, date, type: tipo, justified: false, reason: null })
            });
            const d = await res.json();
            if (!res.ok || d.error) { alert(d.error || 'Error.'); return; }
            await abrirPanelNominaEmp(localId);
            renderPanelNominaEmpSemana(semIdx);
        }

        function cerrarPanelNominaEmp() {
            document.getElementById('panelNominaEmp').classList.add('hidden');
            document.getElementById('panelOverlay').classList.add('hidden');
            panelNominaEmpData = null;
        }

        // Init
        document.getElementById('lblSemana').textContent = fmtSemana(getWeekStart(0));
        cargarResumen();
    </script>

    {{-- ═══ PANEL NÓMINA EMPLEADO ═══ --}}
    <div id="panelNominaEmp"
        class="hidden fixed inset-y-0 right-0 w-full sm:w-[480px] bg-white shadow-2xl z-40 flex flex-col">
        <div class="flex items-center justify-between p-4 border-b flex-shrink-0">
            <div>
                <h3 id="panelNominaEmpName" class="font-bold text-gray-800 text-sm"></h3>
                <p id="panelNominaEmpJob" class="text-xs text-gray-400 mt-0.5"></p>
            </div>
            <button onclick="cerrarPanelNominaEmp()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div id="panelNominaEmpBody" class="flex-1 overflow-y-auto p-4"></div>
    </div>

    {{-- ═══ MODAL BONO SEMANAL ═══ --}}
    <div id="modalBono" class="hidden fixed inset-0 bg-black/60 z-[70] flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm">
            <div class="flex items-center justify-between p-4 border-b">
                <h3 class="font-bold text-gray-800 text-sm">Registrar bono semanal</h3>
                <button onclick="document.getElementById('modalBono').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="p-5 space-y-4">
                <input type="hidden" id="modalBonoLocalId">
                <input type="hidden" id="modalBonoWeekStart">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Monto del bono (S/)</label>
                    <input id="modalBonoMonto" type="number" min="0" step="5" class="inp w-full" placeholder="0.00">
                    <p class="text-xs text-gray-400 mt-1">Ingresa el monto ganado por el empleado esta semana.</p>
                </div>
                <div class="flex gap-3">
                    <button onclick="document.getElementById('modalBono').classList.add('hidden')"
                        class="flex-1 border border-gray-200 text-gray-600 py-2 rounded-xl text-sm font-medium hover:bg-gray-50 transition">Cancelar</button>
                    <button onclick="confirmarModalBono()"
                        class="flex-1 bg-violet-600 hover:bg-violet-700 text-white py-2 rounded-xl text-sm font-semibold transition">Confirmar</button>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>