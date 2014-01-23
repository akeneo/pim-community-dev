<?php

namespace Pim\Bundle\CatalogBundle\Doctrine;

use Doctrine\ORM\EntityRepository as BaseEntityRepository;
use Doctrine\Common\Util\Inflector;

/**
 * Base entity repository
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class EntityRepository extends BaseEntityRepository
{
    /**
     * {@inheritdoc}
     */
    public function __call($method, $arguments)
    {
        if (0 === strpos($method, 'find')) {
            if (method_exists($this, $builder = 'build'.substr($method, 4))) {
                $qb = call_user_func_array([$this, $builder], $arguments);

                if (0 === strpos(substr($method, 4), 'One')) {
                    return $qb->getQuery()->getOneOrNullResult();
                }

                return $qb->getQuery()->getResult();
            }
        }

        return parent::__call($method, $arguments);
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function build()
    {
        return $this->createQueryBuilder($this->getAlias());
    }

    /**
     * @param integer $id
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function buildOne($id)
    {
        return $this->build()->where($this->getAlias().'.id = '.intval($id));
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function buildAll()
    {
        return $this->build();
    }

    /**
     * @return string
     */
    protected function getAlias()
    {
        $name = basename(str_replace('\\', '/', $this->getClassName()));

        return Inflector::tableize($name);
    }
}
