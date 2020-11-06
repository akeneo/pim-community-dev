<?php

namespace Akeneo\Pim\Enrichment\Bundle\Doctrine\ORM\Repository;

use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductCategoryRepositoryInterface;
use Akeneo\Tool\Bundle\ClassificationBundle\Doctrine\ORM\Repository\AbstractItemCategoryRepository;
use Doctrine\ORM\EntityManager;

/**
 * Product category repository
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCategoryRepository extends AbstractItemCategoryRepository implements ProductCategoryRepositoryInterface
{
    /** @var string */
    protected $categoryClass;

    /**
     * @param EntityManager $em
     * @param string        $entityName
     * @param string        $categoryClass
     */
    public function __construct(EntityManager $em, string $entityName, string $categoryClass)
    {
        parent::__construct($em, $entityName);

        $this->categoryClass = $categoryClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifierProperties(): array
    {
        return ['code'];
    }

    /**
     * {@inheritdoc}
     */
    public function findOneByIdentifier(string $identifier)
    {
        $qb = $this->em->createQueryBuilder()
            ->select('c')
            ->from($this->categoryClass, 'c', 'c.id')
            ->where('c.code = :code')
            ->setParameter('code', $identifier);

        return $qb->getQuery()->getOneOrNullResult();
    }
}
