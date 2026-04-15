# Push Notification System for Job Portal

This document explains the push notification system implemented for the FromCampus job portal.

## Overview

The push notification system allows users to receive instant notifications when new jobs are posted or existing jobs are updated. This enhances user engagement and ensures job seekers never miss important opportunities.

## Features

- **Real-time notifications** for new job postings
- **Update notifications** when jobs are modified
- **Cross-browser support** (Chrome, Firefox, Edge, Safari)
- **Offline support** with service worker caching
- **Subscription management** (subscribe/unsubscribe)
- **Analytics and logging** for notification performance
- **Mobile responsive** design

## Architecture

### Components

1. **Service Worker** (`sw.js`) - Handles push events and displays notifications
2. **Frontend Manager** (`assets/js/push-notifications.js`) - Manages subscriptions and UI
3. **Backend Service** (`lib/PushNotificationService.php`) - Sends notifications to subscribers
4. **API Endpoints** (`api/push-subscribe.php`, `api/push-unsubscribe.php`) - Handle subscription requests
5. **Database Tables** - Store subscriptions and notification logs

### Database Schema

```sql
-- Push notification subscriptions
CREATE TABLE push_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    endpoint VARCHAR(500) NOT NULL,
    p256dh_key VARCHAR(255) NOT NULL,
    auth_key VARCHAR(255) NOT NULL,
    user_agent VARCHAR(255),
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);

-- Notification logs
CREATE TABLE push_notification_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    subscription_id INT,
    notification_type ENUM('new_job', 'job_update') NOT NULL,
    job_id INT,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    payload JSON,
    status ENUM('sent', 'failed', 'pending') DEFAULT 'pending',
    error_message TEXT,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- VAPID keys for authentication
CREATE TABLE vapid_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    public_key VARCHAR(255) NOT NULL,
    private_key VARCHAR(255) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## Installation and Setup

### 1. Run the Setup Script

Access the setup script in your browser:
```
https://yourdomain.com/setup-push-notifications.php
```

This will:
- Create necessary database tables
- Generate VAPID keys (placeholders)
- Update .htaccess for service worker

### 2. Generate Real VAPID Keys

Replace the placeholder VAPID keys with real ones:

**Option A: Online Tool**
- Visit https://vapidkeys.com/
- Generate keys and copy them

**Option B: Node.js**
```bash
npm install -g web-push
web-push generate-vapid-keys
```

**Option C: PHP Library**
```bash
composer require minishlink/web-push
```

### 3. Update Configuration

Update the following files with your VAPID keys:

1. **Database** (vapid_keys table):
```sql
UPDATE vapid_keys SET 
    public_key = 'YOUR_REAL_PUBLIC_KEY',
    private_key = 'YOUR_REAL_PRIVATE_KEY';
```

2. **JavaScript** (`assets/js/push-notifications.js`):
```javascript
this.vapidPublicKey = 'YOUR_REAL_PUBLIC_KEY_HERE';
```

### 4. HTTPS Requirement

Push notifications require HTTPS in production. Ensure:
- SSL certificate is installed
- All resources are served over HTTPS
- Service worker is accessible at `https://yourdomain.com/sw.js`

## Usage

### For Users

1. **Subscribe**: Click "Get Job Alerts" button in the navigation
2. **Grant Permission**: Allow browser notifications when prompted
3. **Receive Notifications**: Get instant alerts for new jobs and updates
4. **Manage**: Click the button again to unsubscribe

### For Admins

Notifications are sent automatically when:
- **New Job**: Posted with status "published"
- **Job Update**: Modified with status "published"

The system will:
- Send notifications to all active subscribers
- Log delivery status and errors
- Deactivate problematic subscriptions
- Provide feedback on notification delivery

## Configuration Options

### Notification Content

Customize notification content in `lib/PushNotificationService.php`:

```php
// New job notification
$title = "New Job: " . $jobData['job_title'];
$message = "Company: " . $jobData['company_name'] . " | Location: " . $jobData['location'];

// Job update notification
$title = "Job Updated: " . $jobData['job_title'];
$message = "Company: " . $jobData['company_name'] . " | Important updates available";
```

