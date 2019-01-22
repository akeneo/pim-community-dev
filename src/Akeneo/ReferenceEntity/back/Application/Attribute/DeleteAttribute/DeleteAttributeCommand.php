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

namespace Akeneo\ReferenceEntity\Application\Attribute\DeleteAttribute;

/**
 * @author JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class DeleteAttributeCommand
{
    /** @var string */
    private $attributeIdentifier;

    public function __construct(string $attributeIdentifier)
    {
        $this->attributeIdentifier = $attributeIdentifier;
    }

    public function getAttributeIdentifier(): string
    {
        return $this->attributeIdentifier;
    }
}
