<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Datagrid\Configuration\PublishedProduct;

use Akeneo\Pim\Permission\Component\Attributes;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProductInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\PublishedProductRepositoryInterface;
use Oro\Bundle\DataGridBundle\Datasource\ResultRecordInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Helper for published datagrid
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class GridHelper
{
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /** @var PublishedProductRepositoryInterface */
    protected $publishedRepository;

    /**
     * @param AuthorizationCheckerInterface       $authorizationChecker
     * @param PublishedProductRepositoryInterface $publishedRepository
     */
    public function __construct(
        AuthorizationCheckerInterface $authorizationChecker,
        PublishedProductRepositoryInterface $publishedRepository
    ) {
        $this->authorizationChecker = $authorizationChecker;
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
            /** @var PublishedProductInterface $published */
            $published = $this->publishedRepository->find($record->getValue('id'));
            $product = $published->getOriginalProduct();
            $ownershipGranted = $this->authorizationChecker->isGranted(Attributes::OWN, $product);

            return [
                'unpublish' => $ownershipGranted,
            ];
        };
    }
}
