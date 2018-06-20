<?php

namespace Pim\Component\Catalog\Manager;

use Pim\Component\Catalog\Completeness\Checker\ValueCompleteCheckerInterface;
use Pim\Component\Catalog\Completeness\CompletenessGeneratorInterface;
use Pim\Component\Catalog\Completeness\CompletenessRemoverInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Pim\Component\Catalog\Repository\FamilyRepositoryInterface;
use Pim\Component\Catalog\Repository\LocaleRepositoryInterface;

/**
 * Manages completeness
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessManager
{
    /** @var FamilyRepositoryInterface */
    protected $familyRepository;

    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /** @var LocaleRepositoryInterface */
    protected $localeRepository;

    /** @var CompletenessGeneratorInterface */
    protected $generator;

    /** @var CompletenessRemoverInterface */
    protected $remover;

    /** @var ValueCompleteCheckerInterface */
    protected $valueCompleteChecker;

    /**
     * @param FamilyRepositoryInterface      $familyRepository
     * @param ChannelRepositoryInterface     $channelRepository
     * @param LocaleRepositoryInterface      $localeRepository
     * @param CompletenessGeneratorInterface $generator
     * @param CompletenessRemoverInterface   $remover
     * @param ValueCompleteCheckerInterface  $valueCompleteChecker
     */
    public function __construct(
        FamilyRepositoryInterface $familyRepository,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        CompletenessGeneratorInterface $generator,
        CompletenessRemoverInterface $remover,
        ValueCompleteCheckerInterface $valueCompleteChecker
    ) {
        $this->familyRepository = $familyRepository;
        $this->channelRepository = $channelRepository;
        $this->localeRepository = $localeRepository;
        $this->generator = $generator;
        $this->remover = $remover;
        $this->valueCompleteChecker = $valueCompleteChecker;
    }

    /**
     * Insert missing completenesses for a given product
     *
     * @param ProductInterface $product
     */
    public function generateMissingForProduct(ProductInterface $product)
    {
        $this->generator->generateMissingForProduct($product);
    }

    /**
     * @param ChannelInterface $channel
     * @param array            $filters
     *
     * @deprecated to remove as completeness is generated on the fly when a product is saved since 2.x
     */
    public function generateMissingForProducts(ChannelInterface $channel, array $filters)
    {
    }

    /**
     * Insert missing completenesses for a given channel
     *
     * @param ChannelInterface $channel
     *
     * @deprecated to remove as completeness is generated on the fly when a product is saved since 2.x
     */
    public function generateMissingForChannel(ChannelInterface $channel)
    {
    }

    /**
     * Insert missing completenesses
     */
    public function generateMissing()
    {
        $this->generator->generateMissing();
    }

    /**
     * Schedule recalculation of completenesses for a product
     *
     * @param ProductInterface $product
     */
    public function schedule(ProductInterface $product)
    {
        if ($product->getId()) {
            $this->remover->removeForProduct($product);
        }
    }

    /**
     * Schedule recalculation of completenesses for all product
     * of a family
     *
     * @param FamilyInterface $family
     */
    public function scheduleForFamily(FamilyInterface $family)
    {
        if ($family->getId()) {
            $this->remover->removeForFamily($family);
        }
    }
}
