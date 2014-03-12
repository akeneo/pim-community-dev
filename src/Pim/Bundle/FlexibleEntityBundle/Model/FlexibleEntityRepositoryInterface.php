<?php

namespace Pim\Bundle\FlexibleEntityBundle\Model;

use Pim\Bundle\FlexibleEntityBundle\Model\Behavior\LocalizableInterface;
use Pim\Bundle\FlexibleEntityBundle\Model\Behavior\ScopableInterface;

/**
 * Repository interface for flexible entity
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface FlexibleEntityRepositoryInterface extends LocalizableInterface, ScopableInterface
{
    /**
     * Get flexible entity config
     *
     * @return array $config
     */
    public function getFlexibleConfig();

    /**
     * Set flexible entity config
     *
     * @param array $config
     *
     * @return FlexibleEntityRepositoryInterface
     */
    public function setFlexibleConfig($config);
}
