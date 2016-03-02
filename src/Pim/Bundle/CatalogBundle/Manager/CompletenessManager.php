<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Akeneo\Component\Console\CommandLauncher;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Doctrine\CompletenessGeneratorInterface;
use Pim\Bundle\CatalogBundle\Entity\Locale;
use Pim\Bundle\CatalogBundle\Repository\FamilyRepositoryInterface;
use Pim\Component\Catalog\Completeness\Checker\ProductValueCompleteCheckerInterface;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\AttributeRequirementInterface;
use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Model\FamilyInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
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

    /** @var ProductValueCompleteCheckerInterface */
    protected $valueCompleteChecker;

    /** @var CommandLauncher */
    protected $commandLauncher;

    /** @var string */
    protected $class;

    /**
     * @param FamilyRepositoryInterface            $familyRepository
     * @param ChannelRepositoryInterface           $channelRepository
     * @param LocaleRepositoryInterface            $localeRepository
     * @param CompletenessGeneratorInterface       $generator
     * @param ProductValueCompleteCheckerInterface $valueCompleteChecker
     * @param CommandLauncher                      $commandLauncher
     * @param string                               $class
     */
    public function __construct(
        FamilyRepositoryInterface $familyRepository,
        ChannelRepositoryInterface $channelRepository,
        LocaleRepositoryInterface $localeRepository,
        CompletenessGeneratorInterface $generator,
        ProductValueCompleteCheckerInterface $valueCompleteChecker,
        CommandLauncher $commandLauncher,
        $class
    ) {
        $this->familyRepository     = $familyRepository;
        $this->channelRepository    = $channelRepository;
        $this->localeRepository     = $localeRepository;
        $this->generator            = $generator;
        $this->valueCompleteChecker = $valueCompleteChecker;
        $this->commandLauncher      = $commandLauncher;
        $this->class                = $class;
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
            $this->generator->schedule($product);
        }
    }

    /**
     * Schedule recalculation of completenesses for all product
     * of a family
     * It could be long so it's launched as a backend task
     *
     * @param FamilyInterface $family
     */
    public function scheduleForFamily(FamilyInterface $family)
    {
        if ($family->getId()) {
            $cmd = sprintf('pim:completeness:schedule-family %s', $family->getCode());
            $logfile = $this->commandLauncher->buildLogfilePath('completeness.log');
            $this->commandLauncher->executeBackground($cmd, $logfile);
        }
    }

    /**
     * Returns an array containing all completeness info and missing attributes for a product
     *
     * @param ProductInterface                                    $product
     * @param \Pim\Bundle\CatalogBundle\Entity\ChannelInterface[] $channels
     * @param \Pim\Bundle\CatalogBundle\Entity\LocaleInterface[]  $locales
     * @param string                                              $localeCode
     *
     * @return array
     */
    public function getProductCompleteness(ProductInterface $product, array $channels, array $locales, $localeCode)
    {
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
        $localeCodes  = $getCodes($locales);

        $channelTemplate = [
            'channels' => array_fill_keys($channelCodes, ['completeness' => null, 'missing' => []]),
            'stats'    => [
                'total'    => 0,
                'complete' => 0
            ]
        ];

        $completenesses = array_fill_keys($localeCodes, $channelTemplate);

        if (!$family) {
            return $completenesses;
        }

        $allCompletenesses = $product->getCompletenesses();
        foreach ($allCompletenesses as $completeness) {
            $locale  = $completeness->getLocale();
            $channel = $completeness->getChannel();

            $compLocaleCode = $locale->getCode();
            if (isset($completenesses[$compLocaleCode])) {
                $completenesses[$compLocaleCode]['channels'][$channel->getCode()]['completeness'] = $completeness;
                $completenesses[$compLocaleCode]['stats']['total']++;

                if (0 === $completeness->getMissingCount()) {
                    $completenesses[$compLocaleCode]['stats']['complete']++;
                }
            }
        }

        $requirements = $this->familyRepository
            ->getFullRequirementsQB($family, $localeCode)
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
     * @param array                         &$completenesses
     * @param AttributeRequirementInterface $requirement
     * @param ArrayCollection               $productValues
     * @param LocaleInterface[]             $locales
     */
    protected function addRequirementToCompleteness(
        array &$completenesses,
        AttributeRequirementInterface $requirement,
        ArrayCollection $productValues,
        array $locales
    ) {
        $attribute = $requirement->getAttribute();
        $channel   = $requirement->getChannel();
        foreach ($locales as $locale) {
            $localeCode   = $locale->getCode();
            $valueCode    = $this->getValueCode($attribute, $localeCode, $channel->getCode());
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
