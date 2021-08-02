<?php

namespace PeterTechnology\TaxvatValidator\Test\Unit\Plugin\Customer\Model;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Customer\Model\AccountManagement;
use Magento\Framework\Api\SearchCriteriaBuilder;
use PeterTechnology\TaxvatValidator\Plugin\Customer\Model\AccountManagementPlugin;
use Monolog\Logger;

class AccountManagementPluginTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $customerMock;

    /**
     * @var MockObject
     */
    protected $accountManagementPluginMock;

    /**
     * @var MockObject
     */
    protected $accountManagementMock;

    /**
     * @var MockObject
     */
    protected $loggerMock;

    /**
     * @var MockObject
     */
    protected $customerRepositoryMock;

    /**
     * @var MockObject
     */
    protected $searchCriteriaBuilderMock;

    /**
     * @var MockObject
     */
    protected $filterBuilderMock;

    /**
     * @var MockObject
     */
    protected $filterGroupBuilderMock;

    /**
     * @var MockObject
     */
    protected $customerSearchMock;


    public function setUp() : void
    {
        $this->accountManagementMock = $this->createMock(AccountManagement::class);

        $this->customerMock = $this->getMockBuilder(Customer::class)
            ->addMethods(['getTaxvat', 'getEmail'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerMock = $this->createMock(Logger::class);

        $searchCriteriaMock = $this->createMock(\Magento\Framework\Api\SearchCriteria::class);
        $this->searchCriteriaBuilderMock = $this->createMock(SearchCriteriaBuilder::class);
        $this->searchCriteriaBuilderMock->method('setFilterGroups')->withAnyParameters()->willReturnSelf();
        $this->searchCriteriaBuilderMock->method('create')->willReturn($searchCriteriaMock);


        $filterMock = $this->createMock(\Magento\Framework\Api\Filter::class);
        $this->filterBuilderMock = $this->createMock(FilterBuilder::class);
        $this->filterBuilderMock->method('setField')->withAnyParameters()->willReturnSelf();
        $this->filterBuilderMock->method('setValue')->withAnyParameters()->willReturnSelf();
        $this->filterBuilderMock->method('setConditionType')->withAnyParameters()->willReturnSelf();
        $this->filterBuilderMock->method('create')->willReturn($filterMock);


        $abstractSimpleObjectBuilderMock = $this->createMock(\Magento\Framework\Api\AbstractSimpleObjectBuilder::class);
        $this->filterGroupBuilderMock = $this->createMock(FilterGroupBuilder::class);
        $this->filterGroupBuilderMock->method('addFilter')->withAnyParameters()->willReturnSelf();
        $this->filterGroupBuilderMock->method('create')->withAnyParameters()->willReturn($abstractSimpleObjectBuilderMock);


        $this->customerSearchMock = $this->createMock(\Magento\Customer\Api\Data\CustomerSearchResultsInterface::class);
        $this->customerRepositoryMock = $this->createMock(CustomerRepository::class);
        $this->customerRepositoryMock->method('getList')->willReturn($this->customerSearchMock);


        $this->accountManagementPluginMock = $this->getMockBuilder(AccountManagementPlugin::class)
            ->onlyMethods(['__construct'])
            ->setConstructorArgs(
                [
                    $this->loggerMock,
                    $this->customerRepositoryMock,
                    $this->searchCriteriaBuilderMock,
                    $this->filterGroupBuilderMock,
                    $this->filterBuilderMock
                ]
            )
            ->getMock();
    }

    /**
     * @test
     * createAccount plugin before
     */
    public function testReturnArrayToCreateAccountTaxvatIsNotExistInCollection()
    {
        $this->customerMock->method('getTaxvat')->willReturn('12345678910');
        $this->customerSearchMock->method('getItems')->willReturn([]);
        $result = $this->accountManagementPluginMock->beforeCreateAccount($this->accountManagementMock, $this->customerMock, null);
        $this->assertEquals([$this->customerMock, null, ''], $result);
    }

    /**
     * @test
     * createAccount plugin before
     */
    public function testReturnArrayToCreateAccountTaxvatExistInCollection()
    {
        $this->customerMock->method('getTaxvat')->willReturn('12345678910');
        $this->customerSearchMock->method('getItems')->willReturn(['df', 'dada']);
        $this->expectException(\Exception::class);
        $this->accountManagementPluginMock->beforeCreateAccount($this->accountManagementMock, $this->customerMock, null);
    }

    /**
     * @test
     * authenticate plugin before
     */
    public function testLoginWithEmail()
    {
        $username = "teste@teste.com";
        $password = "sr76de12";
        $result = $this->accountManagementPluginMock->beforeAuthenticate($this->accountManagementMock, $username, $password);
        $this->assertEquals([$username, $password], $result);
    }

    /**
     * @test
     * authenticate plugin before
     */
    public function testLoginWithCpf()
    {
        $username = "12345678910";
        $password = "sr76de12";
        $this->customerSearchMock->method('getItems')->willReturn([$this->customerMock]);
        $this->customerMock->method('getEmail')->willReturn("teste@teste.com");
        $result = $this->accountManagementPluginMock->beforeAuthenticate($this->accountManagementMock, $username, $password);
        $this->assertNotEquals([$username, $password], $result);
    }

    /**
     * @test
     * authenticate plugin before
     */
    public function testLoginCpfWithoutEmailOrWithoutCustomer()
    {
        $username = "12345678910";
        $password = "sr76de12";
        $this->customerSearchMock->method('getItems')->willReturn([]);
        $this->accountManagementPluginMock->beforeAuthenticate($this->accountManagementMock, $username, $password);
    }

}
