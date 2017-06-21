<?php

namespace Pim\Component\Catalog\Manager;

use Pim\Component\Catalog\Completeness\Checker\ValueCompleteCheckerInterface;
use Pim\Component\Catalog\Completeness\CompletenessGeneratorInterface;
use Pim\Component\Catalog\Completeness\CompletenessRemoverInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeRequirementInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ValueCollectionInterface;
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
     * Insert missing completenesses for a given channel
     *
     * @param ChannelInterface $channel
     */
    public function generateMissingForChannel(ChannelInterface $channel)
    {
        $this->generator->generateMissingForChannel($channel);
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

    /**
     * Returns an array containing all completeness info and missing attributes for a product
     *
     * @param ProductInterface   $product
     * @param ChannelInterface[] $channels
     * @param LocaleInterface[]  $locales
     * @param string             $localeCode
     *
     * @return array
     * [
     *      [
     *          'channels' => [
     *              'completeness' => [
     *                  'channel'  => string,
     *                  'locale'   => string,
     *                  'missing'  => int,
     *                  'ratio'    => int,
     *                  'required' => int
     *              ],
     *              'missing' => [string, ...],
     *          'stats'    => [
     *               'total'    => int,
     *               'complete' => int,
     *          ],
     *          'locale'   => string
     *      ],
     * ]
     */
    public function getProductCompleteness(
        ProductInterface $product,
        array $channels,
        array $locales,
        $localeCode = null
    ) {
        $family = $product->getFamily();

        $getCodes = function ($entities) {
            return array_map(
                function ($entity) {
                    return $entity->getCode();
                },
                $entities
            );
        };

        $channelCodes = $getCodes($channels);
        $localeCodes = $getCodes($locales);

        sort($channelCodes);
        $channelTemplate = [
            'channels' => array_fill_keys($channelCodes, ['completeness' => null, 'missing' => []]),
            'stats'    => [
                'total'    => 0,
                'complete' => 0
            ],
            'locale' => ''
        ];

        $completenesses = array_fill_keys($localeCodes, $channelTemplate);

        if ($family) {
            $completenesses = $this->fillCompletenessesTemplate(
                $completenesses,
                $product,
                $locales,
                $localeCode
            );
        }

        foreach ($completenesses as $localeCode => $completeness) {
            $completenesses[$localeCode]['channels'] = array_values($completeness['channels']);
        }
        $completenesses = array_values($completenesses);

        return $completenesses;
    }

    /**
     * Returns completenesses filled
     *
     * @param array            $completenesses
     * @param ProductInterface $product
     * @param array            $locales
     * @param string           $localeCode
     *
     * @return array
     */
    protected function fillCompletenessesTemplate(
        array $completenesses,
        ProductInterface $product,
        array $locales,
        $localeCode
    ) {
        $allCompletenesses = $product->getCompletenesses();
        foreach ($allCompletenesses as $completeness) {
            $locale = $completeness->getLocale();
            $channel = $completeness->getChannel();

            $compLocaleCode = $locale->getCode();
            if (isset($completenesses[$compLocaleCode])) {
                $completenesses[$compLocaleCode]['channels'][$channel->getCode()]['completeness'] = $completeness;
                $completenesses[$compLocaleCode]['locale'] = $compLocaleCode;
                $completenesses[$compLocaleCode]['stats']['total']++;

                if (0 === $completeness->getMissingCount()) {
                    $completenesses[$compLocaleCode]['stats']['complete']++;
                }
            }
        }

        $requirements = $this->familyRepository
            ->getFullRequirementsQB($product->getFamily(), $localeCode)
            ->getQuery()
            ->getResult();

        $productValues = $product->getValues();
        foreach ($requirements as $requirement) {
            if ($requirement->isRequired()) {
                $this->addRequirementToCompleteness($completenesses, $requirement, $productValues, $locales);
            }
        }

        return $completenesses;
    }

    /**
     * Adds a requirement to the completenesses
     *
     * @param array                         $completenesses
     * @param AttributeRequirementInterface $requirement
     * @param ValueCollectionInterface      $productValues
     * @param LocaleInterface[]             $locales
     */
    protected function addRequirementToCompleteness(
        array &$completenesses,
        AttributeRequirementInterface $requirement,
        ValueCollectionInterface $productValues,
        array $locales
    ) {
        $attribute = $requirement->getAttribute();
        $channel = $requirement->getChannel();
        foreach ($locales as $locale) {
            $localeCode = $locale->getCode();
            $valueCode = $this->getValueCode($attribute, $localeCode, $channel->getCode());
            $productValue = isset($productValues[$valueCode]) ? $productValues[$valueCode] : null;
            $isIncomplete = (null !== $productValue) &&
                $this->valueCompleteChecker->supportsValue($productValue) &&
                !$this->valueCompleteChecker->isComplete($productValue, $channel, $locale);

            $shouldExistInLocale = !$attribute->isLocaleSpecific() || $attribute->hasLocaleSpecific($locale);
            if ((null === $productValue || $isIncomplete) && $shouldExistInLocale) {
                $completenesses[$localeCode]['channels'][$channel->getCode()]['missing'][] = $attribute;
            }
        }
    }

    /**
     * @param AttributeInterface $attribute
     * @param string             $locale
     * @param string             $scope
     *
     * @return string
     */
    protected function getValueCode(AttributeInterface $attribute, $locale, $scope)
    {
        $valueCode = $attribute->getCode();
        if ($attribute->isLocalizable()) {
            $valueCode .= '-' . $locale;
        }
        if ($attribute->isScopable()) {
            $valueCode .= '-' . $scope;
        }

        return $valueCode;
    }
}
