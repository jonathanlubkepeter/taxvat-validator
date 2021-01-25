<?php

namespace PeterTecnology\TaxvatValidator\Plugin\Customer\Model;

use Exception;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\Exception\InputException;

class AccountManagementPlugin
{
    protected $customerFactory;
    protected $collectionFactory;

    public function __construct(
        CustomerFactory $customerFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->customerFactory = $customerFactory;
        $this->collectionFactory = $collectionFactory;
    }

    public function beforeCreateAccount(
        AccountManagement $subject,
        $customer,
        $password = null,
        $redirectUrl = ''
    ) {
        $taxvat = $customer->getTaxvat();

        try {
            $this->taxvatExist($taxvat);
        } catch (InputException $e) {
            throw new InputException(
                __('Um cliente já foi associado à esse CPF.')
            );
        }

        return [$customer, $password, $redirectUrl];
    }

    public function beforeAuthenticate(
        AccountManagement $subject,
        $username,
        $password
    ) {
        if (strpos($username, '@') === false) {

            try {
                $customerEmail = $this->returnEmail($username);
            } catch (InputException $e) {
                throw new InputException(
                    __('We did not find any email associated with this cpf.')
                );
            }

            $username = $customerEmail;

            return [$username, $password];
        }
    }

    protected function taxvatExist(String $taxvat)
    {
        $customers = $this->collectionFactory->create();

        foreach ($customers as $key => $cust) {
            if ($cust->getTaxvat() === $taxvat) {
                throw new InputException(
                    __('Um cliente já foi associado à esse CPF.')
                );
            }
        }
        return false;
    }

    protected function returnEmail(String $taxvat)
    {
        $customers = $this->collectionFactory->create();

        foreach ($customers as $key => $cust) {
            if ($cust->getTaxvat() === $taxvat) {
                return $cust->getEmail();
            }
        }
    }
}
