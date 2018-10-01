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

namespace Akeneo\EnrichedEntity\Application\Record\EditRecord\CommandFactory;

use Akeneo\EnrichedEntity\Domain\Model\Attribute\AbstractAttribute;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class EditRecordValueCommandFactory
{
    /** @var EditValueCommandFactoryRegistryInterface */
    private $valueCommandFactoryRegistry;

    public function __construct(EditValueCommandFactoryRegistryInterface $valueCommandFactoryRegistry)
    {
        $this->valueCommandFactoryRegistry = $valueCommandFactoryRegistry;
    }

    public function create(AbstractAttribute $attribute, array $normalizedValue): AbstractEditValueCommand
    {
        $command = new AbstractEditValueCommand();
        $command->attribute = $attribute;
        $command->channel = $normalizedValue['channel'];
        $command->locale = $normalizedValue['locale'];
        $command->data = $this->valueCommandFactoryRegistry
            ->getFactory($attribute)
            ->create($attribute, $normalizedValue['data']);

        return $command;
    }
}
