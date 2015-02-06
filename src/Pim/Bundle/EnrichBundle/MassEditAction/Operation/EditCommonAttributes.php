<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilder;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Manager\ProductMassActionManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductUpdaterInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Edit common attributes of given products
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EditCommonAttributes extends ProductMassEditOperation
{
    /** @var ArrayCollection|ProductValueInterface[] */
    protected $values;

    /** @var ArrayCollection */
    protected $displayedAttributes;

    /** @var LocaleInterface */
    protected $locale;

    /** @var ProductBuilder */
    protected $productBuilder;

    /** @var ProductMassActionManager */
    protected $massActionManager;

    /** @var UserContext */
    protected $userContext;

    /** @var CatalogContext */
    protected $catalogContext;

    /** @var array */
    protected $commonAttributes;

    /** @var array */
    protected $warningMessages;

    /** @var ProductUpdaterInterface */
    protected $productUpdater;

    /** @var NormalizerInterface */
    protected $normalizer;

    /**
     * Constructor
     *
     * @param ProductBuilder           $productBuilder
     * @param ProductUpdaterInterface  $productUpdater
     * @param UserContext              $userContext
     * @param CatalogContext           $catalogContext
     * @param ProductMassActionManager $massActionManager
     * @param NormalizerInterface      $normalizer
     * @param BulkSaverInterface       $productSaver
     */
    public function __construct(
        ProductBuilder $productBuilder,
        ProductUpdaterInterface $productUpdater,
        UserContext $userContext,
        CatalogContext $catalogContext,
        ProductMassActionManager $massActionManager,
        NormalizerInterface $normalizer,
        BulkSaverInterface $productSaver
    ) {
        parent::__construct($productSaver);

        $this->productBuilder = $productBuilder;
        $this->productUpdater = $productUpdater;
        $this->userContext = $userContext;
        $this->catalogContext = $catalogContext;
        $this->massActionManager = $massActionManager;
        $this->displayedAttributes = new ArrayCollection();
        $this->values = new ArrayCollection();
        $this->normalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function affectsCompleteness()
    {
        return true;
    }

    /**
     * Set values
     *
     * @param Collection $values
     *
     * @return EditCommonAttributes
     */
    public function setValues(Collection $values)
    {
        $this->values = $values;

        return $this;
    }

    /**
     * Get values
     *
     * @return Collection
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Set locale
     *
     * @param LocaleInterface $locale
     *
     * @return EditCommonAttributes
     */
    public function setLocale(LocaleInterface $locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get locale
     *
     * @return LocaleInterface
     */
    public function getLocale()
    {
        if ($this->locale instanceof LocaleInterface) {
            return $this->locale;
        }

        return $this->userContext->getCurrentLocale();
    }

    /**
     * Set displayed attributes
     *
     * @param Collection $displayedAttributes
     *
     * @return EditCommonAttributes
     */
    public function setDisplayedAttributes(Collection $displayedAttributes)
    {
        $this->displayedAttributes = $displayedAttributes;

        return $this;
    }

    /**
     * Get displayed attributes
     *
     * @return Collection
     */
    public function getDisplayedAttributes()
    {
        return $this->displayedAttributes;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return 'pim_enrich_mass_edit_common_attributes';
    }

    /**
     * Get form options
     *
     * @return array
     */
    public function getFormOptions()
    {
        return array(
            'locales'           => $this->userContext->getUserLocales(),
            'common_attributes' => $this->getCommonAttributes(),
            'current_locale'    => $this->getLocale()->getCode()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $locale = $this->getLocale()->getCode();
        $this->catalogContext->setLocaleCode($locale);

        $this->warningMessages  = null;
        $this->commonAttributes = null;

        $commonAttributes = $this->getCommonAttributes();

        $this->values = new ArrayCollection();
        foreach ($commonAttributes as $attribute) {
            $this->addValues($attribute, $this->getLocale());
        }
    }

    /**
     * {@inheritdoc}
     */
    public function perform()
    {
        parent::perform();
    }

    /**
     * Initializes self::commonAtributes with values from the repository
     * Attribute is not available for mass editing if:
     *   - it is an identifier
     *   - it is unique
     *   - without value AND not link to family
     *   - is not common to every products
     *
     * @return array
     */
    public function getCommonAttributes()
    {
        if (null === $this->commonAttributes) {
            $locale   = $this->getLocale()->getCode();
            $products = $this->objects;

            $this->commonAttributes = $this->generateCommonAttributes($products, $locale);
        }

        return $this->commonAttributes;
    }

    /**
     * Generate common attributes
     * @param array  $products
     * @param string $locale
     *
     * @return AttributeInterface[]
     */
    protected function generateCommonAttributes(array $products, $locale)
    {
        $commonAttributes = $this->massActionManager->findCommonAttributes($products);

        foreach ($commonAttributes as $attribute) {
            $attribute->setLocale($locale);
            $attribute->getGroup()->setLocale($locale);
        }

        $commonAttributes = $this->massActionManager->filterLocaleSpecificAttributes(
            $commonAttributes,
            $locale
        );

        $commonAttributes = $this->massActionManager->filterAttributesComingFromVariant(
            $commonAttributes,
            $products
        );

        return $commonAttributes;
    }

    /**
     * Get warning messages
     *
     * @return string[]
     */
    public function getWarningMessages()
    {
        if (null === $this->warningMessages) {
            $this->warningMessages = $this->generateWarningMessages($this->objects);
        }

        return $this->warningMessages;
    }

    /**
     * Get warning messages to display during the mass edit action
     * @param ProductInterface[] $products
     *
     * @return string[]
     */
    protected function generateWarningMessages(array $products)
    {
        $messages = [];

        $variantAttributeCodes = $this->massActionManager->getCommonAttributeCodesInVariant($products);
        $rootMessageKey = 'pim_enrich.mass_edit_action.edit-common-attributes';
        if (count($variantAttributeCodes) > 0) {
            $messages[] = [
                'key'     => $rootMessageKey.'.truncated_by_variant_attribute.warning',
                'options' => ['%attributes%' => implode(', ', $variantAttributeCodes)]
            ];
        }

        if (count($this->getCommonAttributes()) < 1) {
            $messages[] = [
                'key' => $rootMessageKey.'.no_attribute.warning',
                'options' => []
            ];
        }

        return $messages;
    }

    /**
     * {@inheritdoc}
     */
    protected function doPerform(ProductInterface $product)
    {
        $this->setProductValues($product);
    }

    /**
     * Set product values with the one stored inside $this->values
     *
     * @param ProductInterface $product
     */
    protected function setProductValues(ProductInterface $product)
    {
        foreach ($this->values as $value) {
            $rawData = $this->normalizer->normalize($value->getData(), 'json', ['entity' => 'product']);
            // if the value is localizable, let's use the locale the user has chosen in the form
            $locale = null !== $value->getLocale() ? $this->getLocale()->getCode() : null;

            $this->productUpdater->setValue(
                [$product],
                $value->getAttribute()->getCode(),
                $rawData,
                $locale,
                $value->getScope()
            );
        }
    }

    /**
     * Add all the values required by the given attribute
     *
     * @param AttributeInterface $attribute
     * @param LocaleInterface    $locale
     */
    protected function addValues(AttributeInterface $attribute, $locale)
    {
        if ($attribute->isScopable()) {
            foreach ($locale->getChannels() as $channel) {
                $key = $attribute->getCode() . '_' . $channel->getCode();
                $value = $this->productBuilder->createProductValue(
                    $attribute,
                    $locale->getCode(),
                    $channel->getCode()
                );

                $this->productBuilder->addMissingPrices($value);
                $this->values[$key] = $value;
            }
        } else {
            $value = $this->productBuilder->createProductValue(
                $attribute,
                $locale->getCode()
            );

            $this->productBuilder->addMissingPrices($value);
            $this->values[$attribute->getCode()] = $value;
        }
    }
}
