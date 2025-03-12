# Record Modules

This extension makes it possible to configure backend modules for some type of records.
Editors do not have to switch to module "List" and search for the correct sys folder to find the records.

The generated backend modules can be shown/hidden for editors using the normal ACL settings in backend usergroup recods.

## Installation

Install the extension via Composer:

```
composer req rfuehricht/recordmodules
```

## Configuration

Configuration in database records overrides settings in TCA.

After creating of a configuration record or any change in the configuration, the system caches must be cleared and the backend must be reloaded.

### Via TCA

In your site package, use TCA overrides to activate a custom module for a record type.

Configuration/TCA/Overrides/sys_category.php
```
$GLOBALS['TCA']['sys_category']['ctrl']['recordModule'] = [
    'activate' => true,
    'pids' => 1,
    'parent' => 'web',
    'sorting' => 12,
    'title' => 'LLL:EXT:site_package/Resources/Private/locallang.xlf:myTitle',
    'icon' => 'EXT:site_package/Resources/Public/Icons/my_icon.svg'
    'iconIdentifier' => 'my-ext-my-icon'
];
```

`parent` is optional and specifies where to put the module. Default is a new custom module group "Records".

`pids` is optional and can be a comma separated string of page ids, a single id or an array of ids.
If set, the module will only list records of these page ids (if the current user has access).
if **not** set, the module will show the normal page tree for the user to select a page. Note that only records of the current table are listed in the module.

`sorting` is optional. Use integer values to specify sort order of your custom modules when creating more than one.

`title` is optional. Default is the title of the table as specified in TCA. Use a string or a LLL reference.

`icon` and `iconIdentifier` are optional. Default is the icon as specified in TCA.

### Via configuration records

On root level you can create configuration records for each desired backend module.

The settings are nearly the same as the settings in TCA.

## Report issues

You can report issues at https://github.com/rfuehricht/typo3-recordmodules/issues.
