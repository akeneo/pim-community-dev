<?php

namespace Pim\Bundle\AnalyticsBundle\DataCollector;

use Akeneo\Tool\Component\Analytics\DataCollectorInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\CountableRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;

/**
 * Collects the structure of the PIM catalog:
 * - number of channels
 * - number of products
 * - number of attributes
 * - number of locales
 * - number of families
 * - number of users
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DBDataCollector implements DataCollectorInterface
{
    /** @var CountableRepositoryInterface */
    protected $channelRepository;

    /** @var CountableRepositoryInterface */
    protected $productRepository;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var CountableRepositoryInterface */
    protected $familyRepository;

    /** @var CountableRepositoryInterface */
    protected $userRepository;

    /** @var CountableRepositoryInterface */
    protected $productModelRepository;

    /** @var CountableRepositoryInterface */
    protected $variantProductRepository;

    /** @var CountableRepositoryInterface */
    protected $familyVariantRepository;

    /**
     * @param CountableRepositoryInterface            $channelRepository
     * @param CountableRepositoryInterface            $productRepository
     * @param LocaleRepositoryInterface             $localeRepository
     * @param CountableRepositoryInterface             $familyRepository
     * @param CountableRepositoryInterface               $userRepository
     * @param CountableRepositoryInterface       $productModelRepository
     * @param CountableRepositoryInterface     $variantProductRepository
     * @param CountableRepositoryInterface      $familyVariantRepository
     */
    public function __construct(
        CountableRepositoryInterface $channelRepository,
        CountableRepositoryInterface $productRepository,
        LocaleRepositoryInterface $localeRepository,
        CountableRepositoryInterface $familyRepository,
        CountableRepositoryInterface $userRepository,
        CountableRepositoryInterface $productModelRepository,
        CountableRepositoryInterface $variantProductRepository,
        CountableRepositoryInterface $familyVariantRepository
    ) {
        $this->channelRepository = $channelRepository;
        $this->productRepository = $productRepository;
        $this->productModelRepository = $productModelRepository;
        $this->variantProductRepository = $variantProductRepository;
        $this->familyVariantRepository = $familyVariantRepository;
        $this->localeRepository = $localeRepository;
        $this->familyRepository = $familyRepository;
        $this->userRepository = $userRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function collect()
    {
        return [
            'nb_channels'           => $this->channelRepository->countAll(),
            'nb_locales'            => $this->localeRepository->countAllActivated(),
            'nb_products'           => $this->productRepository->countAll(),
            'nb_product_models'     => $this->productModelRepository->countAll(),
            'nb_variant_products'   => $this->variantProductRepository->countAll(),
            'nb_family_variants'    => $this->familyVariantRepository->countAll(),
            'nb_families'           => $this->familyRepository->countAll(),
            'nb_users'              => $this->userRepository->countAll(),
        ];
    }
}
