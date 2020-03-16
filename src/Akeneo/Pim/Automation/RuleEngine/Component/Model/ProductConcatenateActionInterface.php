<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Model;

use Akeneo\Tool\Bundle\RuleEngineBundle\Model\ActionInterface;

/**
 * Concatenate action interface used in product rules.
 * A concatenate action value is used to concatenate several product source values to a product target value.
 *
 * For example : model-fr_FR-ecommerce + ' ' + color-<all_locales>-<all_channels> to title-fr_FR_ecommerce
 *
 * @author Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 */

interface ProductConcatenateActionInterface extends ActionInterface, FieldImpactActionInterface
{
    public function getSourceCollection(): ProductSourceCollection;

    public function getTarget(): ProductTarget;
}
