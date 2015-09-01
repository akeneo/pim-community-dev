<?php

namespace Pim\Bundle\AnalyticsBundle\DataCollector;

use Akeneo\Component\Analytics\DataCollectorInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ChannelRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\FamilyRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\LocaleRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductRepositoryInterface;
use Pim\Bundle\UserBundle\Entity\Repository\UserRepositoryInterface;

/**
 * Class DBDataCollector
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

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var FamilyRepositoryInterface */
    protected $familyRepository;

    /** @var UserRepositoryInterface */
    protected $userRepository;

    /**
     * @param ChannelRepositoryInterface   $channelRepository
     * @param ProductRepositoryInterface   $productRepository
     * @param AttributeRepositoryInterface $attributeRepository
     * @param LocaleRepositoryInterface    $localeRepository
     * @param FamilyRepositoryInterface    $familyRepository
     * @param UserRepositoryInterface      $userRepository
     */
    public function __construct(
        ChannelRepositoryInterface $channelRepository,
        ProductRepositoryInterface $productRepository,
        AttributeRepositoryInterface $attributeRepository,
        LocaleRepositoryInterface $localeRepository,
        FamilyRepositoryInterface $familyRepository,
        UserRepositoryInterface $userRepository
    ) {
        $this->channelRepository   = $channelRepository;
        $this->productRepository   = $productRepository;
        $this->attributeRepository = $attributeRepository;
        $this->localeRepository    = $localeRepository;
        $this->familyRepository    = $familyRepository;
        $this->userRepository      = $userRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function collect()
    {
        return [
            'nb_channels'   => $this->channelRepository->countAll(),
            'nb_products'   => $this->productRepository->countAll(),
            'nb_attributes' => $this->attributeRepository->countAll(),
            'nb_locales'    => $this->localeRepository->countAllActivated(),
            'nb_families'   => $this->familyRepository->countAll(),
            'nb_users'      => $this->userRepository->countAll(),
        ];
    }
}
