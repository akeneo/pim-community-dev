<?php

namespace Pim\Bundle\BatchBundle\Configuration;

use JMS\Serializer\Annotation\Exclude;

/**
 * Abstract configuration
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
