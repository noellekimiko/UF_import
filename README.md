# IMPORT
Basic tool to import users from a CSV to [userfrosting4](https://userfrosting.com)

# Installation
1. Edit UserFrosting `app/sprinkles.json` file and add `import` to the `base` list. Your `sprinkles.json` should look like this:
```
{
    "require": {
    },
    "base": [
        "core",
        "account",
        "admin",
        "import"
    ]
}
```  
2. Open up a command prompt at the root of your UserFrosting app and run `composer update` to include the sprinkle in your site.
3. Also run `php bakery build-assets --compile` to get all the assets included.

# Configuration
You can upload CSV files either with or without a header row. The configuration file (`config/default.php`) is currently set to expect a header row with the default UserFrosting user fields: 
*  user_name
*  first_name
*  last_name
*  email
*  locale
*  group_id

If you include a header row, the order of the columns is not important as values are keyed directly to the column header.

You can also change the `header_row` setting to `FALSE` in which case the `header_keys` array will be used. In this case, please ensure your upload file has the columns in the exact order shown in the configuration file. 

## Notes
*  This sprinkle must be loaded after `account` as it replaces the default admin dashboard for viewing users.
*  Code may contain bugs or errors. Please open an issue if you find one.
