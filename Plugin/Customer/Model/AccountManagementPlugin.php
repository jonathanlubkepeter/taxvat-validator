<?php

namespace PeterTechnology\TaxvatValidator\Plugin\Customer\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\AccountManagement;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\InputException;
use Psr\Log\LoggerInterface as Logger;

class AccountManagementPlugin
{
    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var FilterGroupBuilder
     */
    protected $filterGroupBuilder;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;


    /**
     * @param Logger $logger
     * @param CustomerRepositoryInterface $customerRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param FilterBuilder $filterBuilder
     */
    public function __construct(
        Logger $logger,
        CustomerRepositoryInterface $customerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterGroupBuilder $filterGroupBuilder,
        FilterBuilder $filterBuilder
    ) {
        $this->logger = $logger;
        $this->customerRepository = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->filterBuilder = $filterBuilder;
    }

    /**
     * @param AccountManagement $subject
     * @param $customer
     * @param null $password
     * @param string $redirectUrl
     * @return array
     * @throws InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeCreateAccount(
        AccountManagement $subject,
        $customer,
        $password = null,
        $redirectUrl = ''
    )
    {
        if ($this->taxvatExist($customer->getTaxvat()) != 0) {
            $this->logger->error("CPF já existe: " . $customer->getTaxvat());
            throw new InputException(
                __('Um cliente já foi associado à esse CPF.')
            );
        }

        return [$customer, $password, $redirectUrl];
    }

    /**
     * @param AccountManagement $subject
     * @param $username
     * @param $password
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeAuthenticate(
        AccountManagement $subject,
        $username,
        $password
    )
    {
        if (strpos($username, '@') === false) {
            $username = $this->getEmailByTaxvat($username);
        }

        return [$username, $password];
    }

    /**
     * @param String $taxvat
     * @return int|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function taxvatExist(String $taxvat)
    {
        $customer = $this->filterBuilder->setField('taxvat')
            ->setValue($taxvat)
            ->setConditionType('eq')
            ->create();

        $filter = $this->filterGroupBuilder
            ->addFilter($customer)
            ->create();

        $this->searchCriteriaBuilder->setFilterGroups([$filter]);

        $qtd = count($this->customerRepository->getList($this->searchCriteriaBuilder->create())->getItems());

        return $qtd;
    }

    /**
     * @param String $taxvat
     * @return string|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getEmailByTaxvat(String $taxvat)
    {
        $customer = $this->filterBuilder->setField('taxvat')
            ->setValue($taxvat)
            ->setConditionType('eq')
            ->create();

        $filter = $this->filterGroupBuilder
            ->addFilter($customer)
            ->create();

        $this->searchCriteriaBuilder->setFilterGroups([$filter]);

        $customerData = $this->customerRepository->getList($this->searchCriteriaBuilder->create())->getItems();

        foreach ($customerData as $cust) {
            return $cust->getEmail();
        }
        $this->logger->error("Nenhum email encontrado para o CPF: $taxvat");
    }
}
