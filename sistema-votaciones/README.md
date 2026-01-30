# Sistema de Votos

## Running with Docker

1. Copy the example environment file and set any required secrets (database, cache, etc.):
   ```bash
   cp .env.example .env
   ```

2. Build and start the containers. The `app` service will build the PHP-FPM image, run migrations via the entrypoint, and cache the config/views/routes.
   ```bash
   docker compose up --build
   ```

3. The database container (if using one via Docker) should be reachable through the credentials configured in your `.env` file. If your database is external, ensure the network allows connections from the containers.

4. If this is the first time you run the stack against a brand-new database, seed the essential records after migrations complete:
   ```bash
   docker compose run --rm app php artisan db:seed
   ```
   This publishes the default admin account and voting settings needed for the system to function.

5. Visit the exposed port for the Nginx service (`${OUTPUT_PORT}` defaulting to `8091`) in your browser to access the application.

6. For frontend development, the `node` service already runs `npm run dev` on port `5173`; you can point Vite to that port for hot-reloading asset development.

> **Note:** The Dockerfile already caches npm dependencies and builds the frontend assets during the PHP image build, and the entrypoint runs `php artisan migrate` every time to guarantee schema consistency.
