<?php

namespace Pim\Bundle\EnrichBundle\MassEditAction\Operator;

use JMS\Serializer\Annotation\Exclude;
use Doctrine\ORM\EntityManager;
use Oro\Bundle\SecurityBundle\SecurityFacade;

/**
 * A batch operation operator
 * Applies batch operations to families passed in the form of QueryBuilder
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @Exclude
 */
class FamilyMassEditOperator extends AbstractMassEditOperator
{
    /** @var EntityManager */
    protected $manager;

    /**
     * @param SecurityFacade $securityFacade
     * @param EntityManager  $manager
     */
    public function __construct(Securityfacade $securityFacade, EntityManager $manager)
    {
        parent::__construct($securityFacade);

        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'family';
    }

    /**
     * {@inheritdoc}
     */
    public function getPerformedOperationRedirectionRoute()
    {
        return 'pim_enrich_family_index';
    }

    /**
     * {@inheritdoc}
     */
    public function finalizeOperation()
    {
        set_time_limit(0);
        $this->manager->flush();
    }
}
