<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\Purger;

use Akeneo\Tool\Bundle\VersioningBundle\Doctrine\Query\SqlGetFirstVersionIdsByIdsQuery;

/**
 * Prevents first version of an entity from being purged
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SkipFirstVersionPurgerAdvisor implements VersionPurgerAdvisorInterface
{
    /** @var SqlGetFirstVersionIdsByIdsQuery */
    private $getFirstVersionsByIdsQuery;

    public function __construct(SqlGetFirstVersionIdsByIdsQuery $getFirstVersionsByIdsQuery)
    {
        $this->getFirstVersionsByIdsQuery = $getFirstVersionsByIdsQuery;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(PurgeableVersionList $version)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isPurgeable(PurgeableVersionList $versionList): PurgeableVersionList
    {
        $firstVersionIds = $this->getFirstVersionsByIdsQuery->execute($versionList->getVersionIds());

        return $versionList->remove($firstVersionIds);
    }
}
