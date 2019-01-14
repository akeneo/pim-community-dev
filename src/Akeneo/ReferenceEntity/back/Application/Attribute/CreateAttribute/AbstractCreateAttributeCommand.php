<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Application\Attribute\CreateAttribute;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
abstract class AbstractCreateAttributeCommand
{
    /** @var string */
    public $referenceEntityIdentifier;

    /** @var string */
    public $code;

    /** @var array */
    public $labels;

    /** @var bool */
    public $isRequired;

    /** @var bool */
    public $valuePerChannel;

    /** @var bool */
    public $valuePerLocale;
}
