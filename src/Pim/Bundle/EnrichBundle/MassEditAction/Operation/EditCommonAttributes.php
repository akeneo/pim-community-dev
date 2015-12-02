<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Akeneo\Component\FileStorage\File\FileStorerInterface;
use Akeneo\Component\StorageUtils\Updater\ObjectUpdaterInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Manager\ProductMassActionManager;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
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

    /** @var ArrayCollection */
    protected $displayedAttributes;

    /** @var LocaleInterface */
    protected $locale;

    /** @var ProductBuilderInterface */
    protected $productBuilder;

    /** @var UserContext */
    protected $userContext;

    /** @var CatalogContext */
    protected $catalogContext;

    /** @var array */
    protected $allAttributes;

    /** @var NormalizerInterface */
    protected $normalizer;

    /** @var AttributeRepositoryInterface */
    protected $attributeRepository;

    /** @var FileStorerInterface */
    protected $fileStorer;

    /** @var ProductMassActionManager */
    protected $massActionManager;

    /** @var string */
    protected $errors;

    /** @var ObjectUpdaterInterface */
    protected $productUpdater;

    /** @var ValidatorInterface */
    protected $productValidator;

    /** @var NormalizerInterface */
    protected $internalNormalizer;

    /** @var string */
    protected $tmpStorageDir;

    /**
     * @param ProductBuilderInterface      $productBuilder
     * @param UserContext                  $userContext
     * @param CatalogContext               $catalogContext
     * @param AttributeRepositoryInterface $attributeRepository
     * @param NormalizerInterface          $normalizer
     * @param FileStorerInterface          $fileStorer
     * @param ProductMassActionManager     $massActionManager
     * @param ObjectUpdaterInterface       $productUpdater
     * @param ValidatorInterface           $productValidator
     * @param NormalizerInterface          $internalNormalizer
     * @param string                       $tmpStorageDir
     */
    public function __construct(
        ProductBuilderInterface $productBuilder,
        UserContext $userContext,
        CatalogContext $catalogContext,
        AttributeRepositoryInterface $attributeRepository,
        NormalizerInterface $normalizer,
        FileStorerInterface $fileStorer,
        ProductMassActionManager $massActionManager,
        ObjectUpdaterInterface $productUpdater,
        ValidatorInterface $productValidator,
        NormalizerInterface $internalNormalizer,
        $tmpStorageDir = null
    ) {
        $this->productBuilder      = $productBuilder;
        $this->userContext         = $userContext;
        $this->catalogContext      = $catalogContext;
        $this->displayedAttributes = new ArrayCollection();
        $this->values              = '';
        $this->normalizer          = $normalizer;
        $this->attributeRepository = $attributeRepository;
        $this->fileStorer          = $fileStorer;
        $this->massActionManager   = $massActionManager;
        $this->productUpdater      = $productUpdater;
        $this->productValidator    = $productValidator;
        $this->tmpStorageDir       = $tmpStorageDir;
        $this->internalNormalizer  = $internalNormalizer;
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
     * @param LocaleInterface $locale
     *
     * @return EditCommonAttributes
     *
     * @deprecated Will be removed in 1.6
     */
    public function setLocale(LocaleInterface $locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return LocaleInterface
     *
     * @deprecated Will be removed in 1.6
     */
    public function getLocale()
    {
        if ($this->locale instanceof LocaleInterface) {
            return $this->locale;
        }

        return $this->userContext->getCurrentLocale();
    }

    /**
     * @param Collection $displayedAttributes
     *
     * @return EditCommonAttributes
     *
     * @deprecated Will be removed in 1.6
     */
    public function setDisplayedAttributes(Collection $displayedAttributes)
    {
        $this->displayedAttributes = $displayedAttributes;

        return $this;
    }

    /**
     * @return Collection
     *
     * @deprecated Will be removed in 1.6
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
                    $tmpStorageDir = (null !== $this->tmpStorageDir) ? $this->tmpStorageDir : sys_get_temp_dir() . '/pim/file_storage';
                    $newPath = $tmpStorageDir . DIRECTORY_SEPARATOR . $uploadedFile->getFilename();

                    $filesystem->rename($uploadedFile->getPathname(), $newPath);

                    $data[$attributeCode][$index]['data']['filePath'] = $newPath;
                }
            }
        }

        $this->values = json_encode($data);
    }

    /**
     * Initializes self::allAttributes with values from the repository
     *
     * @return array
     *
     * @deprecated Will be removed in 1.6
     */
    public function getAllAttributes()
    {
        if (null === $this->allAttributes) {
            $locale = $this->getLocale()->getCode();
            $allAttributes = $this->attributeRepository->findWithGroups([], ['conditions' => ['unique' => 0]]);

            foreach ($allAttributes as $attribute) {
                $attribute->setLocale($locale);
                $attribute->getGroup()->setLocale($locale);
            }

            $allAttributes = $this->massActionManager->filterLocaleSpecificAttributes(
                $allAttributes,
                $locale
            );

            $this->allAttributes = $allAttributes;
        }

        return $this->allAttributes;
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
            'normalized_values' => $this->getValues()
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

        $product = $this->productBuilder->createProduct('0');
        $this->productUpdater->update($product, $data);
        $violations = $this->productValidator->validate($product);

        $errors = ['values' => $this->internalNormalizer->normalize($violations, 'internal_api')];
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
}
