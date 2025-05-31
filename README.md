# Bluesky Feed for WordPress®

Showcase your recent Bluesky posts on your WordPress® website in a variety of ways. Display your Bluesky feed using a widget, shortcode, preview the feed in the admin settings, with customizable themes and display styles.

* * *

## Features

- **Widget Integration**: Easily add your Bluesky feed to your website's sidebar or footer.
- **Shortcode Support**: Place the feed anywhere on your site with `[bluesky_feed display="list|grid"]`.
- **Admin Preview**: See how your feed will look directly in the plugin settings page.
- **Customizable Themes**: Choose from `light`, `dim`, or `dark` themes.
- **Grid or List Layouts**: Choose between a list display or a grid layout with 3-4 posts per row.
- **Real-Time Fetching**: Fetch your latest Bluesky posts via the official API.
- **Pin Filtering**: Optionally include or exclude pinned posts.
- **Responsive Design**: Feed styles adapt seamlessly across devices.
- **Translation Ready**: Currently translated into Spanish, French, Italian, Afrikaans, and Hindi
* * *

## Installation

1. **Download the Plugin**  
Clone or download the plugin from the [GitHub repository](https://github.com/robertdevore/bluesky-feed-for-wordpress).

2. **Upload to WordPress**

    - Navigate to **Plugins > Add New** in your WordPress admin.
    - Click **Upload Plugin** and select the downloaded ZIP file.
    - Click **Install Now** and **Activate**.
3. **Configure Settings**

    - Go to **Settings > Bluesky Feed**.
    - Enter your Bluesky username and adjust display options.
* * *

## Usage

### 1. **Widget**

Add the **Bluesky Feed Widget** from the **Appearance > Widgets** section. Customize global settings in the plugin options.

### 2. **Shortcode**

Use the `[bluesky_feed]` shortcode in any post or page.  
Available attributes:

- `display`: Choose between `list` (default) or `grid`.
- `post_count`: Control how many posts shall be displayed. If not provided the number from the app settings will be used.

Example:
 
```
[bluesky_feed display="grid" post_count="10"]
```

If you want to load the dependencies outside of a widget or a content shortcode (eg. `echo do_shortcode("[bluesky_feed display='grid']")`) set the global variable `$loadBlueSkyApp` to true.

Example:


```
function loadBlueSkyApp() {
    if(is_front_page()) {
        global $loadBlueSkyApp;
        $loadBlueSkyApp = true;
    }
}
add_action( 'wp', 'loadBlueSkyApp' );
```

### 3. **Admin Preview**

Preview your feed directly in the settings page under **Settings > Bluesky Feed**.

* * *


## License

This plugin is licensed under the **GPL-2.0+**.  
See the [LICENSE](http://www.gnu.org/licenses/gpl-2.0.txt) file for details.

* * *

## Acknowledgments

- Built with ❤️ by [Robert DeVore](https://robertdevore.com/).
- Icons powered by [Tabler Icons](https://tabler-icons.io/).
