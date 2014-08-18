<?php

namespace Pim\Bundle\BaseConnectorBundle\Reader\ORM;

use Doctrine\ORM\EntityRepository;
use Pim\Bundle\BaseConnectorBundle\Reader\Doctrine\Reader;

/**
 * Category reader that reads categories ordered by tree and order inside the tree
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CategoryReader extends Reader
{
    /**
     * @var EntityRepository
     */
    protected $categoryRepository;

    /**
     * @param EntityRepository $categoryRepository
     */
    public function __construct(EntityRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery()
    {
        if (!$this->query) {
            $qb = $this->categoryRepository->createQueryBuilder('c');

            $qb
                ->orderBy('c.root')
                ->addOrderBy('c.left');

            $this->query = $qb->getQuery();
        }

        return $this->query;
    }
}
