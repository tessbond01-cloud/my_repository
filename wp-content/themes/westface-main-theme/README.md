# Westface Professional WordPress Theme

A professional WordPress theme with enhanced WooCommerce templates featuring color swatches, two-column layouts, modern design, and comprehensive e-commerce functionality.

## Features

### 🎨 **Professional Design**
- Modern teal color scheme (#00d4aa)
- Responsive design for all devices
- Professional typography with Inter font family
- Gradient backgrounds and smooth animations
- Card-based layouts with depth and shadows

### 🛍️ **Enhanced WooCommerce**
- **Color Swatch System**: Automatic color detection from product names (25+ colors supported)
- **Two-Column Product Layout**: Pricing and photo on left, specifications on right
- **Professional Product Cards**: Clean, minimal design with dual pricing structure
- **Category Grid**: Enhanced category display with card-like appearance
- **Custom Meta Attributes**: Professional product information tables
- **Sample Request System**: Integrated quote request functionality

### 📱 **Responsive Features**
- Mobile-optimized touch interfaces
- Responsive grid layouts
- Adaptive navigation with hamburger menu
- Cross-device compatibility

### 🔧 **Advanced Functionality**
- **Quote Request Plugin**: Modal forms with database storage and email notifications
- **Laminate Quiz Plugin**: Interactive product selection quiz with contact forms
- **Professional Tabs**: Organized product information in right column
- **Upsells/Cross-sells**: Grid layout (4 items on PC, 3 on tablets, 2 on phones)
- **Categories Display**: Shows categories before products on shop page
- **Professional Pagination**: Styled pagination with Finnish language support

### 🎯 **Technical Excellence**
- Clean, maintainable code structure
- WordPress coding standards compliance
- Performance optimized
- SEO-friendly structure
- Accessibility features
- Cross-browser compatibility

## Installation

1. **Upload Theme**:
   - Upload the theme folder to `/wp-content/themes/`
   - Or install via WordPress admin: Appearance > Themes > Add New > Upload Theme

2. **Activate Theme**:
   - Go to Appearance > Themes
   - Click "Activate" on Westface Professional

3. **Install Plugins** (Optional):
   - Upload the included plugins to `/wp-content/plugins/`
   - Activate them in the WordPress admin

## Theme Structure

```
westface-professional/
├── woocommerce/                    # WooCommerce template overrides
│   ├── single-product.php          # Enhanced single product template
│   ├── content-single-product.php  # Two-column layout with color swatches
│   ├── archive-product.php         # Professional shop pages
│   ├── content-product.php         # Product loop with color swatches
│   └── taxonomy-product-cat.php    # Enhanced category pages
├── css/
│   └── professional-woocommerce-styles.css  # Comprehensive WooCommerce styling
├── js/
│   └── professional-woocommerce-scripts.js  # Interactive functionality
├── plugins/                        # Optional WordPress plugins
│   ├── quote-request-plugin.php    # Quote request system
│   └── laminate-quiz-plugin.php    # Product selection quiz
├── style.css                       # Main theme stylesheet
├── functions.php                   # Theme functions and hooks
├── header.php                      # Theme header template
├── footer.php                      # Theme footer template
├── index.php                       # Main template file
└── README.md                       # This documentation
```

## ⚙️ Configuration

### **Customizer Options**
Go to **Appearance > Customize > Professional WooCommerce** to configure:

- **Primary Color**: Change the main brand color (default: #00d4aa)
- **Products Per Page**: Set how many products to display per page (default: 12)

### **Product Color Override**
When editing products, you'll find a new "Professional Color Swatch" meta box where you can:
- Set custom colors for specific products
- Override automatic color detection
- Use color picker for precise color selection

### **Menu Integration**
The theme automatically enhances your existing menus with:
- Professional styling and hover effects
- Mobile-responsive hamburger menu
- Smooth animations and transitions

## 🎨 Customization

### **Changing Colors**
To modify the color scheme, edit the CSS custom properties in `style.css`:

```css
:root {
    --primary-color: #00d4aa;        /* Main brand color */
    --primary-dark: #00b894;         /* Darker shade */
    --text-dark: #333333;            /* Dark text */
    /* Add more custom properties as needed */
}
```

### **Adding New Product Colors**
To add new colors to the automatic detection system, edit the color arrays in:
- `woocommerce/content-single-product.php` (line ~45)
- `woocommerce/content-product.php` (line ~30)

```php
$colors = array(
    'your-color-name' => '#hexcode',
    'another-color' => '#hexcode',
    // Add more colors here
);
```

### **Customizing Layout**
Modify the grid layout by editing the CSS in `css/professional-woocommerce-styles.css`:

```css
/* Change product grid columns */
.woocommerce ul.products {
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
}

/* Adjust single product layout */
.professional-product-layout {
    grid-template-columns: 1fr 400px; /* Left column, Right column */
}
```

## 🔧 Advanced Features

### **Color Detection System**
The theme includes intelligent color detection that:
- Analyzes product names for color keywords
- Supports 25+ common colors out of the box
- Falls back to brand color for unrecognized products
- Can be overridden per product in admin

### **Quick View Modal**
- AJAX-powered product preview
- Responsive modal design
- Keyboard navigation (ESC to close)
- Touch-friendly mobile interface

### **Wishlist System**
- Session-based wishlist storage
- AJAX add/remove functionality
- Visual feedback and notifications
- Persistent across page loads

### **Sample Request System**
- Professional contact form modal
- Customizable form fields
- Email integration ready
- Mobile-optimized interface

## 📱 Mobile Optimization

The theme is fully responsive with specific optimizations for:

- **Mobile Phones** (320px - 768px): Single column layout, touch-friendly buttons
- **Tablets** (768px - 1024px): Optimized two-column layout
- **Desktop** (1024px+): Full professional layout with all features

## 🛠️ Troubleshooting

### **Common Issues**

#### **Templates Not Loading**
1. Ensure WooCommerce is active and updated
2. Clear all caches (WordPress, theme, plugins)
3. Check file permissions (644 for files, 755 for directories)

#### **Colors Not Showing**
1. Verify product names contain recognizable color keywords
2. Check the color detection functions in template files
3. Use the custom color override in product admin

#### **JavaScript Not Working**
1. Check browser console for JavaScript errors
2. Ensure jQuery is loaded
3. Verify Font Awesome is loading correctly

#### **Mobile Layout Issues**
1. Test on actual devices, not just browser resize
2. Clear mobile browser cache
3. Check viewport meta tag in theme header

### **Debug Mode**
Enable WordPress debug mode by adding to `wp-config.php`:
```php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

## 🔄 Updates and Maintenance

### **Theme Updates**
- The child theme preserves customizations during parent theme updates
- Always backup before major updates
- Test functionality after updates

### **WooCommerce Compatibility**
- Templates are based on latest WooCommerce standards
- Monitor WooCommerce changelog for breaking changes
- Update template version numbers if needed

### **Performance Optimization**
- CSS and JS are conditionally loaded only on WooCommerce pages
- Images use lazy loading where supported
- Minimal external dependencies

## 📋 Requirements

### **WordPress Requirements**
- WordPress 5.0 or higher
- WooCommerce 4.0 or higher
- PHP 7.4 or higher
- Westface parent theme

### **Browser Support**
- Chrome 70+
- Firefox 65+
- Safari 12+
- Edge 79+
- Mobile browsers (iOS Safari, Chrome Mobile)

### **Server Requirements**
- PHP sessions enabled (for wishlist functionality)
- AJAX support
- Standard WordPress hosting environment

## 🎯 Best Practices

### **Content Management**
- Use descriptive product names with color keywords
- Optimize product images for web (WebP recommended)
- Write compelling product descriptions
- Use proper product categories and tags

### **SEO Optimization**
- The theme includes structured data for products
- Proper heading hierarchy is maintained
- Image alt texts are preserved
- Page loading speeds are optimized

### **Performance**
- Use image optimization plugins
- Implement caching solutions
- Monitor Core Web Vitals
- Regular performance audits

## 📞 Support

### **Documentation Resources**
- WooCommerce Documentation: https://woocommerce.com/documentation/
- WordPress Theme Development: https://developer.wordpress.org/themes/
- Child Theme Guide: https://developer.wordpress.org/themes/advanced-topics/child-themes/

### **Community Support**
- WordPress Support Forums
- WooCommerce Community
- Stack Overflow (woocommerce, wordpress tags)

## 📄 License

This child theme inherits the license of the parent Westface theme. Please ensure compliance with all applicable licenses when using in commercial projects.

## 🔄 Changelog

### Version 1.0.0
- Initial release with professional WooCommerce templates
- Two-column single product layout with color swatches
- Enhanced shop and category pages
- Professional styling with teal color scheme
- Quick view and wishlist functionality
- Sample request system
- Mobile-responsive design
- Comprehensive customization options

---

**Professional WooCommerce implementation for Westface theme** 🏆

For technical support or customization requests, please refer to the WordPress and WooCommerce documentation or community forums.

