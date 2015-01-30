<?php

namespace Pim\Bundle\BaseConnectorBundle\Reader\Repository;

use Pim\Bundle\CatalogBundle\Repository\GroupRepositoryInterface;

/**
 * The variant group repository reader
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupReader extends AbstractReader
{
    /** @var GroupRepositoryInterface */
    protected $repository;

    /**
     * @param GroupRepositoryInterface $repository
     */
    public function __construct(GroupRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    protected function readItems()
    {
        return $this->repository->getAllVariantGroups();
    }
}
