# Marble Slabs E-Commerce Website

A PHP-based e-commerce website for showcasing and selling marble slabs, featuring a comprehensive product catalog with detailed specifications, high-quality photo galleries, and e-commerce functionality.

## Features

- **Product Catalog**: Detailed product listings with specifications, images, and sorting/filtering
- **Category Management**: Hierarchical category structure for organized browsing
- **Image Gallery**: High-quality image display with zoom functionality
- **Shopping Cart**: Add products, update quantities, and manage your selections
- **Admin Panel**: Comprehensive admin interface for product, order, and user management
- **Responsive Design**: Mobile-friendly layout for all screen sizes
- **SEO Optimized**: Clean URLs and customizable meta tags

## System Requirements

- PHP 8.1 or higher
- MySQL 8.0 or higher
- Apache 2.4+ or Nginx
- mod_rewrite enabled (for Apache)
- GD or Imagick PHP extension (for image processing)

## Installation

### Option 1: Automatic Installation (Recommended)

1. Upload all files to your web server
2. Navigate to `http://your-domain.com/install.php` in your browser
3. Follow the on-screen instructions to configure your database and admin account
4. Once installation is complete, you'll be redirected to the homepage

### Option 2: Manual Installation

1. Upload all files to your web server
2. Create a MySQL database
3. Import the database structure from `/db/create_database.sql`
4. Edit the database connection settings in `/includes/db_connection.php`:
   ```php
   $db_host = 'localhost'; // Your database host
   $db_user = 'username';  // Your database username
   $db_pass = 'password';  // Your database password
   $db_name = 'marble_slabs'; // Your database name
   ```
5. Edit site configuration in `/includes/config.php`:
   ```php
   $config = [
       'site_title' => 'Your Site Title',
       'base_url' => 'http://your-domain.com',
       // other settings...
   ];
   ```
6. Create the `/includes/installed.php` file:
   ```php
   <?php // Installation completed on <?php echo date('Y-m-d H:i:s'); ?> ?>
   ```

## Directory Structure

```
/
├── admin/             # Admin panel files
├── assets/            # CSS, JavaScript, and front-end assets
│   ├── css/           # Stylesheets
│   ├── js/            # JavaScript files
│   └── img/           # Site images (logo, icons)
├── db/                # Database scripts
├── images/            # Product and content images
├── includes/          # Core PHP files
├── section/           # Reusable page sections (header, footer)
├── uploads/           # User-uploaded content
├── .htaccess          # Apache configuration
├── 404.php            # 404 error page
├── index.php          # Homepage
├── install.php        # Installation script
├── products.php       # Product listing page
├── product.php        # Product detail page
└── README.md          # This file
```

## Admin Access

After installation, you can access the admin panel at `http://your-domain.com/admin/`

Default admin credentials:
- **Email**: admin@example.com
- **Password**: Admin123!

*Important: Change these credentials immediately after first login.*

## User Roles

- **Admin**: Full access to all functionality and settings
- **Staff**: Limited access to product management and order processing
- **Customer**: Access to their own orders, wishlist, and account settings

## Customization

### Theme Customization

Edit the CSS files in `/assets/css/` to customize the appearance:
- `style.css` - Main stylesheet

### Site Settings

Site settings can be modified through the admin panel:
1. Login to the admin panel
2. Navigate to "Settings"
3. Update the desired fields
4. Click "Save Changes"

## Troubleshooting

### Database Connection Issues

If you encounter database connection errors:
1. Verify database credentials in `/includes/db_connection.php`
2. Check that your MySQL server is running
3. Ensure the database exists and the user has proper permissions

### Missing Images

If product images aren't displaying:
1. Check that the `/uploads/` directory is writable
2. Verify that the image paths in the database are correct
3. Check for PHP memory limit issues if handling large images

### 404 Errors on Pages

If you get 404 errors on URLs:
1. Make sure mod_rewrite is enabled on your server
2. Check that the .htaccess file was uploaded correctly
3. Verify your server configuration allows .htaccess overrides

## Development Guidelines

When extending or modifying the codebase:

1. Follow the existing coding style and patterns
2. Add proper error handling for new functionality
3. Test all changes on different browsers and screen sizes
4. Validate user input and implement proper security measures

## License

This software is provided for your specific use and is not for redistribution without permission.

## Support

For questions or support, contact:
- Technical support: support@example.com
- General inquiries: info@example.com