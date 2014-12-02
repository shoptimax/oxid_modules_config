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
 * Class oxpsModulesConfigContentTest
 * Tests for model oxpsModulesConfigContent.
 *
 * @see oxpsModulesConfigContent
 */
class oxpsModulesConfigContentTest extends OxidTestCase
{

    /**
     * Subject under the test.
     *
     * @var oxpsModulesConfigContent
     */
    protected $SUT;


    /**
     * Set SUT state before test.
     */
    public function setUp()
    {
        parent::setUp();

        $this->SUT = $this->getMock('oxpsModulesConfigContent', array('__call'));
    }


    public function testGetModulesList()
    {
        // Config mock
        $oConfig = $this->getMock('oxConfig', array('getModulesDir'));
        $oConfig->expects($this->once())->method('getModulesDir')->will($this->returnValue('/shop/modules/'));

        oxRegistry::set('oxConfig', $oConfig);

        // Modules list mock
        $oModuleList = $this->getMock('oxModuleList', array('__call', 'getModulesFromDir'));
        $oModuleList->expects($this->once())->method('getModulesFromDir')->with('/shop/modules/')->will(
            $this->returnValue(
                array(
                    'my_module'         => (object) array('version' => '1.0.0'),
                    'oxpsmodulesconfig' => (object) array('version' => '0.1.0'),
                    'good_extension'    => (object) array('version' => '0.2.5'),
                )
            )
        );

        oxTestModules::addModuleObject('oxModuleList', $oModuleList);

        $this->assertEquals(
            array(
                'my_module'      => (object) array('version' => '1.0.0'),
                'good_extension' => (object) array('version' => '0.2.5'),
            ),
            $this->SUT->getModulesList()
        );
    }


    public function testGetSettingsList()
    {
        $mSettings = $this->SUT->getSettingsList();

        $this->assertTrue(is_array($mSettings));
        $this->assertArrayHasKey('version', $mSettings);
        $this->assertArrayHasKey('extend', $mSettings);
        $this->assertArrayHasKey('files', $mSettings);
        $this->assertArrayHasKey('templates', $mSettings);
        $this->assertArrayHasKey('blocks', $mSettings);
        $this->assertArrayHasKey('settings', $mSettings);
        $this->assertArrayHasKey('events', $mSettings);
    }
}
