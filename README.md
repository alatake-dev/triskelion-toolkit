# Triskelion Toolkit v1.2.0

A modular utility suite for WordPress, engineered with professional backend architecture and modern Gutenberg components.

## Featured Modules
- **Code Showcase**: A premium Gutenberg block designed to display code snippets with a macOS terminal aesthetic. Supports multiple tabs, syntax highlighting, and custom themes.

## Development Requirements
To modify or extend the Gutenberg (React) components, the following environment is required:
- **Node.js** (v18 or higher recommended)
- **NPM** or **PNPM**
- **Composer** (for PHP dependency management and PSR-4 autoloading)
- **Docker** (for local deployment and development environment)

## Local Setup
1. Clone the repository into your WordPress plugins directory.
2. Install PHP dependencies:
   $ composer install
3. Install JS dependencies and compile blocks:
   $ npm install
4. For active development (with hot-reloading):
   $ npm start
5. For production-ready assets:
   $ npm run build

## Internationalization (i18n)
The plugin supports Spanish (es_MX and base es). Block translations are managed via JSON files located in the /languages directory. Due to WordPress translation loading priorities, handle-specific naming is maintained for consistent editor localization.


## 🏗 Architecture Overview

The toolkit operates as a service container with a strictly decoupled architecture:

- **Core Modules**: Essential infrastructure (Settings, Diagnostic) that is always available and cannot be deactivated.
- **Feature Modules**: Independent, toggleable features that load only when activated.
- **Lazy Loading**: Classes are only instantiated when their specific tab is active or their functionality is required.

---

## 🚀 Environment Setup (Docker)

This project is developed using a dedicated Docker environment.

**docker-compose.yml sample**

```yaml
services:
   db:
      image: mariadb:10.6
      container_name: triskelion-db
      restart: always
      volumes:
         - triskelion_db_data:/var/lib/mysql
      environment:
         MYSQL_ROOT_PASSWORD: password
         MYSQL_DATABASE: wordpress
         MYSQL_USER: alatake
         MYSQL_PASSWORD: password

   wordpress:
      depends_on:
         - db
      image: wordpress:latest
      container_name: triskelion-wp
      ports:
         - "8080:80"
      environment:
         WORDPRESS_DB_HOST: db
         WORDPRESS_DB_USER: admin
         WORDPRESS_DB_PASSWORD: password
         WORDPRESS_DB_NAME: wordpress
         WORDPRESS_CONFIG_EXTRA: |
            define( 'FS_METHOD', 'direct' );
            define( 'WP_DEBUG', true );
            define( 'WP_DEBUG_LOG', true );
            define( 'WP_DEBUG_DISPLAY', false );
      volumes:
         - /path/to/plugin/code/triskelion-toolkit:/var/www/html/wp-content/plugins/triskelion-toolkit
         - triskelion_wp_uploads:/var/www/html/wp-content/uploads

   cli:
      image: wordpress:cli
      container_name: triskelion-cli
      depends_on:
         - db
         - wordpress
      volumes:
         - /path/to/plugin/code/triskelion-toolkit:/var/www/html/wp-content/plugins/triskelion-toolkit
         - triskelion_wp_uploads:/var/www/html/wp-content/uploads
      environment:
         WORDPRESS_DB_HOST: db
         WORDPRESS_DB_USER: db_user
         WORDPRESS_DB_PASSWORD: db_password
         WORDPRESS_DB_NAME: wordpress

volumes:
   triskelion_db_data:
   triskelion_wp_uploads:

```

*Adjust the paths to match your environment.*

1. **Start the environment**:
   ```bash
   docker-compose up -d
   ```
2. Access the WordPress Container:
    ```bash
    docker exec -it triskelion-wp bash
    ```
3. Plugin Path:

   /var/www/html/wp-content/plugins/triskelion-toolkit

## 📝 Logging & Troubleshooting

The toolkit includes a proprietary, independent logging system.

- **Storage**: Logs are stored in `/wp-content/uploads/triskelion-logs/atk_debug_[hash].log`.
- **Activation**: Can be enabled via the "Logs & Diagnostic" tab or by defining a constant in `wp-config.php`:
  ```php
  define( 'TSK_LOG_ENABLED', true );
  ```
- **Access Control:** The log directory is protected via .htaccess and index.php to prevent direct web access.

## 📜 Development Rules

1. **Zero Bloat**: Only enqueue assets if the feature is active and present.
2. **Encapsulation**: Each module loader is responsible for its own rendering.
3. **The "Opener-Closer" Rule**: Any method opening an HTML tag (div, section, main) MUST be responsible for closing it.
4. **Log Everything**: Use `Logger::xxxx` for critical failures or complex logic tracing. Avoid `var_dump` in
   production-ready code.