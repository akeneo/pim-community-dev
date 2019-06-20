<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\UrlAttribute;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
class EditUrlValueCommand extends AbstractEditValueCommand
{
    /** @var string */
    public $url;

    public function __construct(UrlAttribute $attribute, ?string $channel, ?string $locale, string $url)
    {
        parent::__construct($attribute, $channel, $locale);

        $this->url = $url;
    }
}
