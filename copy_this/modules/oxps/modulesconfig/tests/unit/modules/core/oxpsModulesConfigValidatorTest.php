<?php
/**
 * This Software is the property of OXID eSales and is protected
 * by copyright law - it is NOT Freeware.
 *
 * Any unauthorized use of this software without a valid license key
 * is a violation of the license agreement and will be prosecuted by
 * civil and criminal law.
 *
 * @category      module
 * @package       modulesconfig
 * @author        OXID Professional services
 * @link          http://www.oxid-esales.com
 * @copyright (C) OXID eSales AG 2003-2014
 */

/**
 * Class oxpsModulesConfigValidatorTest
 * Tests for core class oxpsModulesConfigValidator.
 *
 * @see oxpsModulesConfigValidator
 */
class oxpsModulesConfigValidatorTest extends OxidTestCase
{

    /**
     * Subject under the test.
     *
     * @var oxpsModulesConfigValidator
     */
    protected $SUT;


    /**
     * Set SUT state before test.
     */
    public function setUp()
    {
        parent::setUp();

        $this->SUT = $this->getMock( 'oxpsModulesConfigValidator', array('__call') );
    }

    /**
     * Import data validation data provider.
     *
     * @return array
     */
    public function modulesConfigurationValidationDataProvider()
    {
        $aSettingsDataHeader = array(
            '_OXID_ESHOP_MODULES_CONFIGURATION_' => array(
                'sShopVersion' => '5.2.0',
                'sShopEdition' => 'EE',
                'sShopId'      => 1,
                'aModules'     => array(),
            )
        );

        return array(
            array(array(), $aSettingsDataHeader, array('OXPS_MODULESCONFIG_ERR_EMPTY_DATA')),
            array(array(1), $aSettingsDataHeader, array('OXPS_MODULESCONFIG_ERR_INVALID_FORMAT')),
            array(array('some_setting' => 1), $aSettingsDataHeader, array('OXPS_MODULESCONFIG_ERR_INVALID_FORMAT')),
            array(array('_OXID_ESHOP_MODULES_CONFIGURATION_' => array()), $aSettingsDataHeader, array('OXPS_MODULESCONFIG_ERR_INVALID_FORMAT')),
            array(
                array('_OXID_ESHOP_MODULES_CONFIGURATION_' => array('5.2.0', 'EE', 1, array())),
                $aSettingsDataHeader,
                array('OXPS_MODULESCONFIG_ERR_INVALID_FORMAT')
            ),
            array(
                array(
                    '_OXID_ESHOP_MODULES_CONFIGURATION_' => array(
                        'sShopVersion', 'sShopEdition', 'sShopId', 'aModules' => array()
                    )
                ),
                $aSettingsDataHeader,
                array('OXPS_MODULESCONFIG_ERR_INVALID_FORMAT')
            ),
            array(
                array(
                    '_OXID_ESHOP_MODULES_CONFIGURATION_' => array(
                        'sShopVersion' => '5.2.0',
                        'sShopEdition' => 'EE',
                        'sShopId'      => 1,
                    )
                ),
                $aSettingsDataHeader,
                array('OXPS_MODULESCONFIG_ERR_INVALID_FORMAT')
            ),
            array(
                array(
                    '_OXID_ESHOP_MODULES_CONFIGURATION_' => array(
                        'sShopVersion' => '4.4.4',
                        'sShopEdition' => 'CE',
                        'sShopId'      => 'baseshop',
                        'aModules'     => array()
                    )
                ),
                $aSettingsDataHeader,
                array(
                    'OXPS_MODULESCONFIG_ERR_SHOP_VERSION',
                    'OXPS_MODULESCONFIG_ERR_SHOP_EDITION',
                    'OXPS_MODULESCONFIG_ERR_WRONG_SUBSHOP'
                )
            ),
            array(
                array(
                    '_OXID_ESHOP_MODULES_CONFIGURATION_' => array(
                        'sShopVersion' => '5.2.0',
                        'sShopEdition' => 'CE',
                        'sShopId'      => 'baseshop',
                        'aModules'     => array()
                    )
                ),
                $aSettingsDataHeader,
                array('OXPS_MODULESCONFIG_ERR_SHOP_EDITION', 'OXPS_MODULESCONFIG_ERR_WRONG_SUBSHOP')
            ),
            array(
                array(
                    '_OXID_ESHOP_MODULES_CONFIGURATION_' => array(
                        'sShopVersion' => '5.2.0',
                        'sShopEdition' => 'EE',
                        'sShopId'      => 'baseshop',
                        'aModules'     => array()
                    )
                ),
                $aSettingsDataHeader,
                array('OXPS_MODULESCONFIG_ERR_WRONG_SUBSHOP')
            ),
            array(
                array(
                    '_OXID_ESHOP_MODULES_CONFIGURATION_' => array(
                        'sShopVersion' => '5.2.0',
                        'sShopEdition' => 'EE',
                        'sShopId'      => 1,
                        'aModules'     => array()
                    )
                ),
                $aSettingsDataHeader,
                array()
            ),
        );
    }

    /**
     * @dataProvider modulesConfigurationValidationDataProvider
     */
    public function testValidate( array $aImportData, array $aSettingsDataHeader, array $aExpectedErrors )
    {
        $this->SUT->init( $aImportData, $aSettingsDataHeader );

        $this->assertSame( $aExpectedErrors, $this->SUT->validate() );
    }

    public function testValidate_nothingInitialized_returnEmptyDataError()
    {
        $this->assertSame( array('OXPS_MODULESCONFIG_ERR_EMPTY_DATA'), $this->SUT->validate() );
    }

    public function testValidate_noSettingHeaderInitialized_returnInvalidFormatError()
    {
        $this->SUT->init(
            array(
                '_OXID_ESHOP_MODULES_CONFIGURATION_' => array(
                    'sShopVersion' => '5.2.0',
                    'sShopEdition' => 'EE',
                    'sShopId'      => 1,
                    'aModules'     => array()
                )
            ),
            array()
        );

        $this->assertSame( array('OXPS_MODULESCONFIG_ERR_INVALID_FORMAT'), $this->SUT->validate() );
    }
}
