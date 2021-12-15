# WordPress Plugin: Library Databases
This is a WordPress plugin for a library wishing to provide easy access to and organization of library databases and other electronic resources on the web.

## Usage
The plugin creates a custom post type called Library Databases. Each database has a *title*, *description*, an indication of its *availability* (available only in the library vs. availably freely on the web or with a library card) and a *primary url*. In addition, a database may have a *home use url* if the urls for using it in outside of the library differ.

### Upgrading
As of v2.0.0 the automatic migration from versions before v1.0.0 has been discontinued. In the unlikely event that you are upgrading from a version older than v2.0.0 be sure to upgrade to one of the 1.x.x versions before upgrading to v2.0.0.

### Settings
Users with administrator access can set the IP Addresses to be considered 'in library' on the settings page found under `Settings > Library Databases`.

### Shortcodes
To include a list of databases on a page use the shortcode `[lib_database_list]`. To instead present a dropdown menu use the shortcode `[lib_database_select]`.

Both shortcodes take the following optional arguments:
- `research_area=slug` show only databases in the research area with the given slug
- `exclude_category=slug` show only databases which are not in the category with the given slug

The `[lib_database_select]` shortcode also takes two additional optional arguments:

- `title` the label for the select menu (defaults to **Database Quick Access**)
- `select_message` the initial option in the menu (defaults to **Select A Database**)
