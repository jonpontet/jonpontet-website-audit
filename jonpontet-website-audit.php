<?php
/**
 * Plugin Name: Jon Pontet's Website Audit
 * Plugin URI:  https://jonpontet.com
 * Description: Jon Pontet's Website Audit
 * Version:     1.0.3
 * Author:      Jon Pontet
 * Author URI:  https://jonpontet.com
 * Text Domain: jpwa
 */

define('JPWA_PLUGIN_ROOT', __DIR__);
define('JPWA_PLUGIN_FILE', __FILE__);

require __DIR__ . '/vendor/autoload.php';

require __DIR__ . '/src/WebsiteAuditPlugin.php';

require __DIR__ . '/src/Core/AuditContext.php';
require __DIR__ . '/src/Core/Audit.php';
require __DIR__ . '/src/Core/Category.php';
require __DIR__ . '/src/Core/Auditor.php';
require __DIR__ . '/src/Core/Shortcode.php';
require __DIR__ . '/src/Core/Settings.php';
require __DIR__ . '/src/Core/Result.php';
require __DIR__ . '/src/Core/RateLimiter.php';

require __DIR__ . '/src/Shortcode/Form.php';
require __DIR__ . '/src/Shortcode/Result.php';
require __DIR__ . '/src/Shortcode/ResultDomain.php';
require __DIR__ . '/src/Shortcode/Email.php';
require __DIR__ . '/src/Shortcode/Recent.php';

require __DIR__ . '/src/Audit/Accessibility.php';
require __DIR__ . '/src/Audit/DisabledPingbacksAndTrackbacks.php';
require __DIR__ . '/src/Audit/DisabledRESTUserScan.php';
require __DIR__ . '/src/Audit/ExternalLinks.php';
require __DIR__ . '/src/Audit/HasSSL.php';
require __DIR__ . '/src/Audit/InternalLinks.php';
require __DIR__ . '/src/Audit/MetaDescription.php';
require __DIR__ . '/src/Audit/MetaFacebook.php';
require __DIR__ . '/src/Audit/MetaTwitter.php';
require __DIR__ . '/src/Audit/MobileFriendliness.php';
require __DIR__ . '/src/Audit/PresenceContactForm.php';
require __DIR__ . '/src/Audit/PresenceGoogleAnalytics.php';
require __DIR__ . '/src/Audit/RobotsTxt.php';
require __DIR__ . '/src/Audit/SitemapXml.php';
require __DIR__ . '/src/Audit/Speed.php';
require __DIR__ . '/src/Audit/TagH1.php';
require __DIR__ . '/src/Audit/TagTitle.php';
require __DIR__ . '/src/Audit/TagTitleLength.php';
require __DIR__ . '/src/Audit/UnauthorisedLoadScripts.php';
require __DIR__ . '/src/Audit/UnauthorisedLoadStyles.php';
require __DIR__ . '/src/Audit/WpLogin404.php';

require __DIR__ . '/src/Category/Accessibility.php';
require __DIR__ . '/src/Category/Conversion.php';
require __DIR__ . '/src/Category/MobileFriendly.php';
require __DIR__ . '/src/Category/SEO.php';
require __DIR__ . '/src/Category/SocialMedia.php';
require __DIR__ . '/src/Category/Speed.php';
require __DIR__ . '/src/Category/WordPressSecurity.php';

require __DIR__ . '/lib/tgm-plugin-activation/class-tgm-plugin-activation.php';

new \JonPontet\WebsiteAudit\WebsiteAuditPlugin();

require __DIR__ . '/lib/wp-package-updater/class-wp-package-updater.php';

new WP_Package_Updater(
    'https://up.jonpontet.com',
    wp_normalize_path( __FILE__ ),
    wp_normalize_path( plugin_dir_path( __FILE__ ) )
);