<?php

namespace Pim\Bundle\CatalogBundle\Doctrine\ORM\Repository;

use Doctrine\ORM\EntityRepository;
use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Repository\ProductTemplateRepositoryInterface;

/**
 * Product template Repository
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductTemplateRepository extends EntityRepository implements ProductTemplateRepositoryInterface
{
    /**
     * {@inheritdoc}
     */
    public function findByAttribute(AttributeInterface $attribute)
    {
        return $this->createQueryBuilder('pt')
            ->where('pt.valuesData LIKE :attribute')
            ->setParameter('attribute', '%"'.$attribute->getCode().'":%')
            ->getQuery()
            ->getResult();
    }
}
