# LearnDash Email Confirmation Plugin

**Plugin Name:** LearnDash Email Confirmation  
**Description:** Adds an email confirmation step to the LearnDash registration process, requiring users to confirm their email address before accessing content.  
**Version:** 1.0.1  
**Author:** Luis Pique  
**Author URI:** [https://luispique.com](https://luispique.com)  
**Text Domain:** learndash-email-confirmation  
**License:** GPL-2.0+  
**License URI:** [http://www.gnu.org/licenses/gpl-2.0.txt](http://www.gnu.org/licenses/gpl-2.0.txt)

## Description

This plugin enhances LearnDash by adding an email confirmation step to the registration process. Users must confirm their email address before accessing content.

## Installation

1. Upload the plugin files to the `/wp-content/plugins/learndash-email-confirmation` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Configure your email settings in the 'LearnDash Email Settings' under the 'Settings' menu.

## Configuration

### Email Settings

Go to 'LearnDash Email Settings' to configure the following:

- **Confirmation Email Subject:** Customize the subject of the confirmation email.
- **Confirmation Email Message:** Customize the message of the confirmation email. Use `[site]` to include the website name and `[name]` to include the user's display name.
- **Welcome Email Subject:** Customize the subject of the welcome email.
- **Welcome Email Message:** Customize the message of the welcome email. Use `[site]` to include the website name and `[name]` to include the user's display name.

### Available Tags

- **[site]**: Website name
- **[name]**: User name

### Send Test Emails

You can send test emails to see how your confirmation and welcome emails will look. Go to 'Send Test Email' under the 'LearnDash Email Settings' menu to send a test confirmation or welcome email.

## Changelog

### 1.0.1
- Initial release.

## Author

**Luis Pique**  
Website: [https://luispique.com](https://luispique.com)

## License

This plugin is licensed under the GPL-2.0+. For more information, see [http://www.gnu.org/licenses/gpl-2.0.txt](http://www.gnu.org/licenses/gpl-2.0.txt).
