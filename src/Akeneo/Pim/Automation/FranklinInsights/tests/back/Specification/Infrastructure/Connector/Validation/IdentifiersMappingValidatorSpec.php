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

namespace Specification\Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Validation;

use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Model\IdentifiersMapping;
use Akeneo\Pim\Automation\FranklinInsights\Domain\IdentifierMapping\Repository\IdentifiersMappingRepositoryInterface;
use Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Connector\Validation\IdentifiersMappingValidator;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Bundle\BatchBundle\Item\Validator\ValidationException;
use Akeneo\Tool\Bundle\BatchBundle\Item\Validator\ValidatorInterface;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

/**
 * @author Mathias METAYER <mathias.metayer@akeneo.com>
 */
class IdentifiersMappingValidatorSpec extends ObjectBehavior
{
    public function let(IdentifiersMappingRepositoryInterface $identifiersMappingRepo): void
    {
        $this->beConstructedWith($identifiersMappingRepo);
    }

    public function it_is_a_validator(): void
    {
        $this->shouldImplement(ValidatorInterface::class);
    }

    public function it_is_an_identifiers_mapping_validator(): void
    {
        $this->shouldHaveType(IdentifiersMappingValidator::class);
    }

    public function it_throws_an_exception_if_identifiers_mapping_is_empty($identifiersMappingRepo): void
    {
        $identifiersMappingRepo->find()->willReturn(new IdentifiersMapping());
        $this->shouldThrow(new ValidationException('Identifiers mapping is empty'))
             ->during('validate', [Argument::any()]);
    }

    public function it_does_nothing_if_identifiers_mapping_is_valid(
        $identifiersMappingRepo,
        AttributeInterface $asin
    ): void {
        $identifiersMapping = new IdentifiersMapping();
        $identifiersMapping->map('asin', $asin->getWrappedObject());
        $identifiersMappingRepo->find()->willReturn($identifiersMapping);
        $this->validate(Argument::any())->shouldReturn(null);
    }
}
