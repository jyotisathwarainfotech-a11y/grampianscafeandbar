# Common Components Setup

This project now uses reusable components to maintain consistency across all pages.

## File Structure

```
includes/
  ├── head.php      - HTML head section with meta tags, stylesheets
  ├── navbar.php    - Navigation bar with logo and menu
  └── footer.php    - Footer with contact info, links, and scripts
```

## How to Use

### Basic Template
Every page should follow this structure:

```php
<?php
$pageTitle = "Page Title - Grampians Cafe & Bar";
require 'includes/head.php';
require 'includes/navbar.php';
?>

<!-- Your page content here -->

<?php require 'includes/footer.php'; ?>
```

### Updating Navigation

The navbar automatically highlights the active page based on the current PHP file name. Links should use `.php` extension:
- `index.html`
- `about.php`
- `menu.php`
- `contact.php`
- `reservation.html`

### Customization

To customize:
1. **Head Section**: Edit `includes/head.php` for stylesheets, fonts, meta tags
2. **Navbar**: Edit `includes/navbar.php` for logo, menu items, colors
3. **Footer**: Edit `includes/footer.php` for contact info, links, social media

## Benefits

✓ **DRY Principle** - Don't Repeat Yourself
✓ **Easy Maintenance** - Update navbar/footer in one place, changes apply everywhere
✓ **Consistency** - All pages have identical header/footer
✓ **Scalability** - Easy to add new pages
✓ **Active Link Highlighting** - Automatic active nav item styling
