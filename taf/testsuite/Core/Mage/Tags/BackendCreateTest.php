<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    tests
 * @package     selenium
 * @subpackage  tests
 * @author      Magento Core Team <core@magentocommerce.com>
 * @copyright   Copyright (c) 2013 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Tag creation tests for Backend
 *
 * @package     selenium
 * @subpackage  tests
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Core_Mage_Tags_BackendCreateTest extends Mage_Selenium_TestCase
{
    /**
     * <p>Preconditions:</p>
     * <p>Navigate to Catalog -> Tags -> All tags</p>
     */
    protected function assertPreConditions()
    {
        $this->loginAdminUser();
        $this->navigate('all_tags');
    }

    protected function tearDownAfterTest()
    {
        $this->loginAdminUser();
        $this->navigate('all_tags');
        $this->tagsHelper()->deleteAllTags();
    }

    /**
     * <p>Creating a new tag</p>
     * <p>Steps:</p>
     * <p>1. Click button "Add New Tag"</p>
     * <p>2. Fill in the fields in General Information</p>
     * <p>3. Click button "Save Tag"</p>
     * <p>Expected result:</p>
     * <p>Received the message that the tag has been saved.</p>
     *
     * @test
     */
    public function createNew()
    {
        //Setup
        $setData = $this->loadDataSet('Tag', 'backend_new_tag');
        //Steps
        $this->tagsHelper()->addTag($setData);
        //Verify
        $this->assertTrue($this->checkCurrentPage('all_tags'), $this->getParsedMessages());
        $this->assertMessagePresent('success', 'success_saved_tag');
    }

    /**
     * <p>Creating a new tag in backend with empty required fields.</p>
     * <p>Steps:</p>
     * <p>1. Click button "Add New Tag"</p>
     * <p>2. Fill in the fields in General Information, but leave tag name empty</p>
     * <p>3. Click button "Save Tag"</p>
     * <p>Expected result:</p>
     * <p>Received error message "This is a required field"</p>
     *
     * @test
     */
    public function withEmptyTagName()
    {
        //Setup
        $setData = $this->loadDataSet('Tag', 'backend_new_tag', array('tag_name' => ''));
        //Steps
        $this->tagsHelper()->addTag($setData);
        //Verify
        $this->assertMessagePresent('validation', 'required_name');
        $this->assertTrue($this->verifyMessagesCount(), $this->getParsedMessages());
    }

    /**
     * <p>Creating a new tag with special values (long, special chars).</p>
     * <p>Steps:</p>
     * <p>1. Click button "Add New Tag"</p>
     * <p>2. Fill in the fields in General Information</p>
     * <p>3. Click button "Save Tag"</p>
     * <p>4. Open the tag</p>
     * <p>Expected result:</p>
     * <p>All fields has the same values.</p>
     *
     * @param array $specialValue
     *
     * @test
     * @dataProvider withSpecialValuesDataProvider
     * @depends createNew
     */
    public function withSpecialValues(array $specialValue)
    {
        //Data
        $setData = $this->loadDataSet('Tag', 'backend_new_tag', $specialValue);
        $tagToOpen = $this->loadDataSet('Tag', 'backend_search_tag', array('tag_name' => $setData['tag_name']));
        //Steps
        $this->tagsHelper()->addTag($setData);
        //Verify
        $this->assertTrue($this->checkCurrentPage('all_tags'), $this->getParsedMessages());
        $this->assertMessagePresent('success', 'success_saved_tag');
        $this->tagsHelper()->openTag($tagToOpen);
        $this->verifyForm($setData);
    }

    public function withSpecialValuesDataProvider()
    {
        return array(
            array(array('tag_name' => $this->generate('string', 255))), //long
            array(array('tag_name' => $this->generate('string', 50, ':punct:'))), //special chars
            array(array('base_popularity' => '4294967295')), //max int(10) unsigned
        );
    }

    /**
     * <p>Creating a tag and assign it to a product as administrator</p>
     * <p>Steps:</p>
     * <p>1. Click button "Add New Tag"</p>
     * <p>2. Fill in the fields in General Information</p>
     * <p>3. Click button "Save and Continue Edit"</p>
     * <p>4. Fill in Products Tagged by Administrators</p>
     * <p>5. Click button "Save Tag"</p>
     * <p>Expected result:</p>
     * <p>Received the message that the tag has been saved.</p>
     * <p>Steps:</p>
     * <p>6. Go to the product settings</p>
     * <p>7. Open Product Tags tab</p>
     * <p>Expected result:</p>
     * <p>The assigned tag is displayed.</p>
     *
     * @test
     */
    public function productTaggedByAdministrator()
    {
        $simple = $this->loadDataSet('Product', 'simple_product_visible');
        $setData = $this->loadDataSet('Tag', 'backend_new_tag_with_product',
            array('prod_tag_admin_name' => $simple['general_name']));
        //Setup
        $this->navigate('manage_products');
        $this->productHelper()->createProduct($simple);
        $this->assertMessagePresent('success', 'success_saved_product');
        //Steps
        $this->navigate('all_tags');
        $this->tagsHelper()->addTag($setData);
        //Verify
        $this->assertTrue($this->checkCurrentPage('all_tags'), $this->getParsedMessages());
        $this->assertMessagePresent('success', 'success_saved_tag');
        $tagSearchData = array('tag_name' => $setData['tag_name']);
        $productSearchData = array('general_name' => $simple['general_name']);
        $this->navigate('manage_products');
        $this->assertTrue($this->tagsHelper()->verifyTagProduct($tagSearchData, $productSearchData),
            $this->getParsedMessages());
    }
}