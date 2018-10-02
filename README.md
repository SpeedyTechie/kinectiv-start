# kinectiv-start
A WordPress starter theme for Kinectiv websites

### Starting a New Theme
*Before beginning, ensure you are viewing the documentation from the latest release: https://github.com/SpeedyTechie/kinectiv-start/releases*

#### Initial Configuration
* Update `Theme Name: Kinectiv Start` at the top of `style.css` to include the name for your theme.
* Find all occurences of `kinectiv-start-` (handle prefixes) and `kinectiv_start_` (function name prefixes) in `functions.php` and update them to match the name of your theme.
* Update `register_nav_menus()` in `functions.php` to register any nav menus you will need for your theme. If no nav menus are needed, remove the function call.

#### Gulp
* Run `npm install` to install all needed packages.
* Run `gulp` to verify everything is installed and working.

#### CSS and JS
* Run `gulp` any time you are making updates to your theme's CSS and JS.
* Add all custom CSS to `style.css`. This file will be minified, autoprefixed, and saved as `style.min.css`. Note that thanks to autoprefixer, you do not need to manually add vendor prefixes to your CSS.
* Add any 3rd party CSS files to `css/src`. These files will be concatenated, minified, and saved as `css/vendor.min.css`. Be sure to uncomment the line in `functions.php` that enqueues this CSS file.
* Add all custom JS functions to `js/src/functions.js`. Add any 3rd party JS files to `js/src`. These files will be concatenated and minified (along with `functions.js`) and saved as `js/script.min.js`.

#### Customization
* If your theme does not require an ACF Theme Options page, remove the `Add ACF options page` section from `functions.php`.
* If your theme requires comments, remove the `Disable comments` section from `functions.php`.
* If your theme requires search, remove the `Disable search` section from `functions.php`.
* If your theme will use Posts, remove the `Hide Posts from admin` section from `functions.php`.
* If your theme requires post archive pages, remove the `Disable archive pages for Posts` section from `functions.php` (or customize to fit your needs).
* If your theme will use the Category or Tag taxonomies for Posts, remove the the `Disable default taxonomies for Posts` section (or the relevant lines) from `functions.php`.
* Replace `images/favicon.ico` with the site icon for your theme.
* Update the default `color`, `font-family`, and any other properties as needed at the beginning of the `Typography` section in `style.css`.
* Update the `background-color` of `body` in the `Elements` section in `style.css` if necessary. This color is used to fill extra space on screens wider than 2100px.
* Update the `background-color` of `.site` in the `Content` section in `style.css` if necessary. This is the main background color of the site.
* Update the default link styles in the `Links` section of `style.css`.
* Remove the `Gravity Forms` section from `style.css` if the Gravity Forms plugin will not be used.
* If your theme will not use video embeds, remove the `initFitvids()` function and call from `js/src/functions.js` and delete `js/src/jquery.fitvids.js`.

#### Other Notes
* Delete any of the following files that your theme will not use: `archive.php`, `page.php`, `search.php`, `single.php`.
* Remember to udpate CSS and JS version numbers in `functions.php` any time you push changes to a live site (to ensure cached files are updated).
* Add any template part files to the `template-parts` folder.
* Add images and graphics to the `images` folder.
