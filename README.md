⚽ Academia Futbol

Plataforma integral para la gestión de academias de fútbol. Sistema que facilita la administración de jugadores, entrenamientos, torneos y estadísticas en tiempo real.

[![PHP Version](https://img.shields.io/badge/PHP-8.0%2B-777BB4?style=flat-square&logo=php)](https://www.php.net)
[![Laravel](https://img.shields.io/badge/Laravel-11.0-FF2D20?style=flat-square&logo=laravel)](https://laravel.com)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)
[![MySQL](https://img.shields.io/badge/MySQL-5.7%2B-00758F?style=flat-square&logo=mysql)](https://www.mysql.com)

## 📋 Características

- **Gestión de Jugadores**: Registro completo, perfiles, historial de desempeño
- **Control de Entrenamientos**: Programación, asistencia, seguimiento de actividades
- **Administración de Torneos**: Creación, gestión de equipos, clasificaciones y resultados
- **Estadísticas en Tiempo Real**: Métricas de desempeño, gráficos y reportes
- **Sistema de Roles**: Superadmin, Entrenador, Padres, Jugadores
- **Notificaciones**: Avisos automáticos de eventos importantes
- **Reportes Detallados**: Exportación en múltiples formatos

## 🚀 Requisitos Previos

Antes de instalar, asegúrate de tener:

- **PHP**: 8.0 o superior
- **MySQL**: 5.7 o superior
- **Composer**: 2.0 o superior
- **Node.js**: 18.0 o superior (para compilación de assets)
- **NPM**: 9.0 o superior

## 📦 Instalación

Sigue estos pasos para instalar Academia Futbol:

1. **Clonar el repositorio**
   ```bash
   git clone https://github.com/ApoBC/academia-futbol.git
   cd academia-futbol
   ```

2. **Instalar dependencias PHP**
   ```bash
   composer install
   ```

3. **Instalar dependencias JavaScript**
   ```bash
   npm install
   ```

4. **Configurar archivo .env**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configurar base de datos**
   Edita `.env` y configura:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=academia_futbol
   DB_USERNAME=root
   DB_PASSWORD=
   ```

6. **Ejecutar migraciones**
   ```bash
   php artisan migrate:fresh --seed
   ```

7. **Compilar assets**
   ```bash
   npm run dev
   ```

8. **Iniciar servidor**
   ```bash
   php artisan serve
   ```

Accede a `http://localhost:8000`

## 🔐 Usuarios por Defecto

| Rol | Email | Contraseña |
|-----|-------|-----------|
| Superadmin | admin@academia.test | password |
| Entrenador | entrenador@academia.test | password |
| Padre | padre@academia.test | password |

⚠️ **Importante**: Cambia estas contraseñas en producción.

## 📖 Uso

### Para Superadmin
- Acceso total al sistema
- Gestión de usuarios y roles
- Configuración de la plataforma
- Reportes globales

### Para Entrenador
- Crear y editar entrenamientos
- Registrar asistencia de jugadores
- Crear torneos
- Generar reportes del equipo

### Para Padres
- Ver progreso del jugador
- Acceder a horarios de entrenamientos
- Recibir notificaciones de eventos

## 🛠️ Tecnologías Utilizadas

- **Backend**: Laravel 11 (PHP 8.0+)
- **Frontend**: Blade, Tailwind CSS, Alpine.js
- **Base de Datos**: MySQL 5.7+
- **Assets**: NPM, Vite
- **Testing**: PHPUnit, Pest
- **API**: RESTful con autenticación Sanctum

## 📁 Estructura del Proyecto

```
academia-futbol/
├── app/              # Lógica de la aplicación
├── bootstrap/        # Inicialización
├── config/           # Archivos de configuración
├── database/         # Migraciones y seeders
├── public/           # Archivos públicos (compilados)
├── resources/        # Vistas y assets
├── routes/           # Definición de rutas
├── storage/          # Logs y caché
├── tests/            # Tests automatizados
└── ...
```

## 🧪 Testing

Ejecuta los tests con:
```bash
php artisan test
```

## 📝 Commits

Este proyecto sigue el formato de **Conventional Commits**:

```bash
git commit -m "feat(jugadores): agregar filtro por posición"
git commit -m "fix(entrenamientos): corregir cálculo de asistencia"
git commit -m "docs(readme): actualizar requisitos"
git commit -m "style: formatear código"
git commit -m "refactor(equipos): mejorar lógica de clasificación"
git commit -m "test: agregar tests de estadísticas"
git commit -m "chore: actualizar dependencias"
git commit -m "perf(reportes): optimizar queries"
```

## 🚀 Deployment

Para desplegar en producción:

1. Configura variables de entorno en `.env`
2. Ejecuta `composer install --no-dev`
3. Ejecuta `php artisan migrate --force`
4. Configura el servidor web (Nginx/Apache)
5. Habilita HTTPS
6. Configura backups automáticos

Ver [documentación de Laravel](https://laravel.com/docs/deployment) para más detalles.

## 🐛 Reportar Bugs

¿Encontraste un bug? Abre un [issue en GitHub](https://github.com/ApoBC/academia-futbol/issues) con:
- Descripción clara del problema
- Pasos para reproducir
- Comportamiento esperado vs actual
- Versión de PHP y Laravel

## 🤝 Contribuir

Las contribuciones son bienvenidas. Para colaborar:

1. Fork el proyecto
2. Crea una rama (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios siguiendo Conventional Commits
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

Consulta [CONTRIBUTING.md](CONTRIBUTING.md) para más detalles.

## 📄 Licencia

Este proyecto está licenciado bajo la Licencia MIT. Ver [LICENSE](LICENSE) para más detalles.

**Autor**: ApoBC (Bill Castillo Picón)  
**Creado**: 2024  
**Última actualización**: 2026

---

¿Preguntas? Contacta al autor o abre un issue en GitHub.
