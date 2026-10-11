<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Spanish language pack for Chatwoot sync
 *
 * @package    local_chatwootsync
 * @category   string
 * @copyright  2026 Studio LXD <hello@studiolxd.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Chatwoot sincronización';

// Formulario de registro.
$string['signup_optin_label'] = 'Quiero recibir comunicaciones de nuevos cursos y de nuevos contenidos en mis cursos por WhatsApp (puedo darme de baja cuando quiera).';

// Configuración.
$string['chatwoot_baseurl'] = 'URL base de Chatwoot';
$string['chatwoot_baseurl_desc'] = 'Ejemplo: https://chat.studiolxd.com (sin barra final).';
$string['chatwoot_account_id'] = 'ID de cuenta de Chatwoot';
$string['chatwoot_account_id_desc'] = 'ID numérico de la cuenta de Chatwoot (p. ej., 1).';
$string['chatwoot_token'] = 'Token API de Chatwoot';
$string['chatwoot_token_desc'] = 'Token de acceso a la API para autenticación.';
$string['chatwoot_inbox_id'] = 'ID de inbox de Chatwoot';
$string['chatwoot_inbox_id_desc'] = 'ID del inbox para asociar nuevos contactos (p. ej., un canal de WhatsApp).';
$string['label_optin'] = 'Etiqueta de opt-in';
$string['label_optin_desc'] = 'Etiqueta a añadir cuando el usuario acepta WhatsApp.';
$string['label_optout'] = 'Etiqueta de opt-out';
$string['label_optout_desc'] = 'Etiqueta a quitar cuando el usuario acepta (y a añadir al darse de baja).';
$string['attr_opt_in'] = 'Atributo de opt-in';
$string['attr_opt_in_desc'] = 'Atributo personalizado a establecer como verdadero al aceptar.';
$string['attr_opt_in_at'] = 'Atributo de fecha de opt-in';
$string['attr_opt_in_at_desc'] = 'Atributo personalizado para almacenar la fecha/hora de aceptación.';
$string['attr_opt_out'] = 'Atributo de opt-out';
$string['attr_opt_out_desc'] = 'Atributo personalizado a establecer como falso al aceptar.';
$string['attr_opt_out_at'] = 'Atributo de fecha de opt-out';
$string['attr_opt_out_at_desc'] = 'Atributo personalizado para almacenar la fecha/hora de baja (se borra al aceptar).';
$string['choose'] = 'Seleccione';
$string['save_credentials_first'] = 'Primero guarde la URL de Chatwoot, el ID de cuenta y el Token, y luego recargue esta página para configurar etiquetas y atributos.';
$string['api_connection_error'] = 'Error de conexión con la API de Chatwoot';
$string['api_connection_error_desc'] = 'No se pudo conectar con la API de Chatwoot: {$a}. Verifique sus credenciales e intente de nuevo.';
$string['chatwoot_api_error'] = 'Se produjo un error al comunicarse con la API de Chatwoot.';

// Privacidad.
$string['privacy:metadata'] = 'El plugin Chatwoot sync envía datos de usuario a una instancia externa de Chatwoot CRM para comunicaciones por WhatsApp.';
$string['privacy:metadata:phone'] = 'El número de teléfono del usuario se envía para crear o identificar un contacto.';
$string['privacy:metadata:email'] = 'La dirección de correo electrónico del usuario se envía a Chatwoot.';
$string['privacy:metadata:name'] = 'El nombre completo del usuario se envía a Chatwoot.';
$string['privacy:metadata:ip'] = 'La dirección IP del usuario puede registrarse para el control de consentimiento.';
