<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\Purger;

use Akeneo\Tool\Bundle\VersioningBundle\Doctrine\Query\SqlGetLatestVersionsByIdsQuery;

/**
 * Prevents last version of an entity from being purged
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SkipLastVersionPurgerAdvisor implements VersionPurgerAdvisorInterface
{
    /** @var SqlGetLatestVersionsByIdsQuery */
    private $getLatestVersionsByIdsQuery;

    public function __construct(SqlGetLatestVersionsByIdsQuery $getLatestVersionsByIdsQuery)
    {
        $this->getLatestVersionsByIdsQuery = $getLatestVersionsByIdsQuery;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(PurgeableVersionList $versionList)
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function isPurgeable(PurgeableVersionList $versionList): PurgeableVersionList
    {
        $latestVersionsIds = $this->getLatestVersionsByIdsQuery->execute($versionList->getVersionIds());

        return $versionList->remove($latestVersionsIds);
    }
}
