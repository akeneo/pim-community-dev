<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2019 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\tests\back\Acceptance\Persistence\InMemory\Query;

use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Model\Read\Attribute as FranklinAttribute;
use Akeneo\Pim\Automation\FranklinInsights\Domain\FamilyAttribute\Repository\AttributeRepositoryInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;

/**
 * @author    Anael Chardan <anael.chardan@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 */
final class InMemoryGetAttributes implements GetAttributes
{
    /** @var AttributeRepositoryInterface */
    private $attributeRepository;

    public function __construct(AttributeRepositoryInterface $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function forCodes(array $attributeCodes): array
    {
        return array_map(function (FranklinAttribute $franklinAttribute) : Attribute {
            return new Attribute((string) $franklinAttribute->getCode(), $franklinAttribute->getType(), []);
        }, $this->attributeRepository->findByCodes($attributeCodes));
    }
}
