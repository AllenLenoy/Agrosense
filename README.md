# AgroSense: Smart Agriculture & IoT Farming Platform

## Overview
AgroSense is a modern, responsive web application built with Laravel designed to empower farmers with precision agriculture tools. By integrating real-time IoT sensor data, automated irrigation controls, and intelligent disease detection, AgroSense helps optimize crop yields, conserve water, and simplify farm management.

## Key Features
- **Multi-Role Portals:** 
  - **Farmer Dashboard:** Manage farms, fields, and crops. Monitor sensor data, control irrigation, and view disease reports.
  - **Admin Dashboard:** Oversee all users, manage IoT devices, and monitor platform-wide activities.
- **IoT Sensor Monitoring:** Real-time tracking of soil moisture, temperature, humidity, water levels, and pH levels using simulated or real ESP32 devices.
- **Smart Irrigation Management:** Schedule automatic irrigation based on sensor data thresholds, time schedules, or manual triggers.
- **Alerts & Notifications:** Receive timely alerts for critical conditions such as low soil moisture, high temperatures, sensor offline status, and weather warnings.
- **Disease Detection:** Automated disease analysis with confidence scores, treatment suggestions, and prevention methods.
- **Weather Integration:** View real-time weather conditions to make informed farming decisions.
- **Activity Logging:** Comprehensive logs for all significant actions taken on the farm (e.g., irrigation started, alerts triggered).

## Tech Stack
- **Backend:** Laravel (PHP)
- **Frontend:** Blade Templates, JavaScript, CSS (Custom styling)
- **Database:** SQLite (Configurable to MySQL/PostgreSQL)
- **Real-time Communications:** Laravel Reverb (WebSockets)
- **Asset Management:** Vite

## Installation & Setup

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js & NPM
- SQLite (or another supported database)

### Steps to Run Locally
1. **Clone the repository:**
   ```bash
   git clone https://github.com/AllenLenoy/Agrosense.git
   cd Agrosense
   ```

2. **Install PHP dependencies:**
   ```bash
   composer install
   ```

3. **Install frontend dependencies:**
   ```bash
   npm install
   ```

4. **Environment Setup:**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Database Configuration & Seeding:**
   The project uses SQLite by default. Run the migrations and seed the database with rich dummy data:
   ```bash
   php artisan migrate:fresh --seed
   ```

6. **Start the background services:**
   You will need to run the following commands, ideally in separate terminal windows:

   - **Start the Laravel Development Server:**
     ```bash
     php artisan serve
     ```
   - **Start the Vite Development Server (for frontend assets):**
     ```bash
     npm run dev
     ```
   - **Start the Queue Worker (for background tasks like alerts):**
     ```bash
     php artisan queue:work
     ```

## Demo Credentials
The database seeder automatically creates the following accounts for testing:

- **Admin Account:**
  - **Email:** `admin@agrosense.in`
  - **Password:** `password`

- **Farmer Account:**
  - **Email:** `farmer@agrosense.in`
  - **Password:** `password`

## Usage
- Access the application at `http://127.0.0.1:8000/login`.
- Log in using the credentials above to explore the respective dashboards.

## License
This project is open-sourced software.
