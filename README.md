# Triskelion Toolkit v1.0.0

A modular, high-performance WordPress plugin suite designed for the Triskelion premium ecosystem.

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
         - /path/to/youre/code/triskelion-toolkit:/var/www/html/wp-content/plugins/triskelion-toolkit
         - triskelion_wp_uploads:/var/www/html/wp-content/uploads

   # El servicio salvador: WP-CLI
   cli:
      image: wordpress:cli
      container_name: triskelion-cli
      depends_on:
         - db
         - wordpress
      volumes:
         # Montamos el plugin también aquí para que el CLI pueda escanearlo
         - /Users/alatake/Documents/code/php/plugins/triskelion-toolkit:/var/www/html/wp-content/plugins/triskelion-toolkit
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

## 🛠 How to Add a New Module

To maintain the Triskelion Standard, follow these steps:
1. Create the Module Folder
    Navigate to src/Modules/ and create your feature folder:
    
    src/Modules/MyNewFeature/MyNewFeatureLoader.php

2. Extend the Abstract Class
   Your loader must extend AbstractModuleLoader and implement the load() method:
    ```php
    namespace Triskelion\Toolkit\Modules\MyNewFeature;
    
    use Triskelion\Toolkit\Core\AbstractModuleLoader;
    
    class MyNewFeatureLoader extends AbstractModuleLoader {
        public function load(): void {
            // Hooks, Block registrations, etc.
        }
    }
    ```
3. Register in the Toolkit
   Add your module to the array in `src/Core/Toolkit.php`. Use the following schema:
    ```php
    'my_new_feature' => [
        'name'         => __( 'My Feature', 'triskelion-toolkit' ),
        'description'  => __( 'A brief description.', 'triskelion-toolkit' ),
        'class'        => \Triskelion\Toolkit\Modules\MyNewFeature\MyNewFeatureLoader::class,
        'is_core'      => false,     // true for system utilities, false for toggleable features
        'priority'     => 100,       // 0-99: System, 100-899: Features, 900+: Support
        'has_settings' => true,
        'icon'         => 'dashicons-star-filled'
    ],
    ```
   
## 📜 Development Rules
1. **Zero Bloat**: Only enqueue assets if the feature is active and present.
2. **Encapsulation**: Each module loader is responsible for its own rendering. The `Admin` class only orchestrates.
3. **The "Opener-Closer" Rule**: Any method opening an HTML tag (div, section, main) MUST be responsible for closing it.
4. **Log Everything**: Use `Toolkit::log()` for critical failures or complex logic tracing. Avoid `var_dump` in production-ready code.