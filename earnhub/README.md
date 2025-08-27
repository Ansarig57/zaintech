# Earnhub - Premium Earning Platform

A modern, responsive web application for earning real money through spins, ads, and referrals with a premium dark-neon UI/UX.

## 🚀 Features

### User Panel
- **Authentication System**: Login/Register with email/phone + password
- **Email Verification**: Secure account verification system
- **Password Reset**: Email-based password recovery
- **Profile Management**: Edit profile with avatar upload
- **Wallet System**: Points to PKR conversion (1000 points = ₹1 PKR)
- **Spin & Earn**: Animated wheel with configurable rewards and daily limits
- **Watch Ads**: Timer-based ad watching with Facebook/TikTok placeholders
- **Daily Login Bonus**: +50 points daily with 7-day streak bonus (+5000 points)
- **Referral System**: Signup bonuses + lifetime commission
- **Leaderboard**: Weekly & monthly rankings with glowing profiles
- **History**: Complete point transaction logs
- **Notifications**: Admin broadcast system
- **Support Tickets**: User support system
- **Analytics**: Earnings graphs with Chart.js
- **Weekly Jackpot**: Auto-entry for top users

### Admin Panel
- **Secure Login**: Hardcoded admin credentials (admin / 123ZAIN)
- **User Management**: View, edit, delete users and manage points
- **Withdrawal Management**: Approve/reject withdrawals (min ₹200 PKR)
- **Settings Management**: Configure spin/watch limits, rewards, conversion rates
- **Notification System**: Broadcast messages to users
- **Jackpot Management**: Configure weekly jackpot settings
- **Site Settings**: Points/PKR conversion, referral rates, theme colors
- **Template Management**: Multiple dashboard templates
- **Analytics Dashboard**: User growth and activity charts

### Design & UX
- **Premium Dark-Neon UI**: Modern, engaging interface
- **10 Color Themes**: Dark, Neon, Golden, Blue, Red, Green, Purple, Gradient, Pastel, Classic
- **Responsive Design**: Perfect on desktop, tablet, and mobile
- **Smooth Animations**: Engaging transitions and effects
- **Glowing Elements**: Premium neon effects
- **Sound Effects**: Click sounds and celebration audio
- **Live Earnings Ticker**: Real-time activity feed
- **Accessibility Optimized**: Screen reader friendly

## 🛠 Technology Stack

- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Charts**: Chart.js
- **Icons**: Font Awesome 6
- **Fonts**: Google Fonts (Poppins)

## 📦 Installation

### Requirements
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- SSL certificate (recommended)

### Quick Setup (InfinityFree/cPanel)

1. **Download & Extract**
   ```bash
   # Download the earnhub.zip file
   # Extract to your hosting account's public_html directory
   ```

2. **Database Setup**
   ```bash
   # Create a new MySQL database in cPanel
   # Import the database/earnhub.sql file
   # Note down database credentials
   ```

3. **Configuration**
   ```php
   # Copy config/env.php.example to config/env.php
   # Update database credentials:
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'your_database_name');
   define('DB_USER', 'your_database_user');
   define('DB_PASS', 'your_database_password');
   
   # Update site URL:
   define('SITE_URL', 'https://yourdomain.com');
   define('SITE_EMAIL', 'noreply@yourdomain.com');
   ```

4. **File Permissions**
   ```bash
   # Set proper permissions for upload directories
   chmod 755 uploads/
   chmod 755 uploads/avatars/
   chmod 644 config/env.php
   ```

5. **Email Setup** (Optional)
   ```php
   # Configure SMTP settings in config/env.php for email verification
   define('SMTP_HOST', 'smtp.gmail.com');
   define('SMTP_PORT', 587);
   define('SMTP_USERNAME', 'your-email@gmail.com');
   define('SMTP_PASSWORD', 'your-app-password');
   ```

### Local Development Setup

1. **Clone Repository**
   ```bash
   git clone <repository-url>
   cd earnhub
   ```

2. **Start Local Server**
   ```bash
   # Using PHP built-in server
   php -S localhost:8000
   
   # Or use XAMPP/WAMP/MAMP
   ```

3. **Database Setup**
   ```bash
   # Import database/earnhub.sql to your local MySQL
   # Update config/env.php with local credentials
   ```

4. **Access Application**
   ```
   Frontend: http://localhost:8000
   Admin Panel: http://localhost:8000/admin
   ```

## 🔐 Default Credentials

### Admin Panel
- **Username**: admin
- **Password**: 123ZAIN

### Test User (Created after registration)
- Register normally through the frontend
- Email verification required for full access

## 🎨 Theme System

The application includes 10 beautiful themes:

1. **Dark Theme** (Default) - Premium dark with neon accents
2. **Neon Theme** - Bright neon colors
3. **Golden Theme** - Elegant gold and black
4. **Blue Theme** - Professional blue tones
5. **Red Theme** - Bold red design
6. **Green Theme** - Nature-inspired green
7. **Purple Theme** - Royal purple theme
8. **Gradient Theme** - Beautiful gradients
9. **Pastel Theme** - Soft pastel colors
10. **Classic Theme** - Traditional light theme

