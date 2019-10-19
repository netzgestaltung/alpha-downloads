# Migrate from Delightful Downloads to Alpha Downloads with Sandbox Theme

## Backup

## Files
Download: https://github.com/netzgestaltung/alpha-downloads
Copy Alpha Downloads Plugin-Files to /wp-content/plugins/
Copy new Sandbox template files update to /wp-content/themes/sandbox/

## Backend
Activate Plugin, Set Settings

General:
Categories and Tags: yes
Members Only: yes
Redirect page: Lernmaterialen für den Unterricht
Open in Browser: Default
Block user Agents: Default

Shortcodes: Default
Statistics: Default

Advanced:
Output CSS: no
Cache: yes
Time: 15
Download Address: lernmaterial
Upload Directory: lernmaterialien
Folder Protection: yes
Complete Uninstall: no

Plugins Page: 
Deactivate and Delete "Delightful Downloads" Plugin

## SQL
Post Type:
UPDATE `alfa_wp_posts` SET `post_type` = 'alpha_download' WHERE `post_type` = 'dedo_download';
https://wpsites.net/wordpress-admin/use-sql-query-in-phpmyadmin-to-change-custom-post-types/

Custom Post Fields:
UPDATE `alfa_wp_postmeta` SET `meta_key` = '_alpha_file_url' WHERE `meta_key` = '_dedo_file_url';
UPDATE `alfa_wp_postmeta` SET `meta_key` = '_alpha_file_size' WHERE `meta_key` = '_dedo_file_size';
UPDATE `alfa_wp_postmeta` SET `meta_key` = '_alpha_file_count' WHERE `meta_key` = '_dedo_file_count';
UPDATE `alfa_wp_postmeta` SET `meta_key` = '_alpha_file_options' WHERE `meta_key` = '_dedo_file_options';
https://trickspanda.com/rename-custom-fields-wordpress/
























