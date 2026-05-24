# AlCorte — Usar el sistema en móvil (conectado a la web)

Esta guía explica cómo usar **la misma aplicación web** que corre en tu PC (Laragon) desde el **celular**, tablet o cualquier dispositivo en la misma red Wi‑Fi. No es una app distinta: el móvil abre el sitio en el navegador y comparte la misma base de datos y el mismo panel de administración.

---

## Resumen rápido

| Dónde | URL de ejemplo |
|--------|----------------|
| PC (escritorio) | `http://localhost/alcorte/` |
| **Móvil Android (recomendado)** | `http://TU_IP_LOCAL/alcorte/` |
| Panel admin | `http://TU_IP_LOCAL/alcorte/admin.php` |
| Modo TV | `http://TU_IP_LOCAL/alcorte/tv_mode.php` |
| QR y ayuda | `http://localhost/alcorte/acceso-movil.php` |

Sustituye `TU_IP_LOCAL` por la IP de tu computadora (ejemplo: `192.168.1.105`).

---

## Requisitos

1. **Laragon** instalado y funcionando (Apache + MySQL en verde).
2. Base de datos **`barberia_db`** importada (`barberia_db.sql` en la raíz del proyecto).
3. **PC y móvil en la misma red Wi‑Fi** (no uses datos móviles del teléfono para probar en local).
4. Carpeta del proyecto en: `C:\laragon\www\.barberia\`

---

## Paso 1 — Encender el servidor en el PC

1. Abre **Laragon**.
2. Pulsa **Start All** (Apache y MySQL deben quedar activos).
3. En el navegador del PC abre:  
   `http://localhost/alcorte/`
4. Comprueba el admin:  
   `http://localhost/alcorte/admin.php`

Si en el PC no abre, corrige eso antes de probar en el móvil.

---

## Paso 2 — Obtener la IP de tu computadora

La IP es la “dirección” de tu PC dentro de la red Wi‑Fi.

### Opción A — Laragon

1. Clic derecho en el icono de **Laragon** (bandeja del sistema).
2. Busca la opción que muestre la IP o el menú **www** / información de red (según versión).
3. Anota la IP que empiece por `192.168.x.x` o `10.x.x.x`.

### Opción B — Windows (recomendado)

1. Presiona `Win + R`, escribe `cmd` y Enter.
2. Ejecuta:

```bat
ipconfig
```

3. Busca **Adaptador de LAN inalámbrica Wi-Fi** (o similar).
4. Copia **Dirección IPv4**, por ejemplo: `192.168.1.105`.

> **Importante:** No uses la IP `172.25.x.x` si aparece en “vEthernet” o “Default Switch”; esa es virtual. Usa la de **Wi‑Fi**.

### Opción C — Página de ayuda del proyecto

Con Laragon encendido, abre en el PC:

`http://localhost/alcorte/acceso-movil.php`

Ahí verás la URL lista para copiar, el código QR y las IPs detectadas.

---

## Paso 3 — Permitir conexiones desde la red (firewall)

Windows puede bloquear que otros dispositivos entren a Apache.

1. Abre **Seguridad de Windows** → **Firewall y protección de red**.
2. **Permitir una aplicación a través del firewall**.
3. Busca **Apache HTTP Server** o **httpd** (Laragon) y marca **Privada**.
4. Si no aparece, **Permitir otra aplicación** y agrega:
   - `C:\laragon\bin\apache\httpd-2.4.xx\bin\httpd.exe`  
   (la carpeta `httpd-2.4.xx` puede variar según tu versión).

**Alternativa:** ejecuta como administrador el script en esta carpeta:

`doc\permitir-firewall-android.bat`

---

## Paso 4 — Abrir el sistema en el móvil (Android)

1. Conecta el **celular a la misma Wi‑Fi** que el PC (no uses datos móviles).
2. En el **PC** abre: `http://localhost/alcorte/acceso-movil.php`
3. **Escanea el código QR** con la cámara de Android o Chrome.
4. O escribe manualmente (con tu IP real):

```
http://192.168.1.105/alcorte/
```

> **Importante para Android**
> - Usa **`http://`** (sin **s** — si pones `https` no abrirá).
> - Usa la ruta **`/alcorte/`** (sin punto al inicio).
> - No uses la IP `172.25.x.x` (es red virtual de Windows).

