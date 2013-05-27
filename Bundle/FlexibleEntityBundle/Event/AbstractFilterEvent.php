<?php
namespace Oro\Bundle\FlexibleEntityBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager;

/**
 * Filter event allows to know the create flexible attribute
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
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
