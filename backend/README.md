# Petron Backend Deployment

This folder is the Railway-ready PHP backend/runtime copy. It intentionally excludes the root Node/Tailwind toolchain, `.env` files, `node_modules`, `vendor`, and local macOS/XAMPP artifacts.

## Railway

Deploy this folder as its own repository, or set the Railway service root directory to `/petron-backend`. Railway will use the `Dockerfile` in this folder.

Add a Railway MySQL service, then expose these variables to the backend service:

- `MYSQLHOST`
- `MYSQLPORT`
- `MYSQLUSER`
- `MYSQLPASSWORD`
- `MYSQLDATABASE`

The app also supports `MYSQL_URL` or `DATABASE_URL`, and generic `DB_HOST`, `DB_PORT`, `DB_USERNAME`, `DB_PASSWORD`, and `DB_DATABASE` variables.

## Local Docker

```sh
docker build -t petron-backend .
docker run --rm -p 8080:8080 \
  -e PORT=8080 \
  -e DB_HOST=host.docker.internal \
  -e DB_PORT=3306 \
  -e DB_DATABASE=petron_inventory_db \
  -e DB_USERNAME=root \
  -e DB_PASSWORD= \
  petron-backend
```

Open `http://localhost:8080/public/auth/login.php`.
