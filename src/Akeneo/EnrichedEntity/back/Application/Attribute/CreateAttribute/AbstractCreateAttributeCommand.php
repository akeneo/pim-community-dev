<?php

declare(strict_types=1);

namespace Akeneo\EnrichedEntity\Application\Attribute\CreateAttribute;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
abstract class AbstractCreateAttributeCommand
{
    /** @var array */
    public $identifier;

    /** @var string */
    public $enrichedEntityIdentifier;

    /** @var string */
    public $code;

    /** @var array */
    public $labels;

    /** @var int */
    public $order;

    /** @var bool */
    public $required;

    /** @var bool */
    public $valuePerChannel;

    /** @var bool */
    public $valuePerLocale;
}
