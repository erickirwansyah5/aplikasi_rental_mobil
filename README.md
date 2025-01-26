# CarRent - Car Rental Management System

![CarRent Logo](assets/images/logo.png)

## Overview
CarRent is a comprehensive car rental management system designed for car rental businesses of all sizes. Built with PHP and MySQL, it offers a robust platform for managing your entire rental operation, from vehicle inventory to customer bookings and payments.

## Key Features

### Admin Panel
- **Dashboard**: Real-time overview of rentals, revenue, and fleet status
- **Car Management**: 
  - Add, edit, and remove vehicles from your fleet
  - Track vehicle status (available, rented, maintenance)
  - Upload and manage vehicle images
  - Set rental rates and vehicle specifications
- **Customer Management**:
  - View and manage customer profiles
  - Track rental history
  - Manage user accounts
- **Rental Management**:
  - Process new rentals
  - Track active rentals
  - Handle returns and payments
- **Reports**:
  - Generate revenue reports
  - View rental statistics
  - Export data for analysis

### Customer Portal
- User registration and authentication
- Browse available vehicles
- Make rental reservations
- View rental history
- Upload payment proof
- Manage profile

## Technical Specifications

### Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- mod_rewrite enabled
- GD Library (for image processing)

### Security Features
- Password hashing
- SQL injection prevention
- XSS protection
- CSRF protection
- Secure session management

## Installation

1. **Database Setup**:
   ```sql
   CREATE DATABASE db_rental;
   IMPORT db_rental.sql;
   ```

2. **Configuration**:
   - Edit `/config/database.php` with your database credentials
   - Configure your web server to point to the application directory

3. **Default Admin Account**:
   - Username: admin
   - Password: admin123
   (Please change these credentials after first login)

## Support and Updates

### Premium Support
- Priority email support
- Bug fixes and security updates
- Installation assistance
- Custom feature development available

### Documentation
Complete documentation is available at: https://code80vity.com/docs/carrent

## Licensing
This is a commercial product. Purchase includes:
- Full source code
- 1 year of updates
- 1 domain license
- Email support

## Purchase Information
- Regular License: $99
- Extended License: $299
- For custom requirements or bulk licensing, contact: sales@code80vity.com

## Developer Information
Created by: Erick Irwansyah
Website: https://code80vity.com
Email: info@code80vity.com

## Version History
- v1.0.0 (January 2024): Initial Release
  - Complete rental management system
  - Responsive admin and customer interfaces
  - Payment processing
  - Reporting system

---
© 2024 Code80vity.com. All rights reserved.
