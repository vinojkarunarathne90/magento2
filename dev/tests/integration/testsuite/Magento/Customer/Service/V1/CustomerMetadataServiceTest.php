<?php
/**
 * Integration test for service layer \Magento\Customer\Service\Eav\AttributeMetadataV1
 *
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Customer\Service\V1;

class CustomerMetadataServiceTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Customer\Service\V1\CustomerServiceInterface */
    private $_customerService;

    /** @var \Magento\Customer\Service\V1\CustomerMetadataServiceInterface */
    private $_service;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_customerService = $objectManager->create('Magento\Customer\Service\V1\CustomerServiceInterface');
        $this->_service = $objectManager->create('Magento\Customer\Service\V1\CustomerMetadataServiceInterface');
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer.php
     */
    public function testGetCustomerAttributeMetadata()
    {
        // Expect these attributes to exist but do not check the value
        $expectAttrsWOutVals = array('updated_at', 'created_at', 'password_hash');

        // Expect these attributes to exist and check the value - values come from _files/customer.php
        $expectAttrsWithVals = array(
            'entity_id'                 => '1',
            'entity_type_id'            => '1',
            'attribute_set_id'          => '1',
            'website_id'                => '1',
            'store_id'                  => '1',
            'group_id'                  => '1',
            'disable_auto_group_change' => '0',
            'firstname'                 => 'Firstname',
            'lastname'                  => 'Lastname',
            'email'                     => 'customer@example.com',
            'default_billing'           => '1',
            'default_shipping'          => '1',
        );

        $customer = $this->_customerService->getCustomer(1);
        $this->assertNotNull($customer);

        $attributes = $customer->getAttributes();
        $this->assertNotEmpty($attributes);

        foreach ($attributes as $attributeCode => $attributeValue) {
            $this->assertNotNull($attributeCode);
            $this->assertNotNull($attributeValue);
            $attributeMetadata = $this->_service->getCustomerAttributeMetadata($attributeCode);
            $attrMetadataCode = $attributeMetadata->getAttributeCode();
            $this->assertSame($attributeCode, $attrMetadataCode);
            if (($key = array_search($attrMetadataCode, $expectAttrsWOutVals)) !== false) {
                unset($expectAttrsWOutVals[$key]);
            } else {
                $this->assertArrayHasKey($attrMetadataCode, $expectAttrsWithVals);
                $this->assertSame(
                    $expectAttrsWithVals[$attrMetadataCode],
                    $attributeValue,
                    "Failed for $attrMetadataCode"
                );
                unset($expectAttrsWithVals[$attrMetadataCode]);
            }
        }
        $this->assertEmpty($expectAttrsWOutVals);
        $this->assertEmpty($expectAttrsWithVals);
    }

    public function testAttributeMetadataCached()
    {
        $firstCallMetadata = $this->_service->getAddressAttributeMetadata('firstname');
        $secondCallMetadata = $this->_service->getAddressAttributeMetadata('firstname');

        $this->assertSame($firstCallMetadata, $secondCallMetadata);

    }

    public function testGetAddressAttributeMetadata()
    {
        $vatValidMetadata = $this->_service->getAddressAttributeMetadata('vat_is_valid');

        $this->assertNotNull($vatValidMetadata);
        $this->assertEquals('vat_is_valid', $vatValidMetadata->getAttributeCode());
        $this->assertEquals('text', $vatValidMetadata->getFrontendInput());
        $this->assertEquals('VAT number validity', $vatValidMetadata->getStoreLabel());
    }
}
