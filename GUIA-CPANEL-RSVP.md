# Configuración de RSVP en cPanel (sin terminal)

Esta guía sirve para que funcione el formulario desde **cPanel + phpMyAdmin**.

## 1) Subir archivos
Sube todo el proyecto a `public_html` (o la carpeta de tu dominio).

## 2) Crear base de datos y usuario
En cPanel:
1. Entra a **MySQL® Databases**.
2. Crea una base de datos (ejemplo: `usuario_rsvp`).
3. Crea un usuario MySQL (ejemplo: `usuario_rsvp_user`).
4. Asigna ese usuario a la base con **ALL PRIVILEGES**.

## 3) Configurar conexión
En **File Manager**, abre:

`includes/rsvp_config.php`

Y reemplaza:
- `RSVP_DB_NAME`
- `RSVP_DB_USER`
- `RSVP_DB_PASS`

Opcional:
- `RSVP_ADMIN_KEY`: pon una clave (ej. `mifamilia2026`) para proteger el panel.

## 4) Probar formulario
Abre tu invitación y envía una prueba.

El sistema crea la tabla automáticamente al primer envío.

## 5) Ver confirmaciones
Abre:

`https://tudominio.com/includes/rsvp_admin.php`

Si configuraste clave:

`https://tudominio.com/includes/rsvp_admin.php?key=TU_CLAVE`

## 6) Si no guarda datos
- Verifica que el hosting tenga extensión **mysqli** activa (normalmente sí en cPanel).
- Revisa usuario/contraseña de MySQL en `rsvp_config.php`.
- Confirma que el usuario esté agregado a la base con permisos.
