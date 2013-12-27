<?php
namespace Pim\Bundle\CatalogBundle\Datasource\Orm;

use Doctrine\ORM\EntityManager;
use Oro\Bundle\SecurityBundle\ORM\Walker\AclHelper;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Datagrid\DatagridInterface;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;

/**
 * Product data source
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class OrmProductDatasource extends OrmDatasource
{
    /**
     * @var string
     */
    const TYPE = 'orm_product';

    /**
     * @var ProductManager
     */
    protected $productManager;

    /**
     * Instanciate a product data source
     *
     * @param EntityManager  $em
     * @param AclHelper      $aclHelper
     * @param ProductManager $productManager
     */
    public function __construct(EntityManager $em, AclHelper $aclHelper, ProductManager $productManager)
    {
        parent::__construct($em, $aclHelper);
        $this->productManager = $productManager;
    }

    /**
     * {@inheritDoc}
     */
    public function process(DatagridInterface $grid, array $config)
    {
        // TODO : we should inject in product manager
        $this->productManager->setLocale('en_US');
        $this->productManager->setScope('ecommerce');

        $this->qb = $this->em->getRepository('Pim\Bundle\CatalogBundle\Model\Product')
            ->createQueryBuilder('p');
        $rootAlias = $this->qb->getRootAlias();
        $this->qb->addSelect('p');

        $this->qb
            ->leftJoin($rootAlias.'.values', 'values')
            ->leftJoin('values.options', 'valueOptions')
            ->leftJoin('values.prices', 'valuePrices')
            ->leftJoin('values.metric', 'valueMetrics')
            ->addSelect('values')
            ->addSelect('valuePrices')
            ->addSelect('valueOptions')
            ->addSelect('valueMetrics');

        $familyExpr = "(CASE WHEN familyTrans.label IS NULL THEN family.code ELSE familyTrans.label END)";
        $this->qb
            ->leftJoin($rootAlias .'.family', 'family')
            ->leftJoin('family.translations', 'familyTrans', 'WITH', 'familyTrans.locale = :localeCode')
            ->addSelect(sprintf("%s AS familyLabel", $familyExpr));

        $this->qb->setParameter('localeCode', $this->productManager->getLocale());

        $grid->setDatasource(clone $this);
    }
}
