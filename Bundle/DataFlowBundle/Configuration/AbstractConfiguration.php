<?php
namespace Oro\Bundle\DataFlowBundle\Configuration;

use JMS\Serializer\Annotation\Exclude;

/**
 * Abstract Configuration
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
abstract class AbstractConfiguration implements ConfigurationInterface, EditableConfigurationInterface
{
    /**
     * @Exclude
     * @var integer
     */
    protected $id;

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritDoc}
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }
}
