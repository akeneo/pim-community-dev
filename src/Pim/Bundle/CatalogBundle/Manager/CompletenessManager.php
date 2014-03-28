<?php

namespace Pim\Bundle\CatalogBundle\Manager;

use Symfony\Component\Validator\ValidatorInterface;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Pim\Bundle\CatalogBundle\Doctrine\CompletenessGeneratorInterface;
use Pim\Bundle\CatalogBundle\Entity\AttributeRequirement;
use Pim\Bundle\CatalogBundle\Entity\Channel;
use Pim\Bundle\CatalogBundle\Entity\Family;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Manager\ChannelManager;
use Pim\Bundle\CatalogBundle\Validator\Constraints\ProductValueComplete;

/**
 * Manages completeness
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CompletenessManager
{
    /**
     * @var RegistryInterface
     */
    protected $doctrine;

    /**
     * @var CompletenessGeneratorInterface
     */
    protected $generator;

    /**
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var ChannelManager
     */
    protected $channelManager;

    /**
     * Constructor
     *
     * @param RegistryInterface              $doctrine
     * @param CompletenessGeneratorInterface $generator
     * @param ValidatorInterface             $validator
     * @param string                         $class
     */
    public function __construct(
        RegistryInterface $doctrine,
        CompletenessGeneratorInterface $generator,
        ValidatorInterface $validator,
        ChannelManager $channelManager,
        $class

    ) {
        $this->doctrine       = $doctrine;
        $this->generator      = $generator;
        $this->validator      = $validator;
        $this->channelManager = $channelManager;
        $this->class          = $class;
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
     * @param Channel $channel
     */
    public function generateMissingForChannel(Channel $channel)
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
     *
     * @param Family $family
     */
    public function scheduleForFamily(Family $family)
    {
        if ($family->getId()) {
            $this->generator->scheduleForFamily($family);
        }
    }

    /**
     * Returns an array containing all completeness info and missing attributes for a product
     *
     * @param ProductInterface $product
     * @param array            $channels
     * @param array            $locales
     * @param string           $localeCode
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
        $channelTemplate = array_fill_keys($getCodes($channels), array('completeness' => null, 'missing' => array()));
        $localeCodes = $getCodes($locales);
        $completenesses = array_fill_keys($localeCodes, $channelTemplate);

        if (!$family) {
            return $completenesses;
        }

        $allCompletenesses = $product->getCompletenesses();
        foreach ($allCompletenesses as $completeness) {
            $locale = $completeness->getLocale();
            $channel = $completeness->getChannel();
            $completenesses[$locale->getCode()][$channel->getCode()]['completeness'] = $completeness;
        }
        $requirements = $this->doctrine
            ->getRepository(get_class($family))
            ->getFullRequirementsQB($family, $localeCode)
            ->getQuery()
            ->getResult();

        $productValues = $product->getValues();
        foreach ($requirements as $requirement) {
            if ($requirement->isRequired()) {
                $this->addRequirementToCompleteness($completenesses, $requirement, $productValues, $localeCodes);
            }
        }

        return $completenesses;
    }

    /**
     * Adds a requirement to the completenesses
     *
     * @param array                &$completenesses
     * @param AttributeRequirement $requirement
     * @param ArrayCollection      $productValues
     * @param array                $localeCodes
     */
    protected function addRequirementToCompleteness(
        array &$completenesses,
        AttributeRequirement $requirement,
        ArrayCollection $productValues,
        array $localeCodes
    ) {
        $attribute = $requirement->getAttribute();
        $channel = $requirement->getChannel();
        foreach ($localeCodes as $localeCode) {
            $constraint = new ProductValueComplete(array('channel' => $channel));
            $valueCode = $this->getValueCode($attribute, $localeCode, $channel->getCode());
            $missing = false;
            if (!isset($productValues[$valueCode])) {
                $missing = true;
            } elseif ($this->validator->validateValue($productValues[$valueCode], $constraint)->count()) {
                $missing = true;
            }
            if ($missing) {
                $completenesses[$localeCode][$channel->getCode()]['missing'][] = $attribute;
            }
        }
    }

    /**
     * @param AbstractAttribute $attribute
     * @param string            $locale
     * @param string            $scope
     *
     * @return string
     */
    protected function getValueCode(AbstractAttribute $attribute, $locale, $scope)
    {
        $valueCode = $attribute->getCode();
        if ($attribute->isLocalizable()) {
            $valueCode .= '_' .$locale;
        }
        if ($attribute->isScopable()) {
            $valueCode .= '_' . $scope;
        }

        return $valueCode;
    }

    /**
     * Count the number of products by channels
     *
     * @return array
     */
    public function getProductsCountPerChannels()
    {
        $channels = $this->channelManager->getFullChannels();

        return $this->generator->getProductsCountPerChannels($channels);
    }

    /**
     * Count the number of complete products by channels
     *
     * @return array
     */
    public function getCompleteProductsCountPerChannels()
    {
        $channels = $this->channelManager->getFullChannels();

        return $this->generator->getCompleteProductsCountPerChannels($channels);
    }
}
