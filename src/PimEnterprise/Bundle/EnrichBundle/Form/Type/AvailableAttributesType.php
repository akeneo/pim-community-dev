<?php

namespace PimEnterprise\Bundle\EnrichBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Pim\Bundle\EnrichBundle\Form\Type\AvailableAttributesType as PimAvailableAttributesType;

/**
 * Override available attributes type to remove attributes where rights are revoked
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AvailableAttributesType extends PimAvailableAttributesType
{
}
