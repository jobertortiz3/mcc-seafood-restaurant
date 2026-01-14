# MCC Seafood Restaurant Management System

A comprehensive restaurant management system built with PHP, MySQL, and Bootstrap 5. Features include online reservations, menu management, admin dashboard, user authentication, and more.

## 🚀 Features

- **Online Reservations**: Table and cottage booking system
- **User Authentication**: Secure login/logout for customers and admins
- **Admin Dashboard**: Complete management interface
- **Menu Management**: Dynamic food and drinks menu
- **Gallery Management**: Image upload and management
- **Contact Messages**: Customer inquiry handling
- **Responsive Design**: Works on all devices (mobile, tablet, desktop)
- **Real-time Availability**: Check table/cottage availability
- **Message System**: Admin-to-user communication
- **Activity Logging**: Complete admin action tracking

## 📋 Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache/Nginx web server
- XAMPP/WAMP/MAMP (for local development)

## 🛠️ Installation

### 1. Clone the Repository
```bash
git clone https://github.com/yourusername/mcc-seafood-restaurant.git
cd mcc-seafood-restaurant
```

### 2. Database Setup
1. Create a new MySQL database
2. Import the database schema (if provided) or create tables manually
3. Update database credentials in `config.php`

### 3. Configuration
1. Copy `config.sample.php` to `config.php`
2. Update database credentials and other settings in `config.php`

### 4. File Permissions
```bash
chmod 755 images/
chmod 755 images/gallery/
chmod 755 images/menu/
```

### 5. Web Server Setup
- Place the project in your web server's document root
- Ensure `mod_rewrite` is enabled for clean URLs
- Configure virtual host if needed

## 📱 Usage

### For Customers:
1. **Browse Menu**: View food and drinks offerings
2. **Make Reservations**: Book tables or cottages online
3. **User Dashboard**: Manage reservations and view messages
4. **Contact**: Send inquiries to restaurant staff

### For Administrators:
1. **Admin Login**: Access admin dashboard
2. **Manage Reservations**: View, confirm, or cancel bookings
3. **Menu Management**: Add, edit, delete menu items
4. **Gallery Management**: Upload and manage images
5. **Message Handling**: Respond to customer inquiries
6. **Activity Monitoring**: View admin action logs

## 🎨 Responsive Design

The system is fully responsive and optimized for:
- 📱 Mobile phones (320px and up)
- 📟 Tablets (768px and up)
- 💻 Laptops (1024px and up)
- 🖥️ Desktops (1200px and up)

## 🔒 Security Features

- Password hashing with bcrypt
- SQL injection prevention with prepared statements
- XSS protection with input sanitization
- CSRF protection on forms
- Session security with proper configuration
- File upload validation and security

## 📁 Project Structure

```
mcc-seafood-restaurant/
├── admin-dashboard.php      # Admin management interface
├── admin-login.php          # Admin authentication
├── admin-logout.php         # Admin logout
├── config.php               # Database configuration
├── config.sample.php        # Configuration template
├── dashboard.php            # User dashboard
├── index.php                # Homepage
├── login.php                # User authentication
├── logout.php               # User logout
├── reserve.php              # Reservation system
├── contact.php              # Contact page
├── gallery.php              # Image gallery
├── header.php               # Common header/navigation
├── footer.php               # Common footer
├── images/                  # Image assets
│   ├── gallery/            # User uploaded gallery images
│   ├── menu/               # Menu item images
│   └── tables/             # Table/cottage images
├── about*.php              # About pages
├── menu.php                # Menu pages
├── services.php            # Services page
├── faq.php                 # FAQ page
├── terms.php               # Terms and policies
└── README.md               # This file
```

## 🚀 Deployment

### Option 1: GitHub Pages (Static Only)
This project uses PHP and MySQL, so GitHub Pages won't work for full functionality. Use for code hosting only.

### Option 2: Web Hosting Services
Recommended hosting providers for PHP/MySQL applications:
- **Hostinger**
- **SiteGround**
- **Bluehost**
- **DigitalOcean**
- **AWS Lightsail**
- **Heroku** (with ClearDB add-on)

### Deployment Steps:
1. Upload all files to your hosting provider
2. Create MySQL database
3. Update `config.php` with production credentials
4. Set proper file permissions
5. Test all functionality

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 📞 Support

For support, email admin@mccseafood.com or create an issue in this repository.

## 🙏 Acknowledgments

- Bootstrap 5 for responsive design
- Font Awesome for icons
- PHP community for excellent documentation
- Open source contributors

---

**MCC Seafood Restaurant** © 2025. Fresh from the sea, cooked with passion.