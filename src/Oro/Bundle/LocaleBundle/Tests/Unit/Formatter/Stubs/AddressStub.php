<?php

namespace Oro\Bundle\LocaleBundle\Tests\Unit\Formatter\Stubs;

use Oro\Bundle\LocaleBundle\Model\AddressInterface;
use Oro\Bundle\LocaleBundle\Model\FullNameInterface;

class AddressStub implements AddressInterface, FullNameInterface
{
    /**
     * @return string
     */
    public function getFirstName()
    {
        return 'Formatted';
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return 'User';
    }

    /**
     * @return string
     */
    public function getMiddleName()
    {
        return 'Name';
    }

    /**
     * @return string
     */
    public function getNamePrefix()
    {
        return '';
    }

    /**
     * @return string
     */
    public function getNameSuffix()
    {
        return '';
    }

    /**
     * Get street
     *
     * @return string
     */
    public function getStreet()
    {
        return '1 Tests str.';
    }

    /**
     * Get street2
     *
     * @return string
     */
    public function getStreet2()
    {
        return 'apartment 10';
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return 'New York';
    }

    /**
     * Get region
     *
     * @return string
     */
    public function getRegionName()
    {
        return 'New York';
    }

    /**
     * Get region code.
     *
     * @return string
     */
    public function getRegionCode()
    {
        return 'NY';
    }

    /**
     * Get postal_code
     *
     * @return string
     */
    public function getPostalCode()
    {
        return '12345';
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountryName()
    {
        return 'United States';
    }

    /**
     * Get country ISO3 code.
     *
     * @return string
     */
    public function getCountryIso3()
    {
        return 'USA';
    }

    /**
     * Get country ISO2 code.
     *
     * @return string
     */
    public function getCountryIso2()
    {
        return 'US';
    }

    /**
     * Get organization.
     *
     * @return string
     */
    public function getOrganization()
    {
        return 'Company Ltd.';
    }
}
