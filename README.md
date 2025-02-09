# EXT:recordmodules - Separate backend modules for record types

This extension makes it possible to configure backend modules for some type of records.
Editors do not have to switch to module "List" and search for the correct sys folder to find the records.

## How to configure

Order of the settings:

TCA -> Extension Settings -> TypoScript Settings

So, TypoScript will override Extension Settings which override TCA settings.

### Via TCA

In your site package, use TCA overrides to activate a custom module for a record type.

Configuration/TCA/Overrides/sys_category.php
```
$GLOBALS['TCA']['sys_category']['ctrl']['recordModule'] = [
    'activate' => true,
    'pids' => 1,
    'title' => 'LLL:EXT:site_package/Resources/Private/locallang.xlf:myTitle',
    'icon' => 'EXT:site_package/Resources/Public/Icons/my_icon.svg'
    'iconIdentifier' => 'my-ext-my-icon'
];
```

`pids` is optional and can be a comma separated string of page ids, a single id or an array of ids.
If set, the module will only list records of these page ids (if the current user has access).
if **not** set, the module will show the normal page tree for the user to select a page. Note that only records of the current table are listed in the module.

`title` is optional. Default is the title of the table as specified in TCA. Use a string or a LLL reference.

`icon` and `iconIdentifier` are optional. Default is the icon as specified in TCA.

### Via Extension Settings

* Specify tables and pids in a single input field
* Choose if custom modules should be added inside module "Web" or not.
* Specify custom sorting order of custom modules.

### Via TypoScript

Here you can specify all table based options as seen in TCA configuration AND global options like the custom sorting and position of the custom modules.

```
module.tx_recordmodules {
    addToWebModule = 1
    sorting = fe_users,be_users,sys_log

    tables {
        be_users {
            activate = 0
            pids = 1,2,3,4
        }
    }
}
```