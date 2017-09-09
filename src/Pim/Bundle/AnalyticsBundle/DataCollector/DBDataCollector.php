<?php

namespace Pim\Bundle\AnalyticsBundle\DataCollector;

use Akeneo\Component\Analytics\DataCollectorInterface;
use Pim\Bundle\UserBundle\Repository\UserRepositoryInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;
use Pim\Component\Catalog\Repository\ProductRepositoryInterface;

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
    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var ProductRepositoryInterface */
    protected $productRepository;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var FamilyRepositoryInterface */
    protected $familyRepository;

    /** @var UserRepositoryInterface */
    protected $userRepository;

    /**
     * @param ChannelRepositoryInterface   $channelRepository
     * @param ProductRepositoryInterface   $productRepository
     * @param LocaleRepositoryInterface    $localeRepository
     * @param FamilyRepositoryInterface    $familyRepository
     * @param UserRepositoryInterface      $userRepository
     */
    public function __construct(
        ChannelRepositoryInterface $channelRepository,
        ProductRepositoryInterface $productRepository,
        LocaleRepositoryInterface $localeRepository,
        FamilyRepositoryInterface $familyRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->channelRepository = $channelRepository;
        $this->productRepository = $productRepository;
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
            'nb_channels'   => $this->channelRepository->countAll(),
            'nb_locales'    => $this->localeRepository->countAllActivated(),
            'nb_products'   => $this->productRepository->countAll(),
            'nb_families'   => $this->familyRepository->countAll(),
            'nb_users'      => $this->userRepository->countAll(),
        ];
    }
}
