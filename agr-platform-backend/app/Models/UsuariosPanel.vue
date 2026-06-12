<template>
  <div class="usuarios-panel">
    <div class="panel-header">
      <div>
        <h2>Usuarios</h2>
        <p>Administra accesos, roles y empresas asignadas</p>
      </div>

      <q-btn
        label="Nuevo usuario"
        icon="person_add"
        class="btn-primary"
        @click="abrirDialogo()"
      />
    </div>

    <q-card class="panel-card">
      <q-table
        :rows="usuarios"
        :columns="columns"
        row-key="id"
        flat
        dark
        :loading="loading"
      >
        <template #body-cell-empresa="props">
          <q-td :props="props">
            {{ props.row.empresa?.nombre || 'AGR Studio' }}
          </q-td>
        </template>

        <template #body-cell-rol="props">
          <q-td :props="props">
            <q-chip color="purple" text-color="white" dense>
              {{ props.row.rol }}
            </q-chip>
          </q-td>
        </template>

        <template #body-cell-activo="props">
          <q-td :props="props">
            <q-chip
              :color="props.row.activo ? 'positive' : 'negative'"
              text-color="white"
              dense
            >
              {{ props.row.activo ? 'Activo' : 'Inactivo' }}
            </q-chip>
          </q-td>
        </template>

        <template #body-cell-acciones="props">
          <q-td :props="props">
            <q-btn dense flat icon="edit" color="warning" @click="abrirDialogo(props.row)" />
            <q-btn dense flat icon="delete" color="negative" @click="eliminarUsuario(props.row)" />
          </q-td>
        </template>
      </q-table>
    </q-card>

    <q-dialog v-model="dialogo">
      <q-card class="dialog-card">
        <q-card-section>
          <div class="text-h6">
            {{ editando ? 'Editar usuario' : 'Nuevo usuario' }}
          </div>
        </q-card-section>

        <q-card-section>
          <q-input v-model="form.nombre" label="Nombre" dark outlined class="q-mb-md" />
          <q-input v-model="form.usuario" label="Usuario" dark outlined class="q-mb-md" />
          <q-input v-model="form.email" label="Email" dark outlined class="q-mb-md" />

          <q-input
            v-model="form.password"
            :label="editando ? 'Nueva contraseña (opcional)' : 'Contraseña'"
            type="password"
            dark
            outlined
            class="q-mb-md"
          />

          <q-select
            v-model="form.rol"
            :options="roles"
            label="Rol"
            dark
            outlined
            class="q-mb-md"
          />

          <q-select
            v-model="form.empresa_id"
            :options="empresasOptions"
            label="Empresa"
            dark
            outlined
            emit-value
            map-options
            clearable
            class="q-mb-md"
          />

          <q-toggle
            v-model="form.activo"
            label="Usuario activo"
            color="positive"
          />
        </q-card-section>

        <q-card-actions align="right">
          <q-btn flat label="Cancelar" color="grey" v-close-popup />
          <q-btn
            label="Guardar"
            icon="save"
            class="btn-primary"
            :loading="guardando"
            @click="guardarUsuario"
          />
        </q-card-actions>
      </q-card>
    </q-dialog>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useQuasar } from 'quasar'

const $q = useQuasar()
const API_URL = 'http://127.0.0.1:8000/api'

const usuarios = ref([])
const empresas = ref([])
const loading = ref(false)
const guardando = ref(false)
const dialogo = ref(false)
const editando = ref(false)
const usuarioId = ref(null)

const roles = ['super_admin', 'admin_empresa', 'usuario']

const form = ref({
  empresa_id: null,
  nombre: '',
  usuario: '',
  email: '',
  password: '',
  rol: 'usuario',
  activo: true
})

const columns = [
  { name: 'id', label: 'ID', field: 'id', align: 'left' },
  { name: 'nombre', label: 'Nombre', field: 'nombre', align: 'left' },
  { name: 'usuario', label: 'Usuario', field: 'usuario', align: 'left' },
  { name: 'email', label: 'Email', field: 'email', align: 'left' },
  { name: 'empresa', label: 'Empresa', field: 'empresa', align: 'left' },
  { name: 'rol', label: 'Rol', field: 'rol', align: 'center' },
  { name: 'activo', label: 'Estado', field: 'activo', align: 'center' },
  { name: 'acciones', label: 'Acciones', field: 'acciones', align: 'center' }
]

