<?php

namespace Pim\Bundle\CatalogBundle\Entity;

use JMS\Serializer\Annotation\ExclusionPolicy;
use Pim\Bundle\CatalogBundle\Model\AbstractAttribute;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Product attribute, business code is in AbstractAttribute, this class can be overriden in projects
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @Assert\GroupSequenceProvider
 *
 * @ExclusionPolicy("all")
 */
class Attribute extends AbstractAttribute
{
}
