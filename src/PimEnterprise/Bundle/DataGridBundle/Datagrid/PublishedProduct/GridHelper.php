<?php

namespace PimEnterprise\Bundle\DataGridBundle\Datagrid\PublishedProduct;

use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedProductRepositoryInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;

/**
 * Helper for published datagrid
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class GridHelper
{
    /** @var SecurityContextInterface */
    protected $securityContext;

    /** @var PublishedProductRepositoryInterface */
    protected $publishedRepository;

    /**
     * @param SecurityContextInterface            $securityContext
     * @param PublishedProductRepositoryInterface $publishedRepository
     */
    public function __construct(
        SecurityContextInterface $securityContext,
        PublishedProductRepositoryInterface $publishedRepository
    ) {
        $this->securityContext = $securityContext;
        $this->publishedRepository = $publishedRepository;
    }

    /**
     * Returns a callback to ease the configuration of different actions for each row
     *
     * @return callable
     */
    public function getActionConfigurationClosure()
    {
        return function (ResultRecordInterface $record) {
            $published = $this->publishedRepository->findOneBy(['id' => $record->getValue('id')]);
            $ownershipGranted = $this->securityContext->isGranted(Attributes::OWN, $published);

            return [
                'unpublish' => $ownershipGranted,
            ];
        };
    }
}
