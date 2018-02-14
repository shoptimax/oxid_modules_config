----

# OXID Core and Module Configurations Import and Export module

----

Tools to export, backup and import OXID eShop modules, module settings, shop settings and module activation.
There are 2 ways to use the functionality:
* via OXID console commands
* via a GUI based dashboard in eShop administration area under _Service -> Module Configuration Im-/Exporter_. 

All relevant modules configuration is being used including: Versions, Extended classes, Module classes, Templates, Blocks, Settings and Events. 

# Install

## Install using a local repository

* Create a local directory for repositories in your project, e.g. `oxideshop/extensions`.
* Check-out this module and move it to the directory you just created
* Add the repository to your project's compser.json, e.g. like this:

  ```json
    "repositories": {
        "oxid-community/oxid_modules_config": {
            "type": "path",
            "url": "extensions/oxps_modulesconfig/"
        }
    }
  ```
## Install from VCS

* Require `oxid-community/oxid_modules_config`
 
## Development
In order to run the tests you need to have the [OXID testing-library v1.0.6](https://github.com/OXID-eSales/testing_library/tree/v1.0.6) installed. As best practice the testing-library
should be installed in shop vendor directory.

## History
A previous version of this module with less features is on [GitHub](https://github.com/OXIDprojects/oxid_modules_config).
