<?php

namespace Pim\Bundle\FlexibleEntityBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;

/**
 * Filter event allows to know the create flexible attribute
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @abstract
 */
abstract class AbstractFilterEvent extends Event
{
    /**
     * Flexible manager
     * @var FlexibleManager
     */
    protected $manager;

    /**
     * Constructor
     * @param FlexibleManager $manager
     */
    public function __construct(FlexibleManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @return FlexibleManager
     */
    public function getManager()
    {
        return $this->manager;
    }
}
