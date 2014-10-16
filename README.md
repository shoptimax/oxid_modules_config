# Modules Config

----

Modules configuration export and import tools.
It is a GUI based dashboard in administration area under Service -> Modules Config.
All relevant modules configuration is involved including: Versions, Extended classes, Module classes, Templates, Blocks, Settings and Events.

## Installation
- Copy content of _copy\_this/_ folder to eShop root directory
- Activate the module in administration back end

## Usage
- Go to Service -> Modules Config
- Select which modules and settings to export, backup or import
- Press "Export" to download settings as JSON file immediately
- Press "Backup" to save JSON file in _export/modules\_config/_
- Choose a file to import and press "Import" to update modues settings from a JSON file
  Before the an import a full backup is done and after the import, eShop cache is cleared
  
## JSON file structure
- It is built from array with eShop data and settings data
- eShop version, edition and sub-shop ID are stored to identify shop
- Module configuration is split for each module separately by module ID
- Module configuration keys are same as in metadata file and value are as stored in eShop but non encrypted
- Since it is a text file, it could be also edited by hand and put under version control.

## To do and nice to have features for future releases
- Reformat for OXID PSR standards
- Force mode to allow importing configuration to any shop without checking versions
- Export and import off all sub-shops data in one file
- On new module data imported, trigger activation and rebuild views
- Log import of each single setting to a file
- Automatic restore of last backup on at least one setting import failure
- More validation rules for import data: check if imported and selected modules intersect
- For extended classes settings also split it by modules ID (metadata parsing needed)
- Refactor long admin controller class
- Add an option to export / import global CMS snippets
