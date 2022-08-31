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

namespace Akeneo\ReferenceEntity\Application\Record\EditRecord\CommandFactory;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\RecordCollectionAttribute;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditRecordCollectionValueCommand extends AbstractEditValueCommand
{
    /**
     * @param string[] $recordCodes
     */
    public function __construct(
        RecordCollectionAttribute $attribute,
        ?string $channel,
        ?string $locale,
        public array $recordCodes
    ) {
        parent::__construct($attribute, $channel, $locale);
    }
}