### Service Worker Settings

Modify `sw.js` to customize:
- Notification appearance (icon, badge, colors)
- Click behavior (URL redirection)
- Offline caching strategy
- Background sync behavior

### Frontend Behavior

Update `assets/js/push-notifications.js` to change:
- Subscription flow
- UI messaging
- Error handling
- Permission requests

## API Endpoints

### Subscribe to Push Notifications

**POST** `/api/push-subscribe.php`

```json
{
    "endpoint": "https://fcm.googleapis.com/...",
    "keys": {
        "p256dh": "base64_encoded_key",
        "auth": "base64_encoded_auth"
    },
    "userAgent": "Mozilla/5.0...",
    "ipAddress": "192.168.1.1"
}
```

**Response:**
```json
{
    "success": true,
    "message": "Subscription created successfully",
    "subscription_id": 123
}
```

### Unsubscribe from Push Notifications

**POST** `/api/push-unsubscribe.php`

```json
{
    "endpoint": "https://fcm.googleapis.com/..."
}
```

**Response:**
```json
{
    "success": true,
    "message": "Subscription deactivated successfully"
}
```

## Monitoring and Analytics

### Notification Logs

View notification performance in the database:

```sql
-- Get notification statistics
SELECT 
    notification_type,
    status,
    COUNT(*) as count,
    DATE(sent_at) as date
FROM push_notification_logs 
WHERE sent_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
GROUP BY notification_type, status, DATE(sent_at)
ORDER BY date DESC;
```

### Subscription Management

Monitor active subscriptions:

```sql
-- Get subscription statistics
SELECT 
    COUNT(*) as total_subscribers,
    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_subscribers,
    DATE(created_at) as date
FROM push_subscriptions 
GROUP BY DATE(created_at)
ORDER BY date DESC;
```

## Troubleshooting

### Common Issues

1. **Notifications not working**
   - Check HTTPS is enabled
   - Verify VAPID keys are correct
   - Ensure service worker is accessible
   - Check browser console for errors

2. **Permission denied**
   - Users must manually allow notifications
   - Some browsers require user interaction first
   - Check browser settings

3. **Service worker errors**
   - Verify `sw.js` is at root domain
   - Check file permissions
   - Ensure no syntax errors

4. **API endpoint issues**
   - Check database connection
   - Verify file permissions
   - Review error logs

### Debug Mode

Enable debug logging by adding to your PHP files:

```php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Log push notification errors
error_log("Push notification error: " . $errorMessage);
```

### Browser Testing

Test in different browsers:
- **Chrome**: Full support
- **Firefox**: Full support  
- **Edge**: Full support
- **Safari**: Limited support (macOS only)

## Security Considerations

- **HTTPS Required**: All push notifications require HTTPS
- **VAPID Authentication**: Uses cryptographic keys for security
- **Data Privacy**: Minimal data collected (endpoint, keys, user agent)
- **Rate Limiting**: Consider implementing rate limiting for API endpoints
- **Input Validation**: All inputs are validated and sanitized

## Performance Optimization

- **Service Worker Caching**: Offline support for better UX
- **Lazy Loading**: Notifications load asynchronously
- **Database Indexing**: Proper indexes on subscription tables
- **Cleanup Tasks**: Regular cleanup of old subscriptions and logs

## Future Enhancements

Potential improvements:
- **Category-specific notifications**: Subscribe to specific job categories
- **Location-based notifications**: Filter by user location
- **Email fallback**: Send email if push fails
- **Analytics Dashboard**: Visual notification statistics
- **A/B Testing**: Test different notification content
- **Scheduled notifications**: Send at optimal times

## Support

For issues and questions:
1. Check browser console for JavaScript errors
2. Review PHP error logs
3. Verify database tables exist
4. Test API endpoints manually
5. Ensure all prerequisites are met

## License

This push notification system is part of the FromCampus job portal project.
