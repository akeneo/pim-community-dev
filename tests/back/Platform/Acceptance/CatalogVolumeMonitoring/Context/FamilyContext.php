<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Context;

use AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Persistence\Query\InMemory\InMemoryCountQuery;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

final class FamilyContext implements Context
{
    /** @var ReportContext */
    private $reportContext;

    /** @var InMemoryCountQuery */
    private $inMemoryQuery;

    /**
     * @param ReportContext      $reportContext
     * @param InMemoryCountQuery $inMemoryQuery
     */
    public function __construct(ReportContext $reportContext, InMemoryCountQuery $inMemoryQuery)
    {
        $this->reportContext = $reportContext;
        $this->inMemoryQuery = $inMemoryQuery;
    }

    /**
     * @Given a catalog with :numberOfFamilies families
     *
     * @param int $numberOfFamilies
     */
    public function aCatalogWithFamilies(int $numberOfFamilies): void
    {
        $this->inMemoryQuery->setVolume($numberOfFamilies);
    }

    /**
     * @Then the report returns that the number of families is :numberOfFamilies
     *
     * @param int $numberOfFamilies
     */
    public function theReportReturnsThatTheNumberOfFamiliesIs(int $numberOfFamilies): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::eq($numberOfFamilies, $volumes['count_families']['value']);
    }
}
