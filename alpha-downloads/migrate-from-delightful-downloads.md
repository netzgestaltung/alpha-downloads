# Migrate from Delightful Downloads to Alpha Downloads with Sandbox Theme

## Backup

## Files
Download: https://github.com/netzgestaltung/alpha-downloads
Copy Alpha Downloads Plugin-Files to /wp-content/plugins/
Copy new Sandbox template files update to /wp-content/themes/sandbox/

## Backend
Goto Settings->Just Custom Fields (if installed) and export settings

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
UPDATE `{prefix}_wp_postmeta` SET `meta_key` = '_alpha_file_url' WHERE `meta_key` = '_dedo_file_url';
UPDATE `{prefix}_wp_postmeta` SET `meta_key` = '_alpha_file_size' WHERE `meta_key` = '_dedo_file_size';
UPDATE `{prefix}_wp_postmeta` SET `meta_key` = '_alpha_file_count' WHERE `meta_key` = '_dedo_file_count';
UPDATE `{prefix}_wp_postmeta` SET `meta_key` = '_alpha_file_options' WHERE `meta_key` = '_dedo_file_options';
https://trickspanda.com/rename-custom-fields-wordpress/

## Backend
Go to Settings->Permalinks and press the "Save" button once.
Edit Exported JCF Settings - replace "dedo" to "alpha"
Goto Settings->Just Custom Fields (if installed) and import edited settings

https://wpexplorer-themes.com/total/docs/enable-comments-for-pages/
Go to Downloads, set "Ansicht anpassen"->"Einträge pro Seite" to 300 and apply
Mark all viewable Posts, klick on Dropdown "Mehrfachaktionen"->"Bearbeiten" and apply
Set Dropdown "Kommentare" to "Erlauben"

Repeat for all 5 Pages, more then 300 per Page seems to reach the PHP Limit for mass editing

## SQL
move custom field to normal content field

  für alle zeilen in der tabelle `alfa_wp_postmeta`, deren spalte `meta_key` den wert `_download-description` enthält:

    lese die spalte `post_id` der zeile aus
    lese die spalte `meta_value` der zeile aus
    
    für alle zeilen in der tabelle `alfa_wp_posts`, deren spalte `post_id` mit der ausgelesenen übereinstimmt
      speichere in der spalte `post_content` den inhalt der ausgelesenen `meta_value`      
    ende für alle zeilen
    
    lösche die den inhalt der spalte `meta_value`
    
  ende für alle zeilen

























