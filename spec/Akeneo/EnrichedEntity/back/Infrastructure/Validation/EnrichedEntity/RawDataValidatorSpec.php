<?php
declare(strict_types=1);

namespace spec\Akeneo\EnrichedEntity\back\Infrastructure\Validation\EnrichedEntity;

use PhpSpec\ObjectBehavior;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

class RawDataValidatorSpec extends ObjectBehavior
{
    public function it_returns_violations_if_identifier_is_null()
    {
        $data = ['identifier' => null, 'labels' => []];
        $violations = $this->validate($data);
        $violations->shouldBeAnInstanceOf(ConstraintViolationList::class);

        $violations->shouldHaveCount(0);
    }

    public function it_returns_violations_if_identifier_is_empty()
    {
        $data = ['identifier' => '', 'labels' => []];
        $violations = $this->validate($data);
        $violations->shouldBeAnInstanceOf(ConstraintViolationList::class);

        $violations->shouldNotHaveCount(0);

        $violations->get(0)->shouldBeAnInstanceOf(ConstraintViolation::class);
        $violations->get(0)->getMessage()->shouldReturn('This value should not be blank.');
        $violations->get(0)->getPropertyPath()->shouldReturn('[identifier]');
    }

    public function it_returns_violations_if_identifier_contains_invalid_characters()
    {
        $data = ['identifier' => 'michel-', 'labels' => []];
        $violations = $this->validate($data);
        $violations->shouldBeAnInstanceOf(ConstraintViolationList::class);

        $violations->shouldNotHaveCount(0);

        $violations->get(0)->shouldBeAnInstanceOf(ConstraintViolation::class);
        $violations->get(0)->getMessage()->shouldReturn('Enriched Entity code may contain only letters, numbers and underscores');
        $violations->get(0)->getPropertyPath()->shouldReturn('[identifier]');
    }

    public function it_does_not_return_any_violation_if_data_are_valid()
    {
        $data = ['identifier' => 'michel', 'labels' => []];
        $violations = $this->validate($data);
        $violations->shouldBeAnInstanceOf(ConstraintViolationList::class);

        $violations->shouldHaveCount(0);
    }

    public function it_does_not_return_any_violation_if_keys_are_missing()
    {
        $data = [];
        $violations = $this->validate($data);
        $violations->shouldBeAnInstanceOf(ConstraintViolationList::class);

        $violations->shouldHaveCount(0);
    }

    public function it_does_not_return_any_violation_if_labels_are_valid()
    {
        $data = [
            'labels' => [
                'fr_FR' => 'Bonjour',
                'en_US' => 'Hola'
            ],
        ];

        $violations = $this->validate($data);
        $violations->shouldBeAnInstanceOf(ConstraintViolationList::class);

        $violations->shouldHaveCount(0);
    }
}
