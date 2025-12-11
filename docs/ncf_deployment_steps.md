# Pasos para ver y usar la administración de NCF y reportes DGII

Sigue esta guía para levantar en un entorno local las pantallas y flujos de NCF, retenciones y reportes 606/607/608.

1. **Actualizar el código y dependencias**
   - `git pull` para traer los últimos cambios.
   - `composer install` (o `composer update` si corresponde) para asegurar dependencias PHP.
   - `npm install` y `npm run dev` / `npm run build` si usas los activos compilados del frontend.

2. **Ejecutar migraciones**
   - `php artisan migrate`
   - Esto crea/actualiza: `ncf_types`, `ncf_sequences`, columnas NCF en facturas y gastos, y las tablas de retenciones.

3. **Sembrar permisos y datos base**
   - `php artisan db:seed --class=UsersTableSeeder`
   - Incluye permisos: `manage/create/edit/delete ncf type`, `manage/create/edit/delete ncf sequence`, `report.dgii` y demás permisos de NCF/retenciones.

4. **Asignar permisos al usuario/rol**
   - Desde el módulo de Roles/Permisos o mediante consola, asigna al usuario de prueba los permisos anteriores.
   - Si manejas roles, agrega estos permisos al rol administrador.

5. **Limpiar cachés de aplicación y permisos**
   - `php artisan permission:cache-reset`
   - `php artisan optimize:clear`
   - Esto refresca rutas, vistas y permisos para que aparezcan los menús de NCF.

6. **Reiniciar sesión en la aplicación**
   - Cierra sesión e ingresa de nuevo para que los permisos asignados surtan efecto en el menú lateral y en las rutas protegidas.

7. **Rutas y menús a verificar**
   - Administración de tipos: `/ncf-types`
   - Administración de secuencias: `/ncf-sequences`
   - Reporte DGII: `/reports/dgii` (descarga 606/607/608 si tienes el permiso `report.dgii`)

8. **Validar flujos con NCF y retenciones**
   - Al crear facturas o gastos, selecciona tipo y secuencia de NCF; el sistema validará rango y vigencia.
   - Al registrar pagos a suplidores, valida que se generen registros de retención cuando aplique.

9. **Opcional: correr pruebas**
   - `php ./vendor/bin/phpunit --filter NcfServiceTest`
   - `php ./vendor/bin/phpunit --filter RetentionServiceTest`

Con estos pasos deberías ver las pantallas de NCF y poder generar los reportes DGII en tu entorno local.
