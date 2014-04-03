<?php

namespace Pim\Bundle\FlexibleEntityBundle\Model\Behavior;

use Pim\Bundle\CatalogBundle\Model\LocalizableInterface as NewLocalizableInterface;

/**
 * Localizable interface, implemented by class which can be localized
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated Deprecated since version 1.1, to be removed in 1.2. Use CatalogBundle/LocalizableInterface
 */
interface LocalizableInterface extends NewLocalizableInterface
{
}