Deberías ver la pantalla de reservas **AlCorte** (igual que en el PC).

### Enlaces útiles en el móvil

| Pantalla | URL |
|----------|-----|
| Reservas (clientes) | `http://TU_IP/alcorte/` |
| Buscar mis citas | `http://TU_IP/alcorte/buscar_citas.php` |
| Login / registro | `http://TU_IP/alcorte/login.php` |
| Panel administrador | `http://TU_IP/alcorte/admin.php` |
| Pantalla TV barbería | `http://TU_IP/alcorte/tv_mode.php` |

Todo usa la **misma base de datos** `barberia_db` del PC: una cita hecha en el móvil aparece en el admin del escritorio al instante.

---

## Paso 5 — Instalar acceso directo en la pantalla de inicio (opcional)

### Android (Chrome)

1. Abre la URL del sistema en Chrome.
2. Menú **⋮** → **Añadir a pantalla de inicio** o **Instalar aplicación**.
3. Confirma el nombre **AlCorte**.

### iPhone (Safari)

1. Abre la URL en Safari.
2. Botón **Compartir** → **Añadir a inicio**.
3. Pulsa **Añadir**.

---

## Paso 6 — Verificar que móvil y web están conectados

1. En el **móvil**, reserva una cita de prueba.
2. En el **PC**, abre `http://localhost/alcorte/admin.php`.
3. En **Agenda** → **Por verificar** debe aparecer la cita nueva.

---

## Credenciales de administrador

Tras importar `barberia_db.sql`:

| Campo | Valor |
|--------|--------|
| Usuario | `admin` |
| Contraseña | La definida en el dump (restablecer desde phpMyAdmin si no entra). |

Login: `http://TU_IP/alcorte/login.php`

---

## Solución de problemas

### El móvil no carga la página (Android)

| Causa | Qué hacer |
|--------|-----------|
| Wi‑Fi distinta | Misma red; desactiva **datos móviles**. |
| URL con `https` | Solo **`http://`** en red local. |
| Ruta incorrecta | Usa **`/alcorte/`** no solo la IP. |
| IP `172.25.x.x` | Usa **`192.168.x.x`** de Wi‑Fi (`ipconfig`). |
| Laragon apagado | **Start All** en Laragon. |
| Firewall | Apache → red **privada** o `doc\permitir-firewall-android.bat` |
| VPN en el móvil | Desactívala. |
| Aislamiento AP del router | Desactiva “aislamiento de clientes”. |

### Carga en el PC pero no en el móvil

1. En Android: `http://TU_IP/alcorte/red-test.php` — si ves `"ok": true`, la red funciona.
2. Usa **Chrome** en Android.
3. Copia la URL desde `acceso-movil.php`.

### Error de base de datos

1. Laragon → MySQL en verde.
2. Importa de nuevo `barberia_db.sql` desde la raíz del proyecto.

### La IP cambió

Repite el **Paso 2** y actualiza el marcador del móvil.

---

## Acceso desde internet — opcional

Para abrir desde **4G u otra red** hace falta túnel seguro (ngrok, Cloudflare Tunnel) o hosting con HTTPS. En local usa solo la IP Wi‑Fi.

---

## Archivos del proyecto

| Archivo | Función |
|---------|---------|
| `db.php` | Conexión MySQL |
| `barberia_db.sql` | Base de datos inicial |
| `doc/` | Esta documentación |
| `manifest.json` | Icono al instalar en el móvil |

`db.php` usa `localhost` para MySQL: la base **siempre corre en el PC**.

---

## Checklist final

- [ ] Laragon: Apache + MySQL activos  
- [ ] `http://localhost/alcorte/` funciona en el PC  
- [ ] IP Wi‑Fi anotada (`acceso-movil.php`)  
- [ ] Firewall permite Apache  
- [ ] Móvil en la misma Wi‑Fi  
- [ ] `http://TU_IP/alcorte/` abre en el móvil  
- [ ] Cita de prueba visible en `admin.php`  

---

## URLs de ejemplo (IP 192.168.1.105)

```
Reservas:     http://192.168.1.105/alcorte/
Admin:        http://192.168.1.105/alcorte/admin.php
Acceso móvil: http://192.168.1.105/alcorte/acceso-movil.php
Test red:     http://192.168.1.105/alcorte/red-test.php
```

---

*Documentación en `doc/` — Proyecto AlCorte.*
