<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Catalog\Builder\ProductBuilderInterface;
use Pim\Component\Localization\Localizer\LocalizedAttributeConverterInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Constraints\IsTrue;
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

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var ObjectUpdaterInterface */
    protected $productUpdater;

    /** @var ValidatorInterface */
    protected $productValidator;

    /** @var NormalizerInterface */
    protected $internalNormalizer;

    /** @var LocalizedAttributeConverterInterface */
    protected $localizedConverter;

    /** @var string */
    protected $tmpStorageDir;

    /** @var string */
    protected $attributeLocale;

    /** @var string */
    protected $errors;

    /**
     * @param ProductBuilderInterface              $productBuilder
     * @param UserContext                          $userContext
     * @param NormalizerInterface                  $normalizer
     * @param ObjectUpdaterInterface               $productUpdater
     * @param ValidatorInterface                   $productValidator
     * @param NormalizerInterface                  $internalNormalizer
     * @param LocalizedAttributeConverterInterface $localizedConverter
     * @param string                               $tmpStorageDir
     */
    public function __construct(
        ProductBuilderInterface $productBuilder,
        UserContext $userContext,
        NormalizerInterface $normalizer,
        ObjectUpdaterInterface $productUpdater,
        ValidatorInterface $productValidator,
        NormalizerInterface $internalNormalizer,
        LocalizedAttributeConverterInterface $localizedConverter,
        $tmpStorageDir
    ) {
        $this->productBuilder     = $productBuilder;
        $this->userContext        = $userContext;
        $this->normalizer         = $normalizer;
        $this->productUpdater     = $productUpdater;
        $this->productValidator   = $productValidator;
        $this->tmpStorageDir      = $tmpStorageDir;
        $this->internalNormalizer = $internalNormalizer;
        $this->localizedConverter = $localizedConverter;
        $this->values             = '';
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
     *
     * Before sending configuration to the job, we store uploaded files.
     * This way, the job process can have access to uploaded files.
     */
    public function finalize()
    {
        $data = json_decode($this->values, true);
        $filesystem = new Filesystem();

        foreach ($data as $attributeCode => $attributeValues) {
            foreach ($attributeValues as $index => $value) {
                if (isset($value['data']['filePath']) && '' !== $value['data']['filePath']) {
                    $uploadedFile = new \SplFileInfo($value['data']['filePath']);
                    $newPath = $this->tmpStorageDir . DIRECTORY_SEPARATOR . $uploadedFile->getFilename();

                    $filesystem->rename($uploadedFile->getPathname(), $newPath);

                    $data[$attributeCode][$index]['data']['filePath'] = $newPath;
                }
            }
        }

        $this->values = json_encode($data);
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
            'attribute_locale'  => $this->getAttributeLocale()
        ];

        return $actions;
    }

    /**
     * Get the code of the JobInstance
     *
     * @return string
     */
    public function getBatchJobCode()
    {
        return 'edit_common_attributes';
    }

    /**
     * Get the name of items this operation applies to
     *
     * @return string
     */
    public function getItemsName()
    {
        return 'product';
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
        $data = $this->localizedConverter->convertLocalizedToDefaultValues($data, ['locale' => $locale]);

        $product = $this->productBuilder->createProduct('0');
        $this->productUpdater->update($product, $data);
        $violations = $this->productValidator->validate($product);
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
}
