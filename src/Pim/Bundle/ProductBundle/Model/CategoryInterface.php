<?php

namespace Pim\Bundle\ProductBundle\Model;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Category interface
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @UniqueEntity(fields="code", message="This code is already taken")
 */
interface CategoryInterface
{
}
