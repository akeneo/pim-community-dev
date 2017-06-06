<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Bundle\CatalogBundle\Filter\CollectionFilterInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Catalog\Localization\Localizer\AttributeConverterInterface;
use Pim\Component\Catalog\Localization\Localizer\LocalizerRegistryInterface;
use Pim\Component\Catalog\Repository\AttributeRepositoryInterface;
use Pim\Component\Enrich\Converter\ConverterInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Edit common attributes of given products
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EditCommonAttributes extends AbstractMassEditOperation
{
    /** @var string */
    protected $values;

    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /** @var UserContext */
    protected $userContext;

    /** @var ObjectUpdaterInterface */
    protected $productUpdater;

    /** @var ValidatorInterface */
    protected $productValidator;

    /** @var NormalizerInterface */
    protected $internalNormalizer;

    /** @var AttributeConverterInterface */
    protected $localizedConverter;

    /** @var LocalizerRegistryInterface */
    protected $localizerRegistry;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var CollectionFilterInterface */
    protected $productValuesFilter;

    /** @var ConverterInterface */
    protected $productValueConverter;

    /** @var string */
    protected $attributeLocale;

    /** @var string */
    protected $attributeChannel;

    /** @var string */
    protected $errors;

    /**
     * @param ProductBuilderInterface      $productBuilder
     * @param UserContext                  $userContext
     * @param AttributeRepositoryInterface $attributeRepository
     * @param ObjectUpdaterInterface       $productUpdater
     * @param ValidatorInterface           $productValidator
     * @param NormalizerInterface          $internalNormalizer
     * @param AttributeConverterInterface  $localizedConverter
     * @param LocalizerRegistryInterface   $localizerRegistry
     * @param CollectionFilterInterface    $productValuesFilter
     * @param ConverterInterface           $productValueConverter
     * @param string                       $tmpStorageDir
     * @param string                       $jobInstanceCode
     */
    public function __construct(
        ProductBuilderInterface $productBuilder,
        UserContext $userContext,
        AttributeRepositoryInterface $attributeRepository,
        ObjectUpdaterInterface $productUpdater,
        ValidatorInterface $productValidator,
        NormalizerInterface $internalNormalizer,
        AttributeConverterInterface $localizedConverter,
        LocalizerRegistryInterface $localizerRegistry,
        CollectionFilterInterface $productValuesFilter,
        ConverterInterface $productValueConverter,
        $jobInstanceCode
    ) {
        parent::__construct($jobInstanceCode);

        $this->productBuilder = $productBuilder;
        $this->userContext = $userContext;
        $this->attributeRepository = $attributeRepository;
        $this->productUpdater = $productUpdater;
        $this->productValidator = $productValidator;
        $this->internalNormalizer = $internalNormalizer;
        $this->localizedConverter = $localizedConverter;
        $this->localizerRegistry = $localizerRegistry;
        $this->productValuesFilter = $productValuesFilter;
        $this->productValueConverter = $productValueConverter;

        $this->values = '';
    }

    /**
     * @param string $values
     *
     * @return string
     */
    public function setValues($values)
    {
        $this->values = $values;

        return $this;
    }

    /**
     * @return string
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return 'pim_enrich_mass_edit_common_attributes';
    }

    /**
     * {@inheritdoc}
     */
    public function getFormOptions()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $this->values = '';
    }

    /**
     * {@inheritdoc}
     */
    public function finalize()
    {
        $data = json_decode($this->values, true);

        $data = $this->productValueConverter->convert($data);
        $data = $this->filterScopableAndLocalizableData(
            $data,
            $this->getAttributeLocale(),
            $this->getAttributeChannel()
        );
        $data = $this->productValuesFilter->filterCollection($data, 'pim.internal_api.product_values_data.edit');
        $data = $this->delocalizeData($data, $this->userContext->getUiLocale()->getCode());

        $this->values = json_encode($data, JSON_HEX_APOS);
    }

    /**
     * {@inheritdoc}
     */
    public function getOperationAlias()
    {
        return 'edit-common-attributes';
    }

    /**
     * {@inheritdoc}
     */
    public function getActions()
    {
        $actions = [
            'normalized_values' => $this->getValues(),
            'ui_locale'         => $this->userContext->getUiLocale()->getCode(),
            'attribute_locale'  => $this->getAttributeLocale(),
            'attribute_channel' => $this->getAttributeChannel()
        ];

        return $actions;
    }

    /**
     * Add constraint on product values integrity.
     *
     * It registers constraint assertion that "hasValidValues" must return true on
     * mass edit form submission.
     *
     * @param ClassMetadata $metadata
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addGetterConstraint('validValues', new IsTrue([
            'message' => 'mass_edit.edit_common_attributes.invalid_values'
        ]));
    }

    /**
     * Apply current values to a fake product and test its integrity with the product validator.
     * If violations are raised, values are not valid.
     *
     * Errors are stored in json format to be useable by the Product Edit Form.
     *
     * @return bool
     */
    public function hasValidValues()
    {
        $data = json_decode($this->values, true);

        $locale = $this->userContext->getUiLocale()->getCode();
        $data = $this->productValueConverter->convert($data);
        $data = $this->localizedConverter->convertToDefaultFormats($data, ['locale' => $locale]);

        $product = $this->productBuilder->createProduct('FAKE_SKU_FOR_MASS_EDIT_VALIDATION_' . microtime());
        $this->productUpdater->update($product, ['values' => $data]);
        $violations = $this->productValidator->validate($product);

        $violations = $this->removeIdentifierViolations($violations);

        $violations->addAll($this->localizedConverter->getViolations());

        $errors = ['values' => $this->internalNormalizer->normalize(
            $violations,
            'internal_api',
            ['product' => $product]
        )];
        $this->errors = json_encode($errors);

        return 0 === $violations->count();
    }

    /**
     * @return string
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @return string
     */
    public function getAttributeLocale()
    {
        return $this->attributeLocale;
    }

    /**
     * @param string $attributeLocale
     */
    public function setAttributeLocale($attributeLocale)
    {
        $this->attributeLocale = $attributeLocale;
    }

    /**
     * @return string
     */
    public function getAttributeChannel()
    {
        return $this->attributeChannel;
    }

    /**
     * @param string $attributeChannel
     */
    public function setAttributeChannel($attributeChannel)
    {
        $this->attributeChannel = $attributeChannel;
    }

    /**
     * Filter scopable and localisable data to keep only the attributes values that
     * conform to the locale and scope that have been chosen in the grid.
     *
     * @param array  $data
     * @param string $localeCode
     * @param string $channelCode
     *
     * @return array
     */
    protected function filterScopableAndLocalizableData(array $data, $localeCode, $channelCode)
    {
        foreach ($data as $code => $values) {
            $values = array_filter($values, function ($value) use ($localeCode, $channelCode) {
                return
                    ($localeCode === $value['locale'] || null === $value['locale']) &&
                    ($channelCode === $value['scope'] || null === $value['scope']) ;
            });
            $data[$code] = $values;
        }

        return $data;
    }

    /**
     * Change users' data (example: "12,45") into storable data (example: "12.45").
     *
     * @param array  $data
     * @param string $uiLocaleCode
     *
     * @return array
     */
    protected function delocalizeData(array $data, $uiLocaleCode)
    {
        foreach ($data as $code => $values) {
            $attribute = $this->attributeRepository->findOneByIdentifier($code);
            $localizer = $this->localizerRegistry->getLocalizer($attribute->getType());

            if (null !== $localizer) {
                $values = array_map(function ($value) use ($localizer, $uiLocaleCode) {
                    $value['data'] = $localizer->delocalize($value['data'], ['locale' => $uiLocaleCode]);

                    return $value;
                }, $values);

                $data[$code] = $values;
            }
        }

        return $data;
    }

    /**
     * Remove all violations related to identifier
     *
     * @param ConstraintViolationListInterface $violations
     *
     * @return ConstraintViolationListInterface
     */
    protected function removeIdentifierViolations(ConstraintViolationListInterface $violations)
    {
        $identifierPath = sprintf('values[%s-', $this->attributeRepository->getIdentifierCode());
        foreach ($violations as $offset => $violation) {
            if (0 === strpos($violation->getPropertyPath(), $identifierPath)) {
                $violations->remove($offset);
            }
        }

        return $violations;
    }
}
