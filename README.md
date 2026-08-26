# Business Management System

A Laravel-based business management system for managing sales, inventory, customers, suppliers, expenses, payments, users, and business reporting from one application.

## Screenshots

Screenshots are not included in the repository yet. Add exported images to `docs/screenshots/` and reference them here, for example:

```markdown
![Dashboard](docs/screenshots/dashboard.png)
![Sales](docs/screenshots/sales.png)
![Inventory](docs/screenshots/inventory.png)
```

Recommended screenshots:

- Dashboard overview
- Sales and payment screens
- Inventory and stock movement screens
- Financial reports
- User and role management

## Features

- Dashboard with business metrics and activity overview
- User accounts with role-based access control
- Roles for super administrators, administrators, managers, salespeople, storekeepers, and accountants
- Product, category, supplier, and customer management
- Sales creation, editing, cancellation, payment recording, and CSV export
- Inventory stock-in, stock-out, adjustments, valuation, movement history, and low-stock reports
- Expense recording with categories, receipt uploads, approval, and rejection workflows
- Financial dashboards, profit and loss, expense reports, balance sheets, and period comparison
- Sales, inventory, and customer analytics
- Notifications and notification preferences
- Internal conversations, participants, messages, and read receipts
- Audit logging and report exports
- Form validation, authentication, password recovery, and soft-deleted users

## Technologies Used

- PHP 8.2+
- Laravel 12
- Laravel Fortify
- SQLite by default, with MySQL support available through Laravel configuration
- Bootstrap 5.3
- Font Awesome 6.4
- Vite 7
- Tailwind CSS 4
- PHPUnit 11
- Composer and npm

## Installation

### Requirements

- PHP 8.2 or later
- Composer
- Node.js and npm
- SQLite, or MySQL if you prefer a server database
- XAMPP is suitable for local Windows development

### Setup

From the project directory:

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
New-Item -ItemType File -Path database\database.sqlite -Force
php artisan migrate --seed
npm install
npm run build
php artisan storage:link
php artisan serve
```

Open `http://127.0.0.1:8000` in your browser.

For development with Vite hot reload, use:

```powershell
npm run dev
```

Keep the Laravel server running in a separate terminal when using Vite development mode.

## Demo Credentials

The database seeders create these accounts with the password `password123`:

| Role | Email |
| --- | --- |
| Super Admin | `superadmin@business.local` |
| Administrator | `admin@business.local` |
| Manager | `manager@business.local` |
| Salesperson | `salesperson@business.local` |
| Storekeeper | `storekeeper@business.local` |
| Accountant | `accountant@business.local` |

Change all seeded passwords before using the application outside a local demo environment.

## Database Setup

### SQLite

SQLite is the default configuration in `.env.example`:

```env
DB_CONNECTION=sqlite
```

Create the database file and run migrations with seed data:

```powershell
New-Item -ItemType File -Path database\database.sqlite -Force
php artisan migrate --seed
```

### MySQL

Create a database in MySQL, then update `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=business_management
DB_USERNAME=root
DB_PASSWORD=
```

Run:

```powershell
php artisan migrate --seed
```

To rebuild a development database from scratch:

```powershell
php artisan migrate:fresh --seed
```

## Project Architecture

This is a conventional Laravel application:

```text
app/
  Console/        Artisan commands
  Exceptions/     Application exception types and handling
  Http/           Controllers, middleware, and form requests
  Mail/           Application email classes
  Models/         Eloquent models and relationships
  Policies/       Authorization policies
  Rules/          Reusable validation rules
  Services/       Domain and application services
bootstrap/        Framework bootstrapping
config/           Application configuration
database/
  factories/      Model factories
  migrations/     Database schema
  seeders/        Roles, permissions, users, and sample data
public/            Web entry point and public assets
resources/views/   Blade templates
routes/web.php    Web routes and middleware boundaries
tests/             Feature and unit tests
```

Authentication is handled by middleware, authorization is organized around roles and permissions, business data is persisted through Eloquent models, and the UI is rendered with Blade templates.

## Live Demo

No live demo has been deployed yet. The source repository is available at:

<https://github.com/Benkiche/business-management-system>

## License

This project is licensed under the MIT License. See [composer.json](composer.json) for the project license declaration.

## Contact

- GitHub: [Benkiche](https://github.com/Benkiche)
- Email: [benedictokichele@gmail.com](mailto:benedictokichele@gmail.com)
