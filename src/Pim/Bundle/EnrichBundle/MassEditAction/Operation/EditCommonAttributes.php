<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Manager\MediaManager;
use Pim\Bundle\CatalogBundle\Manager\ProductMassActionManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Edit common attributes of given products
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EditCommonAttributes extends AbstractMassEditOperation
{
    /** @var ArrayCollection|ProductValueInterface[] */
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

    /** @var MediaManager */
    protected $mediaManager;

    /** @var string */
    protected $uploadDir;

    /** @var ProductMassActionManager */
    protected $massActionManager;

    /**
     * @param ProductBuilderInterface      $productBuilder
     * @param UserContext                  $userContext
     * @param CatalogContext               $catalogContext
     * @param AttributeRepositoryInterface $attributeRepository
     * @param NormalizerInterface          $normalizer
     * @param MediaManager                 $mediaManager
     * @param ProductMassActionManager     $massActionManager
     * @param string                       $uploadDir
     */
    public function __construct(
        ProductBuilderInterface $productBuilder,
        UserContext $userContext,
        CatalogContext $catalogContext,
        AttributeRepositoryInterface $attributeRepository,
        NormalizerInterface $normalizer,
        MediaManager $mediaManager,
        ProductMassActionManager $massActionManager,
        $uploadDir
    ) {
        $this->productBuilder      = $productBuilder;
        $this->userContext         = $userContext;
        $this->catalogContext      = $catalogContext;
        $this->displayedAttributes = new ArrayCollection();
        $this->values              = new ArrayCollection();
        $this->normalizer          = $normalizer;
        $this->attributeRepository = $attributeRepository;
        $this->mediaManager        = $mediaManager;
        $this->uploadDir           = $uploadDir;
        $this->massActionManager   = $massActionManager;
    }

    /**
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
     * @return Collection
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
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
     * {@inheritdoc}
     */
    public function getFormOptions()
    {
        return [
            'locales'        => $this->userContext->getUserLocales(),
            'all_attributes' => $this->getAllAttributes(),
            'current_locale' => $this->getLocale()->getCode()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $locale = $this->getLocale()->getCode();
        $this->catalogContext->setLocaleCode($locale);
        $this->allAttributes = null;
        $this->values = new ArrayCollection();

        $allAttributes = $this->getAllAttributes();

        $this->values = new ArrayCollection();
        foreach ($allAttributes as $attribute) {
            $this->addValues($attribute, $this->getLocale());
        }
    }

    /**
     * {@inheritdoc}
     *
     * Before sending configuration to the job, we move uploaded files
     * from '/tmp/' directory to the upload directory.
     *
     * This way, the job process can have access to uploaded files.
     */
    public function finalize()
    {
        foreach ($this->values as $productValue) {
            $media = $productValue->getMedia();

            if (null !== $media && null !== $media->getFile()) {
                $tmpFile = $media->getFile();
                $name = sprintf('%s-%s', uniqid(), time());
                $movedFile = $tmpFile->move($this->uploadDir, $name);

                $jobFile = new UploadedFile(
                    $movedFile->getPathname(),
                    $tmpFile->getClientOriginalName(),
                    $tmpFile->getClientMimeType(),
                    $tmpFile->getClientSize(),
                    $tmpFile->getError()
                );

                $media->setFile($jobFile);
                $productValue->setMedia($media);
            }
        }
    }

    /**
     * Initializes self::allAtributes with values from the repository
     *
     * @return array
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
        $actions = [];

        foreach ($this->values as $value) {
            $rawData = $this->normalizer->normalize($value->getData(), 'json', ['entity' => 'product']);
            // if the value is localizable, let's use the locale the user has chosen in the form
            $locale = null !== $value->getLocale() ? $this->getLocale()->getCode() : null;

            $actions[] = [
                'field'   => $value->getAttribute()->getCode(),
                'value'   => $rawData,
                'options' => ['locale' => $locale, 'scope' => $value->getScope()]
            ];
        }

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
}
