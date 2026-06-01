<x-app-layout>


    <style>
        .badge-admin         { background: #eef2ff; color: #4338ca; border: 1px solid #c7d2fe; }
        .badge-administrador { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .badge-almacen       { background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa; }
        .badge-vendedor      { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
        .badge-limpieza      { background: #fdf4ff; color: #a21caf; border: 1px solid #f5d0fe; }

        .modal-overlay {
            position: fixed; inset: 0; background: rgba(0,0,0,0.5);
            display: flex; align-items: center; justify-content: center;
            z-index: 999; backdrop-filter: blur(4px);
            opacity: 0; pointer-events: none; transition: opacity 0.2s;
        }
        .modal-overlay.active { opacity: 1; pointer-events: all; }
        .modal-box {
            background: white; border-radius: 1.25rem; padding: 2rem;
            width: 100%; max-width: 480px; box-shadow: 0 25px 60px rgba(0,0,0,0.2);
            transform: translateY(12px); transition: transform 0.25s;
        }
        .modal-overlay.active .modal-box { transform: translateY(0); }

        .stat-card {
            background: white; border-radius: 1rem; padding: 1.25rem 1.5rem;
            border: 1px solid #e5e7eb; display: flex; align-items: center; gap: 1rem;
        }

        .role-icon { width: 2.75rem; height: 2.75rem; border-radius: .75rem; display: flex; align-items: center; justify-content: center; }

        .table-row { transition: background 0.15s; }
        .table-row:hover { background: #f8fafc; }
    </style>

    <div class="py-8 px-6 max-w-7xl mx-auto">
        <div class="flex items-center justify-between mb-8">
            <div class="flex items-center gap-3">
                <div class="bg-indigo-600 text-white rounded-xl p-2 shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Configuración del Sistema</h1>
                    <p class="text-gray-500 mt-1">Gestión de usuarios y roles</p>
                </div>
            </div>
            <button onclick="openModal('modal-crear')"
                class="flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold px-5 py-2.5 rounded-xl transition shadow-sm text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Nuevo Usuario
            </button>
        </div>

        {{-- Flash messages --}}
        @if(session('success'))
        <div id="flash-success" class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl mb-6 text-sm">
            <svg class="w-5 h-5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('success') }}
            <button onclick="document.getElementById('flash-success').remove()" class="ml-auto text-emerald-500 hover:text-emerald-700">✕</button>
        </div>
        @endif
        @if(session('error'))
        <div id="flash-error" class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-xl mb-6 text-sm">
            <svg class="w-5 h-5 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('error') }}
            <button onclick="document.getElementById('flash-error').remove()" class="ml-auto text-red-500 hover:text-red-700">✕</button>
        </div>
        @endif

        {{-- Stats de roles --}}
        <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
            @php
                $roleConfig = [
                    'admin'         => ['label' => 'Super Admins',    'color' => '#4338ca', 'bg' => '#eef2ff', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                    'administrador' => ['label' => 'Administradores', 'color' => '#1d4ed8', 'bg' => '#eff6ff', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z'],
                    'almacen'       => ['label' => 'Almacén',         'color' => '#c2410c', 'bg' => '#fff7ed', 'icon' => 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8'],
                    'vendedor'      => ['label' => 'Vendedores',      'color' => '#15803d', 'bg' => '#f0fdf4', 'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'],
                    'limpieza'      => ['label' => 'Limpieza',        'color' => '#a21caf', 'bg' => '#fdf4ff', 'icon' => 'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z'],
                ];
            @endphp
            @foreach($roleConfig as $roleKey => $cfg)
            <div class="stat-card">
                <div class="role-icon" style="background: {{ $cfg['bg'] }};">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="{{ $cfg['color'] }}">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $cfg['icon'] }}" />
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-gray-900">{{ $roleStats[$roleKey] }}</p>
                    <p class="text-xs text-gray-500">{{ $cfg['label'] }}</p>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Tabla de usuarios --}}
        <div class="bg-white rounded-2xl shadow border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-semibold text-gray-800">Usuarios del Sistema</h3>
                <span class="text-sm text-gray-400">{{ $users->count() }} usuarios en total</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wide">
                            <th class="text-left px-6 py-3 font-medium">Usuario</th>
                            <th class="text-left px-6 py-3 font-medium">Correo</th>
                            <th class="text-left px-6 py-3 font-medium">Rol</th>
                            <th class="text-left px-6 py-3 font-medium">Registrado</th>
                            <th class="text-right px-6 py-3 font-medium">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach($users as $user)
                        <tr class="table-row">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-white text-sm"
                                        style="background: {{ ['admin'=>'#6366f1','administrador'=>'#3b82f6','almacen'=>'#f97316','vendedor'=>'#22c55e','limpieza'=>'#d946ef'][$user->role] ?? '#6b7280' }};">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $user->name }}</p>
                                        @if($user->id === auth()->id())
                                        <span class="text-xs text-indigo-500 font-medium">← Tú</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-600">{{ $user->email }}</td>
                            <td class="px-6 py-4">
                                <span class="badge-{{ $user->role }} inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold capitalize">
                                    {{ ucfirst($user->role) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-gray-400 text-xs">{{ $user->created_at->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button onclick="openEditModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ $user->email }}', '{{ $user->role }}')"
                                        class="p-2 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition" title="Editar">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    @if($user->id !== auth()->id())
                                    <button onclick="confirmDelete({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                        class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Eliminar">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MODAL: Crear usuario --}}
    <div id="modal-crear" class="modal-overlay" onclick="closeModalOnBg(event, 'modal-crear')">
        <div class="modal-box">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-900">Nuevo Usuario</h3>
                <button onclick="closeModal('modal-crear')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form method="POST" action="{{ route('config.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre completo</label>
                    <input type="text" name="name" required placeholder="Ej: Juan Pérez"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
                    <input type="email" name="email" required placeholder="correo@tecnigas.com"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
                    <select name="role" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white">
                        <option value="">— Seleccionar rol —</option>
                        @foreach($roles as $rol)
                        <option value="{{ $rol }}">{{ ucfirst($rol) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Contraseña</label>
                    <input type="password" name="password" required placeholder="Mínimo 8 caracteres"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar contraseña</label>
                    <input type="password" name="password_confirmation" required placeholder="Repite la contraseña"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeModal('modal-crear')"
                        class="flex-1 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50 transition">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition">
                        Crear Usuario
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL: Editar usuario --}}
    <div id="modal-editar" class="modal-overlay" onclick="closeModalOnBg(event, 'modal-editar')">
        <div class="modal-box">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-gray-900">Editar Usuario</h3>
                <button onclick="closeModal('modal-editar')" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <form id="form-editar" method="POST" action="" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre completo</label>
                    <input type="text" id="edit-name" name="name" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Correo electrónico</label>
                    <input type="email" id="edit-email" name="email" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
                    <select id="edit-role" name="role" required
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent bg-white">
                        @foreach($roles as $rol)
                        <option value="{{ $rol }}">{{ ucfirst($rol) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="bg-amber-50 border border-amber-200 rounded-lg p-3">
                    <p class="text-xs text-amber-700 font-medium mb-2">⚠️ Cambio de contraseña (opcional)</p>
                    <div class="space-y-2">
                        <input type="password" name="password" placeholder="Nueva contraseña (dejar vacío para no cambiar)"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                        <input type="password" name="password_confirmation" placeholder="Confirmar nueva contraseña"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-transparent">
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="closeModal('modal-editar')"
                        class="flex-1 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50 transition">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700 transition">
                        Guardar Cambios
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL: Confirmar eliminar --}}
    <div id="modal-eliminar" class="modal-overlay" onclick="closeModalOnBg(event, 'modal-eliminar')">
        <div class="modal-box" style="max-width: 400px;">
            <div class="text-center mb-6">
                <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900">¿Eliminar usuario?</h3>
                <p class="text-sm text-gray-500 mt-1">Esto eliminará permanentemente a <strong id="delete-name" class="text-gray-800"></strong>. Esta acción no se puede deshacer.</p>
            </div>
            <form id="form-eliminar" method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="flex gap-3">
                    <button type="button" onclick="closeModal('modal-eliminar')"
                        class="flex-1 px-4 py-2 rounded-lg border border-gray-300 text-gray-700 text-sm font-medium hover:bg-gray-50 transition">
                        Cancelar
                    </button>
                    <button type="submit"
                        class="flex-1 px-4 py-2 rounded-lg bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition">
                        Sí, eliminar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(id) {
            document.getElementById(id).classList.add('active');
        }
        function closeModal(id) {
            document.getElementById(id).classList.remove('active');
        }
        function closeModalOnBg(e, id) {
            if (e.target === document.getElementById(id)) closeModal(id);
        }

        function openEditModal(id, name, email, role) {
            document.getElementById('edit-name').value  = name;
            document.getElementById('edit-email').value = email;
            document.getElementById('edit-role').value  = role;
            document.getElementById('form-editar').action = `/config/usuarios/${id}`;
            openModal('modal-editar');
        }

        function confirmDelete(id, name) {
            document.getElementById('delete-name').textContent = name;
            document.getElementById('form-eliminar').action = `/config/usuarios/${id}`;
            openModal('modal-eliminar');
        }

        // Auto-close flash after 4s
        setTimeout(() => {
            ['flash-success', 'flash-error'].forEach(id => {
                const el = document.getElementById(id);
                if (el) el.remove();
            });
        }, 4000);
    </script>
</x-app-layout>
