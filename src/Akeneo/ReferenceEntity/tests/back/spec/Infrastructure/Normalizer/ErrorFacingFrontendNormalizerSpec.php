<?php

declare(strict_types=1);

namespace spec\Akeneo\ReferenceEntity\Infrastructure\Normalizer;

use Akeneo\ReferenceEntity\Domain\Exception\UserFacingError;
use PhpSpec\ObjectBehavior;
use Symfony\Contracts\Translation\TranslatorInterface;

class ErrorFacingFrontendNormalizerSpec extends ObjectBehavior
{
    public function let(TranslatorInterface $translator)
    {
        $this->beConstructedWith($translator);
    }

    public function it_normalizes_an_user_facing_error(
        TranslatorInterface $translator,
        UserFacingError $error
    ) {
        $translator->trans('a_translation_id', ['parameter' => 'value'], 'validators')->willReturn('A translation');
        $error->translationKey()->willReturn('a_translation_id');
        $error->translationParameters()->willReturn([
            'parameter' => 'value'
        ]);

        $this->normalize($error, '[code]')->shouldReturn(
            [
                [
                    'messageTemplate' => 'a_translation_id',
                    'parameters' => ['parameter' => 'value'],
                    'message' => 'A translation',
                    'propertyPath' => '[code]',
                ],
            ]
        );

    }
}
