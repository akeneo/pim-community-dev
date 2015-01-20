<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\DataGridBundle\Datagrid\PublishedProduct;

use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use PimEnterprise\Bundle\WorkflowBundle\Repository\PublishedProductRepositoryInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Helper for published datagrid
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
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
            /** @var \PimEnterprise\Bundle\WorkflowBundle\Model\PublishedProductInterface $published */
            $published = $this->publishedRepository->findOneById($record->getValue('id'));
            $ownershipGranted = $this->securityContext->isGranted(Attributes::OWN, $published->getOriginalProduct());

            return [
                'unpublish' => $ownershipGranted,
            ];
        };
    }
}
