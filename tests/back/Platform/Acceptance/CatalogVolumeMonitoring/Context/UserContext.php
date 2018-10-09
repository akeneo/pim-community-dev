<?php

declare(strict_types=1);

namespace AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Context;

use AkeneoTest\Platform\Acceptance\CatalogVolumeMonitoring\Persistence\Query\InMemory\InMemoryCountQuery;
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

final class UserContext implements Context
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
     * @Given a catalog with :numberOfUsers users
     *
     * @param int $numberOfUsers
     */
    public function aCatalogWithUsers(int $numberOfUsers): void
    {
        $this->inMemoryQuery->setVolume($numberOfUsers);
    }

    /**
     * @Given the limit of the number of users is set to :limit
     *
     * @param int $limit
     */
    public function theLimitOfTheNumberOfUsersIsSetTo(int $limit): void
    {
        $this->inMemoryQuery->setLimit($limit);
    }

    /**
     * @Then the report returns that the number of users is :numberOfUsers
     *
     * @param int $numberOfUsers
     */
    public function theReportReturnsThatTheNumberOfUsersIs(int $numberOfUsers): void
    {
        $volumes = $this->reportContext->getVolumes();

        Assert::eq($numberOfUsers, $volumes['count_users']['value']);
    }
}
