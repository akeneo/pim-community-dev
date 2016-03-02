<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use Pim\Component\Catalog\Model\AbstractAttribute;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Product attribute, business code is in AttributeInterface, this class can be overriden in projects
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Assert\GroupSequenceProvider
 */
class Attribute extends AbstractAttribute
{
}
