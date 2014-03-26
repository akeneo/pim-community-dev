<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction;

use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Model\ProductRepositoryInterface;

/**
 * Adds many products to many groups
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddToGroups extends AbstractMassEditAction
{
    /**
     * @var ProductRepositoryInterface $productRepository
     */
    protected $productRepository;

    /** @var ArrayCollection */
    protected $groups;

    /** @var EntityManager */
    protected $entityManager;

    /**
     * Constructor
     *
     * @param EntityManager $entityManager
     *
     * TODO: Remove EntityManager and inject GroupRepository
     */
    public function __construct(ProductRepositoryInterface $productRepository, EntityManager $entityManager)
    {
        $this->productRepository = $productRepository;
        $this->entityManager     = $entityManager;
        $this->groups            = new ArrayCollection();
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
     *
     * TODO: Inject GroupRepository
     */
    public function getFormOptions()
    {
        $groups = $this
            ->entityManager
            ->getRepository('PimCatalogBundle:Group')
            ->findAll();

        return array(
            'groups' => $groups,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return 'pim_enrich_mass_add_to_groups';
    }

    /**
     * {@inheritdoc}
     *
     * TODO: Check with MongoDB implementation
     */
    public function perform(array $productIds)
    {
        $products = $this->productRepository->findBy(array('id' => $productIds));
        foreach ($products as $product) {
            foreach ($this->getGroups() as $group) {
                $group->addProduct($product);
            }
        }
    }
}
