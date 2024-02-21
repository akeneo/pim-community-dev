<?php
declare(strict_types=1);

namespace spec\Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write;

use Akeneo\Connectivity\Connection\Domain\ErrorManagement\ErrorTypes;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\ApiErrorCollection;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\ApiErrorInterface;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\BusinessError;
use Akeneo\Connectivity\Connection\Domain\ErrorManagement\Model\Write\TechnicalError;
use PhpSpec\ObjectBehavior;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ApiErrorCollectionSpec extends ObjectBehavior
{
    public function it_is_instantiated_with_valid_error_types(): void
    {
        $this->getSorted()->shouldMatchErrorTypes();
    }

    public function it_can_be_constructed_with_initial_errors(): void
    {
        $businessError = new BusinessError('{"message": "error"}');
        $this->beConstructedWith([$businessError]);

        $this->count(ErrorTypes::BUSINESS)->shouldBeEqualTo(1);
    }

    public function it_adds_new_errors(): void
    {
        $businessErrorA = new BusinessError('{"message": "error"}');
        $businessErrorB = new BusinessError('{"message": "error"}');
        $technicalErrorA = new TechnicalError('{"message": "error"}');
        $technicalErrorB = new TechnicalError('{"message": "error"}');

        $this->count()->shouldBeEqualTo(0);

        $this->add($businessErrorA);
        $this->count()->shouldBeEqualTo(1);
        $this->add($businessErrorB);
        $this->count()->shouldBeEqualTo(2);
        $this->add($technicalErrorA);
        $this->count()->shouldBeEqualTo(3);
        $this->add($technicalErrorB);
        $this->count()->shouldBeEqualTo(4);
    }

    public function it_adds_and_sorts_new_errors(): void
    {
        $businessErrorA = new BusinessError('{"message": "error"}');
        $businessErrorB = new BusinessError('{"message": "error"}');
        $technicalErrorA = new TechnicalError('{"message": "error"}');
        $technicalErrorB = new TechnicalError('{"message": "error"}');

        $this->count(ErrorTypes::BUSINESS)->shouldBeEqualTo(0);
        $this->count(ErrorTypes::TECHNICAL)->shouldBeEqualTo(0);

        $this->add($businessErrorA);
        $this->count(ErrorTypes::BUSINESS)->shouldBeEqualTo(1);
        $this->count(ErrorTypes::TECHNICAL)->shouldBeEqualTo(0);
        $this->add($businessErrorB);
        $this->count(ErrorTypes::BUSINESS)->shouldBeEqualTo(2);
        $this->count(ErrorTypes::TECHNICAL)->shouldBeEqualTo(0);
        $this->add($technicalErrorA);
        $this->count(ErrorTypes::BUSINESS)->shouldBeEqualTo(2);
        $this->count(ErrorTypes::TECHNICAL)->shouldBeEqualTo(1);
        $this->add($technicalErrorB);
        $this->count(ErrorTypes::BUSINESS)->shouldBeEqualTo(2);
        $this->count(ErrorTypes::TECHNICAL)->shouldBeEqualTo(2);
    }

    public function it_provides_api_errors_by_type(): void
    {
        $businessErrorA = new BusinessError('{"message": "error"}');
        $businessErrorB = new BusinessError('{"message": "error"}');
        $technicalErrorA = new TechnicalError('{"message": "error"}');
        $technicalErrorB = new TechnicalError('{"message": "error"}');

        $this->beConstructedWith([$businessErrorA, $businessErrorB, $technicalErrorA, $technicalErrorB]);

        $this->getByType(ErrorTypes::BUSINESS)->shouldReturn([$businessErrorA, $businessErrorB]);
        $this->getByType(ErrorTypes::TECHNICAL)->shouldReturn([$technicalErrorA, $technicalErrorB]);
    }

    public function it_provides_all_sorted_api_errors(): void
    {
        $businessErrorA = new BusinessError('{"message": "error"}');
        $businessErrorB = new BusinessError('{"message": "error"}');
        $technicalErrorA = new TechnicalError('{"message": "error"}');
        $technicalErrorB = new TechnicalError('{"message": "error"}');

        $this->beConstructedWith([$businessErrorA, $businessErrorB, $technicalErrorA, $technicalErrorB]);

        $this->getSorted()->shouldReturn(
            [
                ErrorTypes::BUSINESS =>  [$businessErrorA, $businessErrorB],
                ErrorTypes::TECHNICAL => [$technicalErrorA, $technicalErrorB],
            ]
        );
    }

    public function it_accepts_only_api_errors_as_initial_parameters(): void
    {
        $this->beConstructedWith([new \DateTime()]);
        $this->shouldThrow(
            new \InvalidArgumentException(
                \sprintf(
                    'Class "%s" accepts only "%s" in the collection.',
                    ApiErrorCollection::class,
                    ApiErrorInterface::class
                )
            )
        )->duringInstantiation();
    }

    public function it_does_not_accept_to_provide_errors_by_type_it_does_not_know(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('getByType', ['any_type']);
    }

    public function it_does_not_accept_to_count_error_types_it_does_not_know(): void
    {
        $this->shouldThrow(\InvalidArgumentException::class)->during('count', ['any_type']);
    }

    /**
     * @return array{matchErrorTypes: Closure(mixed):bool}
     */
    public function getMatchers(): array
    {
        return [
            'matchErrorTypes' => fn ($types): bool => \array_keys($types) === ErrorTypes::getAll(),
        ];
    }
}
