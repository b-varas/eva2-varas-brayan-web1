# Sistema de Gestión de Proyectos — Tech Solutions Group

Evaluación sumativa Unidad 2 — Framework Web (Laravel 11 / PHP 8.3)
*(construida sobre la base de la Unidad 1 — CRUD de proyectos con MVC)*

**Nombre del equipo:** The IT Crowd

**Integrantes:** Eduardo Palma - Luis Muñoz - Brayan Varas

Aplicación web para la gestión de proyectos de Tech Solutions. En esta unidad se incorpora conexión real a base de datos vía ORM (Eloquent), autenticación de usuarios y cifrado de contraseñas, sobre la base MVC ya construida en la Unidad 1.

## Contenido
- [Descripción](#descripción)
- [Tecnologías](#tecnologías)
- [Instalación](#instalación)
- [Configuración de Base de Datos](#configuración-de-base-de-datos)
- [Autenticación y Cifrado](#autenticación-y-cifrado)
- [Rutas disponibles](#rutas-disponibles)
- [Arquitectura (MVC)](#arquitectura-mvc)
- [Patrones de diseño utilizados](#patrones-de-diseño-utilizados)
- [Componente reutilizable: Valor UF del día](#componente-reutilizable-valor-uf-del-día)
- [Estándares de desarrollo web aplicados](#estándares-de-desarrollo-web-aplicados)

## Descripción

La aplicación permite administrar proyectos (listar, ver, crear, actualizar y eliminar) y gestionar usuarios mediante registro e inicio de sesión, cumpliendo los siguientes requerimientos:

- Conexión real a base de datos MySQL mediante el ORM Eloquent de Laravel.
- Registro e inicio de sesión de usuarios, con validación de credenciales.
- Cifrado de contraseñas (bcrypt, vía `Hash::make()`), nunca se almacenan en texto plano.
- CRUD completo de proyectos (id, nombre, fecha de inicio, estado, responsable, monto, usuario creador), ahora persistido en base de datos real.
- Rutas de gestión de proyectos protegidas: solo usuarios autenticados pueden acceder.
- Vistas con estilos básicos y mensajes de confirmación tipo pop-up.
- Componente reutilizable que consume un servicio externo (API de indicadores económicos) para mostrar el valor de la UF del día, con respaldo simulado si el servicio no está disponible.

## Tecnologías

- PHP 8.3
- Laravel 11
- Eloquent ORM
- MySQL 8
- Blade (motor de plantillas)
- JavaScript básico (pop-ups de notificación)
- CSS propio (sin frameworks externos)

## Instalación

```bash
git clone <url-del-repositorio>
cd eva2-varas-brayan-web1
composer install
cp .env.example .env
php artisan key:generate
```

Configura tu base de datos en `.env` (ver sección siguiente), crea la base de datos en MySQL, y luego:

```bash
php artisan migrate
php artisan serve
```

Luego visita `http://127.0.0.1:8000/login` para iniciar sesión, o `http://127.0.0.1:8000/registro` para crear una cuenta nueva.

## Configuración de Base de Datos

El proyecto usa MySQL como motor de base de datos, mediante el ORM Eloquent. En el archivo `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=desarrollo_software_web_1
DB_USERNAME=root
DB_PASSWORD=
```

Antes de ejecutar `php artisan migrate`, la base de datos debe existir en el servidor MySQL:

```sql
CREATE DATABASE desarrollo_software_web_1;
```

Las migraciones (`database/migrations/`) definen la estructura de las tablas:
- `users` — usuarios del sistema (incluida por defecto en Laravel).
- `projects` — proyectos gestionados, con llave foránea `created_by` hacia `users`.

## Autenticación y Cifrado

La autenticación se implementa en `app/Http/Controllers/AuthController.php`, con dos flujos principales:

**Registro (`register()`):**
- Valida los datos de entrada (nombre, correo único, contraseña con confirmación).
- Cifra la contraseña con `Hash::make()` (algoritmo bcrypt) antes de guardarla — nunca se almacena en texto plano.
- Inicia sesión automáticamente tras un registro exitoso.

**Inicio de sesión (`login()`):**
- Valida las credenciales usando `Auth::attempt()`, que compara internamente el hash de la contraseña ingresada contra el hash almacenado, sin exponer ni descifrar la contraseña original en ningún momento.
- Regenera el identificador de sesión tras un login exitoso (prevención de session fixation).

**Cierre de sesión (`logout()`):** invalida la sesión activa y regenera el token CSRF.

Las rutas de gestión de proyectos (`/proyectos/*`) están protegidas con el middleware `auth`: un usuario sin sesión iniciada es redirigido automáticamente a `/login`. Cada proyecto creado registra el `id` del usuario autenticado en el campo `created_by`.

## Rutas disponibles

| Acción | Verbo | Ruta | Nombre | Controlador |
|---|---|---|---|---|
| Mostrar formulario de registro | GET | `/registro` | `register` | `AuthController@showRegister` |
| Procesar registro | POST | `/registro` | `register.store` | `AuthController@register` |
| Mostrar formulario de login | GET | `/login` | `login` | `AuthController@showLogin` |
| Procesar login | POST | `/login` | `login.store` | `AuthController@login` |
| Cerrar sesión | POST | `/logout` | `logout` | `AuthController@logout` |
| Listar proyectos *(requiere sesión)* | GET | `/proyectos` | `projects.index` | `ProjectController@index` |
| Formulario crear *(requiere sesión)* | GET | `/proyectos/crear` | `projects.create` | `ProjectController@create` |
| Guardar proyecto *(requiere sesión)* | POST | `/proyectos` | `projects.store` | `ProjectController@store` |
| Formulario editar *(requiere sesión)* | GET | `/proyectos/{id}/editar` | `projects.edit` | `ProjectController@edit` |
| Actualizar proyecto *(requiere sesión)* | PUT | `/proyectos/{id}` | `projects.update` | `ProjectController@update` |
| Confirmar eliminación *(requiere sesión)* | GET | `/proyectos/{id}/eliminar` | `projects.confirmDelete` | `ProjectController@confirmDelete` |
| Eliminar proyecto *(requiere sesión)* | DELETE | `/proyectos/{id}` | `projects.destroy` | `ProjectController@destroy` |
| Obtener proyecto por id *(requiere sesión)* | GET | `/proyectos/{id}` | `projects.show` | `ProjectController@show` |

Las rutas con parámetro `{id}` están restringidas con `whereNumber()` para aceptar solo valores numéricos, evitando errores por parámetros inválidos.

## Arquitectura (MVC)

- **Modelos** (`app/Models/`):
  - `User.php` — modelo Eloquent estándar de Laravel, con contraseña cifrada automáticamente vía cast (`'password' => 'hashed'`) y reforzada explícitamente en el controlador con `Hash::make()`.
  - `Project.php` — modelo Eloquent que representa la tabla `projects`, con relación `belongsTo` hacia `User` a través de `created_by`. *(En la Unidad 1 este modelo usaba datos estáticos en sesión; en la Unidad 2 se migró a Eloquent con persistencia real en MySQL.)*
- **Controladores** (`app/Http/Controllers/`):
  - `AuthController.php` — gestiona registro, login y logout.
  - `ProjectController.php` — recibe las peticiones HTTP, valida los datos de entrada y coordina la comunicación entre el modelo y las vistas.
- **Vistas** (`resources/views/`):
  - `auth/login.blade.php`, `auth/register.blade.php` — formularios de autenticación.
  - `projects/*.blade.php` — vistas del CRUD de proyectos.
  - Todas heredan una estructura común desde `layouts/app.blade.php`, que incluye el estado de sesión del usuario (nombre y botón de cerrar sesión) cuando corresponde.

## Patrones de diseño utilizados

| Patrón | Dónde se aplica |
|---|---|
| MVC (Model-View-Controller) | Estructura general del proyecto |
| Front Controller / Router | `routes/web.php` distribuye cada petición al controlador correspondiente |
| ORM (Active Record) | `Project` y `User` extienden `Eloquent Model`, mapeando filas de BD a objetos PHP |
| Middleware | `auth` y `guest` filtran el acceso a rutas según el estado de sesión |
| Inyección de dependencias | `UfWidget` recibe `UfService` automáticamente vía el constructor |
| Service Layer | `UfService` aísla la lógica de consumo de la API externa |
| Component Pattern | `<x-uf-widget />`, componente Blade reutilizable en todas las vistas |
| Template Method | `layouts/app.blade.php` con `@yield`, completado por cada vista hija |

## Componente reutilizable: Valor UF del día

`app/Services/UfService.php` consulta la API pública `mindicador.cl`. Si el servicio no responde (sin conexión, timeout, error del servidor), se entrega automáticamente un valor simulado, evitando que la aplicación falle.

`app/View/Components/UfWidget.php` expone este servicio como componente Blade:

```blade
<x-uf-widget />
```

Se incluye una única vez en el layout compartido, por lo que aparece en todas las vistas del módulo sin duplicar código.

## Estándares de desarrollo web aplicados

- Verbos HTTP semánticos (GET, POST, PUT, DELETE) mediante `@method()` en los formularios.
- Protección CSRF (`@csrf`) en todos los formularios, incluido el de cierre de sesión.
- Validación server-side (`$request->validate()`), incluyendo unicidad de correo y confirmación de contraseña.
- Cifrado de contraseñas mediante bcrypt (`Hash::make()`), nunca en texto plano.
- Autenticación mediante `Auth::attempt()`, sin comparación manual de credenciales.
- Protección de rutas sensibles mediante middleware (`auth` / `guest`).
- Rutas nombradas (`route()`) en lugar de URLs escritas manualmente.
- Separación de responsabilidades entre modelo, controlador, vista y servicio.
- Mensajes de retroalimentación al usuario (éxito / error) mediante flash messages y notificaciones tipo pop-up.

---
Desarrollado por Eduardo Palma - Luis Muñoz - Brayan Varas — Evaluación Sumativa Unidad 2, Desarrollo Web con Framework.