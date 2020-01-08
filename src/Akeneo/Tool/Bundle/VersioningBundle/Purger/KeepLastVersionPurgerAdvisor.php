<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\Purger;

use Akeneo\Tool\Bundle\VersioningBundle\Doctrine\Query\SqlGetAllButLastVersionIdsByIdsQuery;

/**
 * Prevents last version of an entity from being purged
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class KeepLastVersionPurgerAdvisor implements VersionPurgerAdvisorInterface
{
    /** @var SqlGetAllButLastVersionIdsByIdsQuery */
    private $getAllButLastVersionsByIdsQuery;

    public function __construct(SqlGetAllButLastVersionIdsByIdsQuery $getLatestVersionsByIdsQuery)
    {
        $this->getAllButLastVersionsByIdsQuery = $getLatestVersionsByIdsQuery;
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
        $allButLastVersionsIds = $this->getAllButLastVersionsByIdsQuery->execute($versionList->getVersionIds());

        return $versionList->keep($allButLastVersionsIds);
    }
}
