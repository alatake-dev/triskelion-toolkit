# Triskelion Toolkit v1.0.0

A modular, high-performance WordPress plugin suite designed for the Triskelion premium ecosystem.

## 🏗 Architecture Overview
The toolkit operates as a service container. The core infrastructure handles:
- **Modular Loading**: Each feature is an independent module.
- **Unified Admin UI**: A vertical tab-based interface with CSS toggles.
- **Security Gatekeeper**: Centralized access control for module settings.

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
   Add your module to the array in src/ServiceLayer/Core/Toolkit.php:
    ```php
    'my_new_feature' => [
        'name'         => __( 'My Feature', 'triskelion-toolkit' ),
        'description'  => __( 'A brief description of what this does.', 'triskelion-toolkit' ),
        'class'        => \Triskelion\Toolkit\Modules\MyNewFeature\MyNewFeatureLoader::class,
        'has_settings' => true,
        'icon'         => 'dashicons-star-filled'
    ],
    ```
## 📜 Development Rules
1. Zero Bloat: Only enqueue assets if the block/feature is present on the page.

2. Responsive-First: All components must be fluid and work on mobile viewports.

3. I18n: Use English for all code strings and UI labels (text-domain: triskelion-toolkit).

4. CSS over JS: Prefer CSS solutions (like Toggles) to keep the admin fast.