<?php

/*
* This file is part of the Akeneo PIM Enterprise Edition.
*
* (c) 2015 Akeneo SAS (http://www.akeneo.com)
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace PimEnterprise\Bundle\EnrichBundle\Twig;

use Pim\Bundle\EnrichBundle\Twig\AttributeExtension as BaseAttributeExtension;

/**
 * Override Twig extension to allow to add Enterprise icons (as AssetCollectionType)
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class AttributeExtension extends BaseAttributeExtension
{
    /**
     * @param array $communityIcons
     * @param array $eeIcons
     */
    public function __construct(array $communityIcons, array $eeIcons)
    {
        $this->icons = array_merge($communityIcons, $eeIcons);
    }
}
