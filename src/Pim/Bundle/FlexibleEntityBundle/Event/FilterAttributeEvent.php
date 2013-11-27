<?php

namespace Pim\Bundle\FlexibleEntityBundle\Event;

use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;
use Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;

/**
 * Filter event allows to know the create flexible attribute
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FilterAttributeEvent extends AbstractFilterEvent
{
    /**
     * Flexible attribute
     * @var AbstractAttribute
     */
    protected $attribute;

    /**
     * Constructor
     *
     * @param FlexibleManager   $manager   the manager
     * @param AbstractAttribute $attribute the attribute
     */
    public function __construct(FlexibleManager $manager, AbstractAttribute $attribute)
    {
        parent::__construct($manager);
        $this->attribute = $attribute;
    }

    /**
     * @return AbstractAttribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }
}
