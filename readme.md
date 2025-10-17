
# 🏡 Masia Can Cruz – Sistema de Reservas

Aplicación web desarrollada con **Laravel** para la **gestión de reservas** de la Masia Can Cruz.  
Permite que los clientes envíen solicitudes de reserva y que el administrador gestione su ciclo de vida: creación, confirmación y cancelación, con notificaciones automáticas por correo electrónico.

---

## 🎯 Objetivo de negocio

El propósito del sistema es ofrecer un **canal directo de reservas** para los huéspedes de la Masia Can Cruz, eliminando intermediarios y simplificando la gestión.  
Desde una interfaz sencilla, los clientes pueden registrar sus datos y fechas de estancia, mientras que el administrador puede revisar, aprobar o cancelar cada solicitud.

### Flujo de negocio principal

1. **Creación de reserva**
   - El cliente rellena un formulario con su nombre, email, fechas de entrada y salida, y un mensaje opcional.
   - La aplicación registra la reserva en estado *pendiente*.
   - Se envía un correo de confirmación de recepción al cliente.

2. **Gestión por parte del administrador**
   - El administrador accede a un panel con todas las reservas.
   - Puede **editar**, **confirmar** o **eliminar** una reserva.

3. **Confirmación o cancelación**
   - Al confirmar, el cliente recibe un email con los detalles de la reserva aprobada.
   - Al cancelar, se notifica al cliente la cancelación por correo electrónico.

4. **Notificaciones automáticas**
   - Los correos se generan mediante plantillas HTML personalizadas para cada etapa del proceso.

---

## 💼 Roles del sistema

| Rol | Permisos principales |
|-----|----------------------|
| **Cliente (invitado)** | Crear nuevas reservas. |
| **Administrador (usuario autenticado)** | Listar, editar, confirmar y cancelar reservas. |

> El acceso al panel de gestión requiere autenticación.

---

## 🧩 Componentes principales

### Modelos

- **`Reserva`**  
  Representa una reserva realizada por un cliente.  
  Contiene información básica del cliente y fechas de la estancia.

- **`User`**  
  Usuario autenticado (administrador) con autenticación estándar de Laravel.

### Controladores

- **`ReservaController`**
  - `index()` → lista todas las reservas (solo admin).  
  - `create()` → muestra el formulario de nueva reserva (público).  
  - `store()` → valida y guarda una reserva nueva, enviando un correo de recepción.  
  - `edit()` / `update()` → permite modificar una reserva existente.  
  - `confirmReservation()` → marca una reserva como confirmada y envía el correo de confirmación.  
  - `destroy()` → elimina una reserva y envía el correo de cancelación.

- **`HomeController`**  
  Redirige la raíz de la aplicación al listado de reservas.

### Mails

Todos los correos se basan en clases `Mailable` y plantillas Blade:

| Clase | Plantilla | Descripción |
|--------|------------|-------------|
| `PendingEmail` | `emailPendingReserva` | Avisa al cliente que su reserva fue recibida. |
| `ReserveConfirmation` | `emailConfirmationReserva` | Confirma la reserva al cliente. |
| `Cancellation` | `emailCancellationReserva` | Notifica la cancelación. |
| `AdminEmail` | `emailConfirmationReservaAdmin` | (Preparado) Aviso al administrador. |

---

## 💻 Vistas principales

| Vista | Descripción |
|-------|--------------|
| `nuevaReserva.blade.php` | Formulario de nueva reserva (público). |
| `adminreservas.blade.php` | Panel del administrador con listado y acciones sobre reservas. |
| `actualizarReserva.blade.php` | Formulario de edición de reservas. |
| `email*.blade.php` | Plantillas HTML para correos automáticos. |
| `layouts/app.blade.php` | Plantilla base con Bootstrap y sistema de autenticación Laravel. |

---

## ⚙️ Arquitectura técnica

- **Framework:** Laravel 5.x  
- **Lenguaje:** PHP 7.x+  
- **Base de datos:** MySQL o equivalente compatible con Eloquent ORM  
- **Frontend:** Blade + Bootstrap 4  
- **Emails:** Laravel Mailables con vistas Blade y soporte para HTML  
- **Autenticación:** Sistema nativo de Laravel (Login, Register, Password Reset)

---

## 🔐 Seguridad y autenticación

- Autenticación basada en sesiones de Laravel (`Auth` facade).  
- Protección CSRF activada por defecto (`VerifyCsrfToken` middleware).  
- Los formularios utilizan `@csrf` para validar las solicitudes.  
- Solo usuarios autenticados pueden acceder al panel de gestión o modificar reservas.

---

## 🧠 Lógica de negocio simplificada

| Acción | Responsable | Resultado |
|--------|--------------|-----------|
| Crear reserva | Cliente | Se guarda en BD, correo “pendiente”. |
| Confirmar reserva | Admin | Cambia estado, correo “confirmado”. |
| Cancelar reserva | Admin | Se elimina, correo “cancelado”. |
| Editar reserva | Admin | Modifica datos existentes. |

---

## 🚀 Instalación y uso (modo desarrollo)

```bash
# 1. Clonar el repositorio
git clone <url>

# 2. Instalar dependencias PHP
composer install

# 3. Configurar variables de entorno
cp .env.example .env
php artisan key:generate

# 4. Configurar base de datos y correo en .env
# MAIL_MAILER=smtp
# MAIL_HOST=smtp.example.com
# ...

# 5. Ejecutar migraciones
php artisan migrate

# 6. Iniciar servidor local
php artisan serve
```

Accede a [http://localhost:8000](http://localhost:8000)

---

## 🧪 Tecnologías adicionales

| Área | Tecnología |
|------|-------------|
| CSS | Sass + Bootstrap |
| JS | Vue.js (configurado pero no usado en producción) |
| Emails | Blade templates con soporte HTML |
| ORM | Eloquent |

