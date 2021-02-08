<?php

namespace PeterTecnology\TaxvatValidator\Test\Unit\Plugin\Customer\Model;

use Magento\Customer\Model\Customer;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Customer\Model\AccountManagement;
use PeterTecnology\TaxvatValidator\Plugin\Customer\Model\AccountManagementPlugin;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;

class AccountManagementPluginTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $customersMock;

    protected $customerMock;

    protected $accountManagementMock;

    protected $accountManagementPluginMock;

    protected $collectionFactory;

    protected function setUp()
    {
        $this->accountManagementMock = $this->createMock(AccountManagement::class);

        $this->customerMock = $this->getMockBuilder(Customer::class)
            ->setMethods(['getTaxvat'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerMock->method('getTaxvat')->willReturn('02546445030');


        $this->customersMock = $this->getMockBuilder(Customer::class)
            ->setMethods(['getTaxvat'])
            ->disableOriginalConstructor()
            ->getMock();



        $this->collectionFactory = $this->createMock(CollectionFactory::class);
        $this->collectionFactory->method('create')->willReturn([$this->customersMock]);

        $this->accountManagementPluginMock = $this->getMockBuilder(AccountManagementPlugin::class)
            ->setMethods(['__construct'])
            ->setConstructorArgs(
                [
                    $this->collectionFactory
                ]
            )
            ->getMock();
    }

    /**
     * @test
     */
    public function testReturnArrayToCreateAccountTaxvatIsNotExistInCollection()
    {
        $result = $this->accountManagementPluginMock->beforeCreateAccount($this->accountManagementMock, $this->customerMock, null);
        $this->customersMock->method('getTaxvat')->willReturn(null);
        $this->assertEquals([$this->customerMock, null, ''], $result);
    }

    /**
     * @test
     */
    public function testException()
    {
        $this->customersMock->method('getTaxvat')->willReturn('02546445030');
        $this->expectException(\Exception::class);
        $this->accountManagementPluginMock->beforeCreateAccount($this->accountManagementMock, $this->customerMock, null);

    }

}
