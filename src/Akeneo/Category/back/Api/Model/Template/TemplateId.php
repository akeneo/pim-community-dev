<?php

declare(strict_types=1);

namespace Akeneo\Category\Api\Model\Template;

use Akeneo\Category\Domain\ValueObject\TemplateId as TemplateIdFromDomain;
use Webmozart\Assert\Assert;

/**
 * This model represents a category template ID as exposed to the outside of the category bounded context
 * It resembles the eponymous internal domain model but can drift in the future
 *
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TemplateId
{
    public static function fromDomainModel(TemplateIdFromDomain $tId): TemplateId {
        return new TemplateId(
            $tId->getId(),
        );
    }

    public function __construct(private string $id)
    {
        Assert::notWhitespaceOnly($id);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function __toString(): string
    {
        return $this->id;
    }
}
