<?php
namespace Oro\Bundle\FlexibleEntityBundle\Event;

use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Oro\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface;

/**
 * Filter event allows to know the create flexible value
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class FilterFlexibleValueEvent extends AbstractFilterEvent
{
    /**
     * Flexible value
     * @var FlexibleValueInterface
     */
    protected $value;

    /**
     * Constructor
     *
     * @param FlexibleManager        $manager the manager
     * @param FlexibleValueInterface $value   the value
     */
    public function __construct(FlexibleManager $manager, FlexibleValueInterface $value)
    {
        parent::__construct($manager);
        $this->value = $value;
    }

    /**
     * @return FlexibleValueInterface
     */
    public function getValue()
    {
        return $this->value;
    }
}
