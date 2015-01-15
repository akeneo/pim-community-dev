<?php


namespace Pim\Bundle\EnrichBundle\MassEditAction\Operation;

use Akeneo\Component\Persistence\BulkSaverInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Entity\Repository\GroupRepository;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Pim\Bundle\CatalogBundle\Model\ProductInterface;

/**
 * My class definition
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddToVariantGroup extends ProductMassEditOperation
{
    /** @var GroupRepository */
    protected $groupRepository;

    /** @var GroupInterface */
    protected $group;

    /** @var string[] */
    protected $warningMessages = null;

    /**
     * @param GroupRepository    $groupRepository
     * @param BulkSaverInterface $productSaver
     */
    public function __construct(GroupRepository $groupRepository, BulkSaverInterface $productSaver)
    {
        parent::__construct($productSaver);

        $this->groupRepository = $groupRepository;
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

        $alreadyInVariantGroup = [];
        foreach ($products as $product) {
            if (null != $product->getVariantGroup()) {
                $alreadyInVariantGroup[] = $product->getIdentifier();
            }
        }

        if (count($alreadyInVariantGroup) > 1) {
            $messages[] = [
                'key'     => 'pim_enrich.mass_edit_action.add-to-variant-group.already_in_variant_group',
                'options' => ['%products%' => implode(', ', $alreadyInVariantGroup)]
            ];
        }

        return $messages;
    }
}
