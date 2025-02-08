# EXT:recordmodules - Separate backend modules for record types

This extension makes it possible to configure backend modules for some type of records.
Editors do not have to switch to module "List" and search for the correct sys folder to find the records.

## How it works

In your site package, use TCA overrides to activate a custom module for a record type.

Configuration/TCA/Overrides/sys_category.php
```
$GLOBALS['TCA']['sys_category']['ctrl']['recordModule'] = [
    'activate' => true,
    'pids' => 1
];
```

`pids` is optional and can be a comma separated string of page ids, a single id or an array of ids.
If set, the module will only list records of these page ids (if the current user has access).
if **not** set, the module will show the normal page tree for the user to select a page. Note that only records of the current table are listed in the module.

