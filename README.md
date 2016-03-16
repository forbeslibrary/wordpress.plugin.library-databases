Wordpress Plugin: Library Databases
===========

This is a Wordpress plugin for a library wishing to provide easy access to and organization of library databases and other electronic resources on the web.

Usage
-----

The plugin creates a custom post type called Library Databases. Each database has a *title*, *description*, an indication of its *availability* (available only in the library vs. availably freely on the web or with a library card) and a *primary url*. In addition, a database may have a *home use url* if the urls for using it in outside of the library differ.

### Upgrading
It is best to deactivate the plugin before upgrading and reactivate it after upgrading. If you are upgrading from a version older than 1.0.0 you **must** reactivate the plugin since it's name has changed (from Forbes Databases to Library Databases). When you reactivate the plugin it will automatically rename your shortcodes, taxonomies, and custom post types as necessary.

Note that shortcode attributes are not automatically updated. In particular, early versions of the plugin used the `exclude_free` argument which is not supported as of version 1.0.0. In versions 1.0.1 and later you may use the `exclude_category` shortcode argument to exclude a category.

### Settings
Users with administrator access can set the IP Addresses to be considered 'in library' on the settings page found under `Settings > Library Databases`.

### Shortcodes
To include a list of databases on a page use the shortcode `[lib_database_list]`. To instead present a dropdown menu use the shortcode `[lib_database_select]`.

Both shortcodes take the following optional arguments:
- `research_area=slug` show only databases in the research area with the given slug
- `exclude_category=slug` show only databases which are not in the category with the given slug