const empresasOptions = ref([])

function token() {
  return localStorage.getItem('agr_token')
}

async function cargarUsuarios() {
  loading.value = true

  try {
    const response = await fetch(`${API_URL}/usuarios-sistema`, {
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${token()}`
      }
    })

    const data = await response.json()

    if (!response.ok) {
      throw new Error('No se pudieron cargar los usuarios')
    }

    usuarios.value = data
  } catch (error) {
    $q.notify({ type: 'negative', message: error.message })
  } finally {
    loading.value = false
  }
}

async function cargarEmpresas() {
  try {
    const response = await fetch(`${API_URL}/empresas`, {
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${token()}`
      }
    })

    const data = await response.json()

    if (!response.ok) {
      throw new Error('No se pudieron cargar las empresas')
    }

    empresas.value = data

    empresasOptions.value = data.map((empresa) => ({
      label: empresa.nombre,
      value: empresa.id
    }))
  } catch (error) {
    $q.notify({ type: 'negative', message: error.message })
  }
}

function abrirDialogo(usuario = null) {
  if (usuario) {
    editando.value = true
    usuarioId.value = usuario.id

    form.value = {
      empresa_id: usuario.empresa_id,
      nombre: usuario.nombre,
      usuario: usuario.usuario,
      email: usuario.email,
      password: '',
      rol: usuario.rol,
      activo: Boolean(usuario.activo)
    }
  } else {
    editando.value = false
    usuarioId.value = null

    form.value = {
      empresa_id: null,
      nombre: '',
      usuario: '',
      email: '',
      password: '',
      rol: 'usuario',
      activo: true
    }
  }

  dialogo.value = true
}

async function guardarUsuario() {
  if (!form.value.nombre || !form.value.usuario) {
    $q.notify({
      type: 'warning',
      message: 'Nombre y usuario son obligatorios'
    })
    return
  }

  if (!editando.value && !form.value.password) {
    $q.notify({
      type: 'warning',
      message: 'La contraseña es obligatoria'
    })
    return
  }

  guardando.value = true

  try {
    const url = editando.value
      ? `${API_URL}/usuarios-sistema/${usuarioId.value}`
      : `${API_URL}/usuarios-sistema`

    const method = editando.value ? 'PUT' : 'POST'

    const payload = { ...form.value }

    if (editando.value && !payload.password) {
      delete payload.password
    }

    const response = await fetch(url, {
      method,
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
        Authorization: `Bearer ${token()}`
      },
      body: JSON.stringify(payload)
    })

    const data = await response.json()

    if (!response.ok) {
      throw new Error(data.message || 'No se pudo guardar el usuario')
    }

    $q.notify({
      type: 'positive',
      message: data.message
    })

    dialogo.value = false
    await cargarUsuarios()
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: error.message
    })
  } finally {
    guardando.value = false
  }
}

async function eliminarUsuario(usuario) {
  const confirmar = confirm(`¿Eliminar el usuario ${usuario.nombre}?`)

  if (!confirmar) return

  try {
    const response = await fetch(`${API_URL}/usuarios-sistema/${usuario.id}`, {
      method: 'DELETE',
      headers: {
        Accept: 'application/json',
        Authorization: `Bearer ${token()}`
      }
    })

    const data = await response.json()

    if (!response.ok) {
      throw new Error(data.message || 'No se pudo eliminar')
    }

    $q.notify({
      type: 'positive',
      message: data.message
    })

    await cargarUsuarios()
  } catch (error) {
    $q.notify({
      type: 'negative',
      message: error.message
    })
  }
}

onMounted(async () => {
  await cargarEmpresas()
  await cargarUsuarios()
})
</script>

<style scoped>
.usuarios-panel {
  margin-top: 42px;
}

.panel-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 18px;
}

.panel-header h2 {
  margin: 0;
  font-size: 32px;
  font-weight: 900;
}

.panel-header p {
  margin: 4px 0 0;
  opacity: 0.65;
}

.panel-card,
.dialog-card {
  background: rgba(15, 23, 42, 0.92);
  color: white;
  border-radius: 24px;
  border: 1px solid rgba(255, 255, 255, 0.12);
}

.dialog-card {
  width: 560px;
  max-width: 95vw;
}

.btn-primary {
  background: linear-gradient(135deg, #7c3aed, #2563eb);
  color: white;
  border-radius: 12px;
  font-weight: 700;
}
</style>