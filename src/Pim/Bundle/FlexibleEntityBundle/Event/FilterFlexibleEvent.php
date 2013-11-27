<?php

namespace Pim\Bundle\FlexibleEntityBundle\Event;

use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Pim\Bundle\FlexibleEntityBundle\Model\FlexibleInterface;

/**
 * Filter event allows to know the create flexible value
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilterFlexibleEvent extends AbstractFilterEvent
{
    /**
     * Flexible entity
     * @var FlexibleInterface
     */
    protected $entity;

    /**
     * Constructor
     *
     * @param FlexibleManager   $manager the manager
     * @param FlexibleInterface $entity  the entity
     */
    public function __construct(FlexibleManager $manager, FlexibleInterface $entity)
    {
        parent::__construct($manager);
        $this->entity = $entity;
    }

    /**
     * @return FlexibleInterface
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
