# SendMail Configuration Guide

## Overview
The `sendmail.php` file is a production-ready email handler for the contact form with comprehensive features.

## Key Features

### ✅ Security
- **Input Validation**: All inputs sanitized and validated
- **Spam Detection**: Basic pattern matching for spam keywords
- **Rate Limiting**: Prevents abuse (1 request per 60 seconds per IP)
- **XSS Protection**: HTML entities properly escaped
- **Email Validation**: Strict email format validation

### ✅ Error Handling
- **Graceful Fallbacks**: Tries PHPMailer first, then falls back to PHP mail()
- **Detailed Logging**: All submissions logged with timestamps and IPs
- **HTTP Status Codes**: Proper codes for different error scenarios

### ✅ Logging
- **Success Logs**: `/logs/email.log` - All successful submissions
- **Error Logs**: `/logs/error.log` - All errors and warnings
- **Rate Limit Data**: `/logs/.rate_limit` - IP tracking for spam prevention

### ✅ Features
- Support for both PHPMailer and PHP mail() function
- Client IP detection (works with Cloudflare and proxies)
- JSON responses for AJAX handling
- Timestamp on all emails
- Professional email formatting
- Configurable settings

## Configuration

Edit the configuration section in `sendmail.php`:

```php
define('SITE_NAME', 'Grampians Cafe & Bar');
define('RECIPIENT_EMAIL', 'tanvimalaviya2004@gmail.com');
define('MAX_NAME_LENGTH', 100);
define('MAX_SUBJECT_LENGTH', 200);
define('MAX_MESSAGE_LENGTH', 5000);
define('RATE_LIMIT', 60); // seconds
```

## Field Limits

| Field | Max Length | Purpose |
|-------|-----------|---------|
| Name | 100 chars | Visitor name |
| Email | 255 chars | Valid email address |
| Subject | 200 chars | Email subject |
| Message | 5000 chars | Email body content |

## Rate Limiting

- **Default**: 1 email per 60 seconds per IP address
- **Change**: Modify `RATE_LIMIT` constant
- **Storage**: Uses `/logs/.rate_limit` file

## API Response

### Success Response (200 OK)
```json
{
  "success": true,
  "message": "Thank you! Your message has been sent successfully. We will get back to you shortly.",
  "timestamp": "2026-01-14 10:30:45"
}
```

### Error Response (400/429/500)
```json
{
  "success": false,
  "message": "Error description here",
  "timestamp": "2026-01-14 10:30:45"
}
```

## HTTP Status Codes

| Code | Meaning |
|------|---------|
| 200 | Email sent successfully |
| 400 | Validation error (empty/invalid fields) |
| 405 | Invalid request method (not POST) |
| 429 | Rate limit exceeded |
| 500 | Server error (email send failure) |

## Logging

### Log File Locations
- Success logs: `logs/email.log`
- Error logs: `logs/error.log`
- Rate limit data: `logs/.rate_limit`

### Log Entry Format
```
[2026-01-14 10:30:45] [success] [IP: 192.168.1.1] Email sent via mail() | {"to":"recipient@example.com","from":"sender@example.com"}
```

## Spam Detection

The script includes basic spam keyword detection. Modify the pattern:

```php
if (preg_match('/viagra|cialis|casino|lottery|prize/i', $message)) {
    // Spam detected
}
```

## Email Sending Methods

### 1. PHPMailer (if installed)
- Requires: `composer install`
- File: `vendor/autoload.php`
- Uses: Gmail SMTP or configured SMTP server
- Config: `.env` file

### 2. PHP mail() Function (fallback)
- No dependencies required
- Uses: Server's default mail configuration
- Works on: Almost all hosting providers
- Reliable on most shared hosting

## Testing

### Local Testing
```bash
curl -X POST http://localhost/sendmail.php \
  -d "name=John&email=john@example.com&subject=Test&message=Hello"
```

### Browser Console (JS)
```javascript
fetch('sendmail.php', {
  method: 'POST',
  headers: {'Content-Type': 'application/x-www-form-urlencoded'},
  body: 'name=John&email=john@example.com&subject=Test&message=Hello'
})
.then(r => r.json())
.then(d => console.log(d))
```

## Troubleshooting

### Emails Not Sending

1. **Check logs**: Review `/logs/email.log` and `/logs/error.log`
2. **Verify recipient**: Ensure `RECIPIENT_EMAIL` is correct
3. **Server mail()**: Ask hosting provider if `mail()` is enabled
4. **Firewall**: Check if outbound SMTP is blocked
5. **Permissions**: Ensure `/logs` directory is writable

### Rate Limit Issues

If legitimate users are getting rate-limited:
1. Increase `RATE_LIMIT` constant
2. Check `/logs/.rate_limit` for stuck IPs
3. Delete `.rate_limit` file to reset tracking

### PHP Errors

Check `/logs/error.log` for detailed error messages from PHP.

## Security Checklist

- ✅ All inputs validated and sanitized
- ✅ XSS protection with htmlspecialchars()
- ✅ Email validation with filter_var()
- ✅ Rate limiting prevents brute force
- ✅ Spam detection for keywords
- ✅ CSRF tokens (can be added if needed)
- ✅ No sensitive data exposed in errors
- ✅ Proper HTTP status codes
- ✅ Input length limits enforced

## Contact Form HTML

The contact form should submit to this file:

```html
<form action="sendmail.php" method="POST" id="contactForm">
  <input type="text" name="name" placeholder="Your Name" required>
  <input type="email" name="email" placeholder="Your Email" required>
  <input type="text" name="subject" placeholder="Subject" required>
  <textarea name="message" placeholder="Message" required></textarea>
  <button type="submit">Send Message</button>
</form>
```

JavaScript handler (in main.js):

```javascript
$('#contactForm').on('submit', function(e) {
    e.preventDefault();
    
    $.ajax({
        type: 'POST',
        url: 'sendmail.php',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response) {
            if(response.success) {
                alert(response.message);
                $('#contactForm')[0].reset();
            } else {
                alert('Error: ' + response.message);
            }
        }
    });
});
```

## Version History

- **v2.0**: Production-ready with logging, rate limiting, and hybrid mail support
- **v1.0**: Initial release with basic validation

## Support

For issues or improvements, check:
- `/logs/error.log` for detailed error information
- `/logs/email.log` for submission tracking
- Browser console for AJAX errors
