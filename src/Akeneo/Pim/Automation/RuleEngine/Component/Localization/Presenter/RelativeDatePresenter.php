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
    private const RELATIVE_DATETIME_FORMAT = '/^(now|([+-][0-9]+)\s?(minute|hour|day|week|month|year)s?)$/';

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
            return $this->translator->trans(
                'pimee_catalog_rule.datetime.now',
                [],
                'messages',
                $options['locale'] ?? 'en_US'
            );
        }

        $count = $matches[2];
        $unit = $matches[3];

        return $this->translator->trans(
            \sprintf('pimee_catalog_rule.datetime.relative_date.%s', $unit),
            [
                '%count%' => $count,
                '%absolute_count%' => abs($count),
            ],
            'messages',
            $options['locale'] ?? 'en_US'
        );
    }
}
