<?php

namespace Pim\Bundle\CatalogBundle\Model;

/**
 * Product template model, aims to store common product values for different products in order to copy them to products
 * later, used by groups of type variant group, may be used linked to other objects or as standalone template
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @deprecated please use \Pim\Component\Catalog\Model\ProductTemplateInterface
 */
interface ProductTemplateInterface extends \Pim\Component\Catalog\Model\ProductTemplateInterface
{
}
