<?php
namespace Oro\Bundle\FlexibleEntityBundle\Event;

use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Oro\Bundle\FlexibleEntityBundle\Model\FlexibleInterface;

/**
 * Filter event allows to know the create flexible value
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
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
