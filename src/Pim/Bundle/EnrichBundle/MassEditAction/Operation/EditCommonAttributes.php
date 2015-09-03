<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Akeneo\Component\FileStorage\File\FileStorerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Manager\ProductMassActionManager;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\LocaleInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Catalog\FileStorage;
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

    /** @var FileStorerInterface */
    protected $fileStorer;

    /** @var ProductMassActionManager */
    protected $massActionManager;

    /**
     * @param ProductBuilderInterface      $productBuilder
     * @param UserContext                  $userContext
     * @param CatalogContext               $catalogContext
     * @param AttributeRepositoryInterface $attributeRepository
     * @param NormalizerInterface          $normalizer
     * @param FileStorerInterface       $fileStorer
     * @param ProductMassActionManager     $massActionManager
     */
    public function __construct(
        ProductBuilderInterface $productBuilder,
        UserContext $userContext,
        CatalogContext $catalogContext,
        AttributeRepositoryInterface $attributeRepository,
        NormalizerInterface $normalizer,
        FileStorerInterface $fileStorer,
        ProductMassActionManager $massActionManager
    ) {
        $this->productBuilder      = $productBuilder;
        $this->userContext         = $userContext;
        $this->catalogContext      = $catalogContext;
        $this->displayedAttributes = new ArrayCollection();
        $this->values              = new ArrayCollection();
        $this->normalizer          = $normalizer;
        $this->attributeRepository = $attributeRepository;
        $this->fileStorer       = $fileStorer;
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
     * Before sending configuration to the job, we store uploaded files.
     * This way, the job process can have access to uploaded files.
     */
    public function finalize()
    {
        foreach ($this->getValues() as $productValue) {
            $media = $productValue->getMedia();

            if (null !== $media && null !== $media->getUploadedFile()) {
                $file = $this->fileStorer->store($media->getUploadedFile(), FileStorage::CATALOG_STORAGE_ALIAS, true);
                $productValue->setMedia($file);
            }
        }
    }

    /**
     * Initializes self::allAttributes with values from the repository
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
