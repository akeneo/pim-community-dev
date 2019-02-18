<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AbstractAttribute;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AbstractEditValueCommand
{
    /** @var AbstractAttribute */
    public $attribute;

    /** @var string|null */
    public $channel;

    /** @var string|null */
    public $locale;

    public function __construct(AbstractAttribute $attribute, ?string $channel, ?string $locale)
    {
        $this->attribute = $attribute;
        $this->channel = $channel;
        $this->locale = $locale;
    }
}
