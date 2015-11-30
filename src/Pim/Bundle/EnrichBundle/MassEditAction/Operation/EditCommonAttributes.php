<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Akeneo\Component\FileStorage\File\FileStorerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Pim\Bundle\CatalogBundle\Builder\ProductBuilderInterface;
use Pim\Bundle\CatalogBundle\Context\CatalogContext;
use Pim\Bundle\CatalogBundle\Manager\ProductMassActionManager;
use Pim\Bundle\CatalogBundle\Repository\AttributeRepositoryInterface;
use Pim\Bundle\UserBundle\Context\UserContext;
use Pim\Component\Catalog\FileStorage;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\LocaleInterface;
use Pim\Component\Catalog\Model\ProductValueInterface;
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

    /**
     * @param ProductBuilderInterface      $productBuilder
     * @param UserContext                  $userContext
     * @param CatalogContext               $catalogContext
     * @param AttributeRepositoryInterface $attributeRepository
     * @param NormalizerInterface          $normalizer
     * @param FileStorerInterface          $fileStorer
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
        $this->values              = '';
        $this->normalizer          = $normalizer;
        $this->attributeRepository = $attributeRepository;
        $this->fileStorer          = $fileStorer;
        $this->massActionManager   = $massActionManager;
    }

    /**
     * @param string $values
     *
     * @return EditCommonAttributes
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
    public function getBatchConfig()
    {
        $config = json_decode(stripslashes(parent::getBatchConfig()), true);

        $config['locale'] = $this->userContext->getUiLocale();

        return addslashes(json_encode($config));
    }

    /**
     * {@inheritdoc}
     */
    public function initialize()
    {
        $this->values = '{id: 13}';
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
            'product'           => 'lepipou'
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
}
