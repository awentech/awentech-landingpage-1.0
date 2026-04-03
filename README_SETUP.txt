═══════════════════════════════════════════════════════════
  AWEN TECH — INSTRUCCIONES DE CONFIGURACIÓN
═══════════════════════════════════════════════════════════

PASO 1 — reCAPTCHA v2   
─────────────────────
1. Ir a: https://www.google.com/recaptcha/admin/create
2. Tipo: reCAPTCHA v2 · "No soy un robot"
3. Dominio: agregar tu dominio (ej: awentech.com.ar) Y localhost para pruebas
4. Guardar y copiar las dos claves que genera Google

5. En index.html, buscar:
     data-sitekey="TU_SITE_KEY_AQUI"
   Reemplazar con tu Site Key (clave pública)

6. En php/send_mail.php, buscar:
     define('RECAPTCHA_SECRET', 'TU_SECRET_KEY_AQUI');
   Reemplazar con tu Secret Key (clave privada)


PASO 2 — Configurar el email (Gmail con App Password)
───────────────────────────────────────────────────────
Gmail requiere una "Contraseña de aplicación" (NO la contraseña normal).

Para obtenerla:
1. Ir a tu cuenta Google → Seguridad
2. Activar "Verificación en 2 pasos" (si no está activa)
3. Ir a: https://myaccount.google.com/apppasswords
4. Crear nueva contraseña de aplicación → Seleccionar "Correo" y "Otro (nombre personalizado)"
5. Ponerle nombre: "Awen Tech Landing"
6. Copiar la contraseña de 16 caracteres que genera

7. En php/send_mail.php, buscar:
     define('SMTP_PASS', 'TU_APP_PASSWORD_AQUI');
   Reemplazar con la App Password (sin espacios)


PASO 3 (OPCIONAL) — PHPMailer para mejor entrega
─────────────────────────────────────────────────
El formulario funciona con mail() nativo de PHP (ya incluido).
Para mayor confiabilidad en producción, instalar PHPMailer:

1. En la carpeta php/ crear: php/vendor/phpmailer/src/
2. Descargar desde: https://github.com/PHPMailer/PHPMailer
3. Copiar estos 3 archivos a php/vendor/phpmailer/src/:
   - PHPMailer.php
   - SMTP.php
   - Exception.php

El sistema detecta automáticamente si PHPMailer está disponible.


PASO 4 — Fotos del equipo
──────────────────────────
Subir las fotos a:
  assets/img/lara.jpg   → Foto de Lara Pintos
  assets/img/teo.jpg    → Foto de Teo Fernández

Tamaño recomendado: 200x200px o mayor (cuadrada)
Si no hay foto, se muestran las iniciales automáticamente.


PASO 5 — Subir a Hostinger
───────────────────────────
1. Subir todos los archivos a public_html/ vía File Manager o FTP
2. Verificar que PHP >= 7.4 esté activo en Hostinger
3. El formulario usará mail() nativo de PHP (funciona sin config extra en Hostinger)
4. Si querés usar SMTP de Gmail, instalar PHPMailer (Paso 3)

ESTRUCTURA DE ARCHIVOS:
  public_html/
  ├── index.html
  ├── Logo AwenTech - Sin fondo.png
  ├── css/
  │   └── styles.css
  ├── js/
  │   └── main.js
  ├── php/
  │   ├── send_mail.php
  │   └── vendor/          ← solo si instalás PHPMailer
  └── assets/
      └── img/
          ├── lara.jpg
          └── teo.jpg


PASO 6 — Links de redes sociales
──────────────────────────────────
En index.html, al final (sección footer), reemplazar los # con las URLs reales:
  <a href="#" aria-label="LinkedIn">  → URL de LinkedIn
  <a href="#" aria-label="Instagram"> → URL de Instagram


CHECKLIST FINAL ANTES DE PUBLICAR:
  [SI] Site Key de reCAPTCHA en index.html
  [ ] Secret Key de reCAPTCHA en php/send_mail.php
  [ ] App Password de Gmail en php/send_mail.php
  [ ] Fotos de Lara y Teo subidas a assets/img/
  [SI] Links de redes sociales actualizados 
  [SI] Dominio configurado en reCAPTCHA admin
  [ ] Probar formulario en producción

═══════════════════════════════════════════════════════════
