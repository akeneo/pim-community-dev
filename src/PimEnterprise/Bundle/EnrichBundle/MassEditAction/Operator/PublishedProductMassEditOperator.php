<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\MassEditAction\Operator;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\EnrichBundle\MassEditAction\Operator\AbstractMassEditOperator;
use PimEnterprise\Bundle\WorkflowBundle\Manager\PublishedProductManager;

/**
 * A batch operation operator
 * Applies batch operations to published products passed in the form of QueryBuilder
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 */
class PublishedProductMassEditOperator extends AbstractMassEditOperator
{
    /** @var PublishedProductManager */
    protected $manager;

    /**
     * @param SecurityFacade          $securityFacade
     * @param PublishedProductManager $manager
     */
    public function __construct(SecurityFacade $securityFacade, PublishedProductManager $manager)
    {
        parent::__construct($securityFacade);

        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'published_product';
    }

    /**
     * {@inheritdoc}
     */
    public function getPerformedOperationRedirectionRoute()
    {
        return 'pimee_workflow_published_product_index';
    }

    /**
     * {@inheritdoc}
     */
    public function finalizeOperation()
    {
        // nothing to do here at the moment
    }
}
