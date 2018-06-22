<?php

declare(strict_types=1);

namespace AkeneoEnterprise\Test\Acceptance\SuggestData\Context;

use Behat\Behat\Context\Context;
use PimEnterprise\Component\SuggestData\Application\ManageMapping;
use PimEnterprise\Component\SuggestData\Repository\IdentifiersMappingRepositoryInterface;
use PHPUnit\Framework\Assert;

class IdentifiersMapping implements Context
{
    private
        $manageMapping,
        $identifiersMappingRepository;

    public function __construct(ManageMapping $manageMapping, IdentifiersMappingRepositoryInterface $identifiersMappingRepository)
    {
        $this->manageMapping = $manageMapping;
        $this->identifiersMappingRepository = $identifiersMappingRepository;
    }

    /**
     * @When I map pim.ai identifiers to my pim identifiers with valid attributes
     */
    public function iMapMyProductIdentifiersWithValidAttributes()
    {
        $isUpdated = $this->manageMapping->updateIdentifierMapping([
            'brand' => 'brand',
            'mpn' => 'mpn',
            'upc' => 'ean',
            'asin' => 'asin',
        ]);

        Assert::assertTrue($isUpdated);
    }

    /**
     * @Then the identifiers mapping is set
     */
    public function theIdentifiersMappingIsSet()
    {
        $identifiers = $this->identifiersMappingRepository->findAll()->getIdentifiers();

        Assert::assertEquals([
            'brand' => 'brand',
            'mpn' => 'mpn',
            'upc' => 'ean',
            'asin' => 'asin',
        ], $identifiers);
    }

    /**
     * @When I map pim.ai identifiers to my pim identifiers with invalid attributes
     */
    public function iMapMyProductIdentifiersWithInvalidAttributes()
    {
        $isUpdated = $this->manageMapping->updateIdentifierMapping([
            'brand' => 'burger',
        ]);

        Assert::assertFalse($isUpdated);
    }

    /**
     * @Then the identifiers mapping is not set
     */
    public function theIdentifiersMappingIsNotSet()
    {
        $identifiers = $this->identifiersMappingRepository->findAll()->getIdentifiers();

        Assert::assertEquals([], $identifiers);
    }
}
