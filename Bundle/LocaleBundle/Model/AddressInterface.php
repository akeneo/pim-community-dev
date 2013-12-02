<?php

namespace Oro\Bundle\LocaleBundle\Model;

interface AddressInterface
{
    /**
     * Get street
     *
     * @return string
     */
    public function getStreet();

    /**
     * Get street2
     *
     * @return string
     */
    public function getStreet2();

    /**
     * Get city
     *
     * @return string
     */
    public function getCity();

    /**
     * Get region
     *
     * @return string
     */
    public function getRegionName();

    /**
     * Get region code.
     *
     * @return string
     */
    public function getRegionCode();

    /**
     * Get postal_code
     *
     * @return string
     */
    public function getPostalCode();

    /**
     * Get country
     *
     * @return string
     */
    public function getCountryName();

    /**
     * Get country ISO3 code.
     *
     * @return string
     */
    public function getCountryIso3();

    /**
     * Get country ISO2 code.
     *
     * @return string
     */
    public function getCountryIso2();

    /**
     * Get organization.
     *
     * @return string
     */
    public function getOrganization();
}
