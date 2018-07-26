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

use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\PublishedProductRepositoryInterface;
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
    /** @var PublishedProductRepositoryInterface */
    protected $publishedProductRepository;

    /** @var string */
    protected $productResourceName;

    /**
     * @param PublishedProductRepositoryInterface $publishedProductRepository
     * @param string                              $productResourceName
     */
    public function __construct(PublishedProductRepositoryInterface $publishedProductRepository, $productResourceName)
    {
        $this->publishedProductRepository = $publishedProductRepository;
        $this->productResourceName = $productResourceName;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(VersionInterface $version)
    {
        return $this->productResourceName === $version->getResourceName();
    }

    /**
     * Prevents published versions of a product from being purged
     *
     * @param VersionInterface $version
     * @param array            $options
     *
     * @return bool
     */
    public function isPurgeable(VersionInterface $version, array $options = [])
    {
        return $version->getId() !== $this->publishedProductRepository->getPublishedVersionIdByOriginalProductId(
            $version->getResourceId()
        );
    }
}
