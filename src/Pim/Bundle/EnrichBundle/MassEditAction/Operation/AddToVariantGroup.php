<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface;
use Pim\Bundle\CatalogBundle\Updater\ProductTemplateUpdaterInterface;
use Symfony\Component\Validator\ValidatorInterface;

/**
 * Operation to add products to variant groups
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddToVariantGroup extends ProductMassEditOperation
{
    /** @var array */
    protected $skippedObjects = [];

    /** @var GroupRepositoryInterface */
    protected $groupRepository;

    /** @var ProductTemplateUpdaterInterface */
    protected $templateUpdater;

    /** @var ValidatorInterface */
    protected $validator;

    /** @var GroupInterface */
    protected $group;

    /** @var string[] */
    protected $warningMessages = null;

    /**
     * @param GroupRepositoryInterface        $groupRepository
     * @param BulkSaverInterface              $productSaver
     * @param ProductTemplateUpdaterInterface $templateUpdater
     * @param ValidatorInterface              $validator
     */
    public function __construct(
        GroupRepositoryInterface $groupRepository,
        BulkSaverInterface $productSaver,
        ProductTemplateUpdaterInterface $templateUpdater,
        ValidatorInterface $validator
    ) {
        parent::__construct($productSaver);

        $this->groupRepository = $groupRepository;
        $this->templateUpdater = $templateUpdater;
        $this->validator       = $validator;
    }

    /**
     * {@inheritdoc}
     */
    public function setObjectsToMassEdit(array $products)
    {
        $this->objects        = [];
        $this->skippedObjects = [];

        foreach ($products as $product) {
            $violations = $this->validator->validate($product, ['pim_catalog_variant_group']);

            if ($product instanceof ProductInterface &&
                null === $product->getVariantGroup() &&
                count($violations) === 0
            ) {
                $this->objects[] = $product;
            } else {
                $this->skippedObjects[] = $product;
            }
        }

        return $this;
    }

    /**
     * Set group
     *
     * @param GroupInterface $group
     */
    public function setGroup(GroupInterface $group)
    {
        $this->group = $group;
    }

    /**
     * Get group
     *
     * @return array
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormOptions()
    {
        return [
            'groups' => $this->groupRepository->getAllVariantGroups()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return 'pim_enrich_mass_add_to_variant_group';
    }

    /**
     * {@inheritdoc}
     */
    public function perform()
    {
        parent::perform();

        if (null !== $this->group->getProductTemplate()) {
            $this->templateUpdater->update($this->group->getProductTemplate(), $this->objects);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function doPerform(ProductInterface $product)
    {
        if (null === $product->getVariantGroup()) {
            $this->group->addProduct($product);
        }
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
        if (count($this->groupRepository->getAllVariantGroups()) === 0) {
            $messages[] = [
                'key'     => 'pim_enrich.mass_edit_action.add-to-variant-group.no_variant_group',
                'options' => []
            ];

            return $messages;
        }

        $invalidProducts = [];
        foreach ($this->skippedObjects as $product) {
            if ($product instanceof ProductInterface) {
                $invalidProducts[] = $product->getIdentifier();
            }
        }

        if (count($invalidProducts) > 0) {
            $messages[] = [
                'key'     => 'pim_enrich.mass_edit_action.add-to-variant-group.already_in_variant_group_or_not_valid',
                'options' => ['%products%' => implode(', ', $invalidProducts)]
            ];
        }

        return $messages;
    }
}
