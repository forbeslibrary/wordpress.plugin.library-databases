Wordpress Plugin: Library Databases
===========

This is a Wordpress plugin for a library wishing to provide easy access to and organization of library databases and other electronic resources on the web.

Usage
-----

The plugin creates a custom post type called Library Databases. Each database has a *title*, *description*, an indication of its *availability* (available only in the library vs. availably freely on the web or with a library card) and a *primary url*. In addition, a database may have a *home use url* if the urls for using it in outside of the library differ.

### Upgrading
If you are upgrading from a version older than 1.0.0 you will need to reactivate
the plugin since it's name has changed (from Forbes Databases to Library Databases). When you reactivate the plugin it should automatically rename your shortcodes, taxonomies, and custom post types as necessary.

### Settings
Users with administrator access can set the IP Addresses to be considered 'in library' on the settings page found under `Settings > Library Databases`.

### Shortcodes
To include a list of databases on a page use the shortcode `[lib_database_list]`, optionally with the research_area parameter, e.g. `[lib_database_list research_area="business"]`.
