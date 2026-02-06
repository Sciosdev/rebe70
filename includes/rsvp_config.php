<?php

declare(strict_types=1);

/**
 * Configuración para cPanel (editar estos valores desde File Manager).
 *
 * 1) Crea la base de datos y usuario en cPanel > MySQL Databases.
 * 2) Asigna el usuario a la base con todos los privilegios.
 * 3) Copia aquí los datos.
 */
const RSVP_DB_HOST = 'localhost';
const RSVP_DB_NAME = 'CAMBIA_AQUI_NOMBRE_BD';
const RSVP_DB_USER = 'CAMBIA_AQUI_USUARIO_BD';
const RSVP_DB_PASS = 'CAMBIA_AQUI_PASSWORD_BD';

/**
 * Clave opcional para proteger el panel de admin.
 * Si la dejas vacía, el panel será público.
 */
const RSVP_ADMIN_KEY = '';
