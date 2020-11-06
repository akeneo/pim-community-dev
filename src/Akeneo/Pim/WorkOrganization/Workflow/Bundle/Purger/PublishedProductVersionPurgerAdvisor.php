<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2016 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Purger;

use Akeneo\Pim\WorkOrganization\Workflow\Bundle\Doctrine\ORM\Query\GetPublishedVersionIdsByVersionIdsQuery;
use Akeneo\Tool\Bundle\VersioningBundle\Purger\PurgeableVersionList;
use Akeneo\Tool\Bundle\VersioningBundle\Purger\VersionPurgerAdvisorInterface;
use Akeneo\Tool\Component\Versioning\Model\VersionInterface;

/**
 * Prevents published versions of a product from being purged
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PublishedProductVersionPurgerAdvisor implements VersionPurgerAdvisorInterface
{
    /** @var GetPublishedVersionIdsByVersionIdsQuery */
    protected $getPublishedVersionIdsByVersionIdsQuery;

    /** @var string */
    protected $productResourceName;

    public function __construct(GetPublishedVersionIdsByVersionIdsQuery $getPublishedVersionIdsByVersionIdsQuery, $productResourceName)
    {
        $this->getPublishedVersionIdsByVersionIdsQuery = $getPublishedVersionIdsByVersionIdsQuery;
        $this->productResourceName = $productResourceName;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(PurgeableVersionList $versionList): bool
    {
        return $this->productResourceName === $versionList->getResourceName();
    }

    /**
     * Prevents published versions of a product from being purged
     */
    public function isPurgeable(PurgeableVersionList $versionList): PurgeableVersionList
    {
        $publishedVersionIds = $this->getPublishedVersionIdsByVersionIdsQuery->execute($versionList->getVersionIds());

        return $versionList->remove($publishedVersionIds);
    }
}
