<?php

namespace PimEnterprise\Bundle\VersioningBundle\Purger;

use Akeneo\Component\Versioning\Model\VersionInterface;
use Pim\Bundle\VersioningBundle\Purger\VersionPurgerAdvisorInterface;
use PimEnterprise\Component\Workflow\Repository\PublishedProductRepositoryInterface;

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
