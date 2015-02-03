<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Akeneo\Component\StorageUtils\Saver\BulkSaverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;
use Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface;

/**
 * Adds many products to many groups
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddToGroups extends ProductMassEditOperation
{
    /** @var GroupRepositoryInterface */
    protected $groupRepository;

    /** @var ArrayCollection */
    protected $groups;

    /** @var string[] */
    protected $warningMessages;

    /**
     * @param GroupRepositoryInterface $groupRepository
     * @param BulkSaverInterface       $productSaver
     */
    public function __construct(GroupRepositoryInterface $groupRepository, BulkSaverInterface $productSaver)
    {
        parent::__construct($productSaver);

        $this->groupRepository = $groupRepository;
        $this->groups = new ArrayCollection();
    }

    /**
     * Set groups
     *
     * @param array $groups
     */
    public function setGroups(array $groups)
    {
        $this->groups = new ArrayCollection($groups);
    }

    /**
     * Get groups
     *
     * @return array
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormOptions()
    {
        return [
            'groups' => $this->groupRepository->getAllGroupsExceptVariant()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return 'pim_enrich_mass_add_to_groups';
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

        if (count($this->groupRepository->getAllGroupsExceptVariant()) === 0) {
            $messages[] = [
                'key'     => 'pim_enrich.mass_edit_action.add-to-groups.no_group',
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
        foreach ($this->groups as $group) {
            $group->addProduct($product);
        }
    }
}
