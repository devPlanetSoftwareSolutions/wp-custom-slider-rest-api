# WordPress Custom Slider Plugin

A WordPress plugin that creates custom sliders with REST API support, allowing you to manage slider content through the WordPress admin interface and retrieve slider data via API.

## Features

- Custom post type for managing sliders
- Image upload/selection for each slider
- REST API endpoint for retrieving slider data
- Clean admin interface
- Responsive design ready

## Installation

1. Upload the `custom-slider-plugin.php` file to your WordPress plugins directory (`/wp-content/plugins/`)
2. Activate the plugin through the 'Plugins' menu in WordPress
3. A new "Sliders" menu item will appear in the admin sidebar

## Usage

### Adding Sliders

1. Navigate to **Sliders → Add New** in WordPress admin
2. Enter a title and description/content for your slider
3. Click "Select Image" to choose an image from the media library or upload a new one
4. Publish the slider

### Accessing Sliders via REST API

The sliders are available at the following endpoint: GET /wp-json/custom/v1/sliders


#### Example Response

```json
[
    {
        "title": "Slider 1",
        "content": "Slider 1 description",
        "image": "https://yourdomain.com/wp-content/uploads/2023/05/slider1.jpg"
    },
    {
        "title": "Slider 2",
        "content": "Slider 2 description",
        "image": "https://yourdomain.com/wp-content/uploads/2023/05/slider2.jpg"
    }
]
```

## Customization Options

### Post Type Arguments

- Modify the register_slider_post_type() method to change:
- Capabilities
- Labels
- Other post type properties

### REST API Response
Adjust the get_sliders_data() method to include additional fields in the API response.

### Image Sizes
Change the code to return different image sizes by using wp_get_attachment_image_src() instead of wp_get_attachment_url().
