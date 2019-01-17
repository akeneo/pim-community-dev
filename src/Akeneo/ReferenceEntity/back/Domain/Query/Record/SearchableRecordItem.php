<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Domain\Query\Record;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class SearchableRecordItem
{
    /** @var string */
    public $identifier;

    /** @var string */
    public $referenceEntityIdentifier;
    
    /** @var string */
    public $code;

    /** @var array */
    public $labels;
    
    /** @var array */
    public $values;
}