### Theme Switching
```php
// Per user theme switching
$user['theme'] = 'neon-theme';

// Global theme switching (admin)
setAdminSetting('default_theme', 'golden-theme');
```

## 🔧 Configuration

### Reward Settings
```php
// Spin Wheel
define('SPIN_MIN_REWARD', 10);      // Minimum points per spin
define('SPIN_MAX_REWARD', 500);     // Maximum points per spin
define('SPIN_DAILY_LIMIT', 5);      // Daily spin limit

// Watch Ads
define('WATCH_AD_REWARD', 5);       // Points per ad
define('WATCH_AD_DURATION', 30);    // Ad duration in seconds
define('WATCH_DAILY_LIMIT', 20);    // Daily ad limit

// Daily Bonus
define('DAILY_LOGIN_BONUS', 50);    // Daily login bonus
define('STREAK_BONUS_DAYS', 7);     // Days for streak bonus
define('STREAK_BONUS_POINTS', 5000); // Streak bonus points

// Referral System
define('REFERRAL_SIGNUP_BONUS', 100);    // Bonus for referrer
define('REFERRAL_COMMISSION_RATE', 10);  // Commission percentage

// Withdrawal
define('POINTS_TO_PKR_RATE', 1000);     // Points per PKR
define('MIN_WITHDRAWAL_PKR', 200);       // Minimum withdrawal
```

### Security Features
- Password hashing with bcrypt
- Session-based authentication
- CSRF protection
- SQL injection prevention
- XSS protection
- Device/IP tracking
- Rate limiting placeholders

## 📊 Database Schema

### Main Tables
- `users` - User accounts and profiles
- `spins` - Spin wheel history
- `watch_logs` - Ad watching logs
- `withdrawals` - Withdrawal requests
- `notifications` - System notifications
- `tickets` - Support tickets
- `jackpot_entries` - Weekly jackpot entries
- `point_history` - All point transactions
- `admin_settings` - Configuration settings
- `device_tracking` - Anti-cheat logging

## 🚀 Deployment

### Production Checklist
- [ ] Update all credentials in `config/env.php`
- [ ] Set `ENVIRONMENT` to 'production'
- [ ] Configure SSL certificate
- [ ] Set up email SMTP
- [ ] Configure file permissions
- [ ] Test all functionality
- [ ] Set up backups
- [ ] Configure cron jobs (optional)

### Performance Optimization
- Gzip compression enabled
- CSS/JS minification
- Database query optimization
- Caching headers
- Image optimization
- Lazy loading

## 🔍 API Endpoints

### Authentication
- `POST /api/auth/login.php` - User login
- `POST /api/auth/register.php` - User registration
- `POST /api/auth/logout.php` - User logout
- `POST /api/auth/forgot-password.php` - Password reset

### User Actions
- `POST /api/user/spin.php` - Spin wheel
- `POST /api/user/watch-claim.php` - Claim ad reward
- `POST /api/user/daily-bonus.php` - Claim daily bonus
- `GET /api/user/activity.php` - Get user activity

### Admin
- `POST /api/admin/login.php` - Admin login
- `GET /api/admin/users.php` - Get users list
- `GET /api/admin/withdrawals.php` - Get withdrawals
- `POST /api/admin/settings.php` - Update settings

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials in `config/env.php`
   - Ensure MySQL service is running
   - Verify database exists and user has permissions

2. **Email Not Sending**
   - Configure SMTP settings
   - Check spam folder
   - Verify email credentials

3. **File Upload Issues**
   - Check directory permissions (755 for directories, 644 for files)
   - Verify upload limits in PHP settings
   - Ensure uploads directory exists

4. **Theme Not Loading**
   - Clear browser cache
   - Check CSS file paths
   - Verify theme files exist

### Debug Mode
```php
// Enable debug mode in config/env.php
define('ENVIRONMENT', 'development');
define('DEBUG_MODE', true);
```

## 📞 Support

For support and questions:
- Create an issue in the repository
- Check the troubleshooting section
- Review the configuration guide

## 📄 License

This project is licensed under the MIT License. See LICENSE file for details.

## 🙏 Credits

- Font Awesome for icons
- Chart.js for analytics
- Google Fonts for typography
- Various CSS animations and effects

---

**Made with ❤️ for premium earning experiences**

## 🔄 Updates & Maintenance

### Regular Maintenance
- Update PHP dependencies
- Monitor database performance
- Review security logs
- Backup database regularly
- Update admin settings as needed

### Feature Requests
- Submit feature requests through issues
- Check roadmap for planned features
- Contribute to development

---

**Version**: 1.0.0  
**Last Updated**: December 2024  
**Compatibility**: PHP 7.4+, MySQL 5.7+