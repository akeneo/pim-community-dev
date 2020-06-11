<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Localization\Presenter;

use Akeneo\Tool\Component\Localization\Presenter\PresenterInterface;
use Symfony\Component\Translation\TranslatorInterface;

class RelativeDatePresenter implements PresenterInterface
{
    private const RELATIVE_DATETIME_FORMAT = '/^(now|([+-])([0-9]+)\s?(second|minute|hour|day|week|month|year)s?)$/';

    /** @var PresenterInterface */
    private $basePresenter;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(PresenterInterface $basePresenter, TranslatorInterface $translator)
    {
        $this->basePresenter = $basePresenter;
        $this->translator = $translator;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($attributeType)
    {
        return $this->basePresenter->supports($attributeType);
    }

    public function present($value, array $options = [])
    {
        $presented = $this->basePresenter->present($value, $options);
        if (true !== ($options['present_relative_date'] ?? false)) {
            return $presented;
        }

        $relativeDate = $this->presentRelativeDate($value, $options);
        if (null !== $relativeDate) {
            return \sprintf('%s (%s)', $presented, $relativeDate);
        }

        return $presented;
    }

    private function presentRelativeDate($value, $options): ?string
    {
        $matches = [];
        if (!is_string($value) || 1 !== preg_match(self::RELATIVE_DATETIME_FORMAT, trim($value), $matches)) {
            return null;
        }

        if ('now' === $matches[0]) {
            return $this->translator->trans('pim_localization.relative_date.now');
        }

        $time = '+' === $matches[2] ? 'future' : 'past';
        $count = $matches[3];
        $unit = $matches[4];

        $timeframe = $this->translator->transChoice(
            \sprintf('pim_localization.time_unit.%s', $unit),
            $count,
            [],
            'messages',
            $options['locale'] ?? 'en_US'
        );

        return $this->translator->trans(
            \sprintf('pim_localization.relative_date.%s', $time),
            [
                '{{ timeframe }}' => $timeframe,
            ],
            'messages',
            $options['locale'] ?? 'en_US',
        );
    }
}
