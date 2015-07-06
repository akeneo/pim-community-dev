<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductMassActionRepositoryInterface;
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

    /** @var GroupInterface[]  */
    protected $validVariantGroups = null;

    /**
     * @param GroupRepositoryInterface             $groupRepository
     * @param BulkSaverInterface                   $productSaver
     * @param ProductTemplateUpdaterInterface      $templateUpdater
     * @param ValidatorInterface                   $validator
     * @param ProductMassActionRepositoryInterface $prodMassActionRepo
     */
    public function __construct(
        GroupRepositoryInterface $groupRepository,
        BulkSaverInterface $productSaver,
        ProductTemplateUpdaterInterface $templateUpdater,
        ValidatorInterface $validator,
        ProductMassActionRepositoryInterface $prodMassActionRepo
    ) {
        parent::__construct($productSaver);

        $this->groupRepository = $groupRepository;
        $this->templateUpdater = $templateUpdater;
        $this->validator = $validator;
        $this->productMassActionRepo = $prodMassActionRepo;
    }

    /**
     * {@inheritdoc}
     */
    public function setObjectsToMassEdit(array $products)
    {
        $this->objects        = [];
        $this->skippedObjects = [];

        foreach ($products as $product) {
            if (is_array($product)) {
                $product = $product[0];
            }

            $violations = $this->validator->validate($product, ['pim_catalog_variant_group']);

            if ($product instanceof ProductInterface &&
                null === $product->getVariantGroup() &&
                0 === count($violations)
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
            'groups' => $this->getValidVariantGroups()
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
            $this->warningMessages = $this->generateWarningMessages(
                $this->skippedObjects,
                $this->getValidVariantGroups()
            );
        }

        return $this->warningMessages;
    }

    /**
     * Get valid variant groups to display
     *
     * @return array
     */
    public function getValidVariantGroups()
    {
        if (null === $this->validVariantGroups) {
            $this->validVariantGroups = $this->getVariantGroupsWithCommonAttributes($this->objects);
        }

        return $this->validVariantGroups;
    }

    /**
     * Get warning messages to display during the mass edit action
     *
     * @param array $skippedProducts
     * @param array $validVariantGroups
     *
     * @return string[]
     */
    protected function generateWarningMessages(array $skippedProducts, array $validVariantGroups)
    {
        $messages = [];

        if (0 === (int) $this->groupRepository->countVariantGroups()) {
            $messages[] = [
                'key'     => 'pim_enrich.mass_edit_action.add-to-variant-group.no_variant_group',
                'options' => []
            ];
        } elseif (0 === count($validVariantGroups)) {
            $messages[] = [
                'key'     => 'pim_enrich.mass_edit_action.add-to-variant-group.no_valid_variant_group',
                'options' => []
            ];
        }

        $invalidProducts = $this->getInvalidProductsInfos($skippedProducts);
        if ($invalidProducts) {
            $messages[] = [
                'key'     => 'pim_enrich.mass_edit_action.add-to-variant-group.already_in_variant_group_or_not_valid',
                'options' => ['%products%' => implode(', ', $invalidProducts)]
            ];
        }

        $skippedVariantGroups = $this->getSkippedVariantGroupsInfos($validVariantGroups);
        if ($skippedVariantGroups) {
            $messages[] = [
                'key'     => 'pim_enrich.mass_edit_action.add-to-variant-group.some_variant_groups_are_skipped',
                'options' => ['%groups%' => implode(', ', $skippedVariantGroups)]
            ];
        }

        return $messages;
    }

    /**
     * Get identifier for each $skippedProducts.
     *
     * @param array $skippedProducts
     *
     * @return array
     */
    protected function getInvalidProductsInfos(array $skippedProducts)
    {
        $invalidProducts = [];

        foreach ($skippedProducts as $product) {
            if ($product instanceof ProductInterface) {
                $invalidProducts[] = $product->getIdentifier();
            }
        }

        return $invalidProducts;
    }

    /**
     * Get warning information if there is any skipped variant group (no common attribute with products).
     * Return all skipped variant groups with their label and code, or an empty array if no variant group skipped.
     *
     * @param array $validVariantGroups
     *
     * @return array
     */
    protected function getSkippedVariantGroupsInfos(array $validVariantGroups)
    {
        // For now, we show all label and code of skipped groups (not good if too many)
        if ($validVariantGroups) {
            $validIds = array_map(function (GroupInterface $validGroup) {
                return $validGroup->getId();
            }, $validVariantGroups);

            $invalidVariantGroups = $this->groupRepository->getVariantGroupsByIds($validIds, false);
        } else {
            $invalidVariantGroups = $this->groupRepository->getAllVariantGroups();
        }

        return array_map(function (GroupInterface $variantGroup) {
            return sprintf('%s [%s]', $variantGroup->getLabel(), $variantGroup->getCode());
        }, $invalidVariantGroups);
    }

    /**
     * Returns all variant groups with common attributes with selected $products
     *
     * @param array $products
     *
     * @return array
     */
    protected function getVariantGroupsWithCommonAttributes(array $products)
    {
        $validVariantGroups = [];

        if ($products) {
            $productIds = array_map(function (ProductInterface $product) {
                return $product->getId();
            }, $products);

            $commonAttributeIds = $this->productMassActionRepo->findCommonAttributeIds($productIds);
            $validVariantGroups = $this->groupRepository->getVariantGroupsByAttributeIds($commonAttributeIds);
        }

        return $validVariantGroups;
    }
}
