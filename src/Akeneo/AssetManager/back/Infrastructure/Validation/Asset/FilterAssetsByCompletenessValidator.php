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

namespace Akeneo\AssetManager\Infrastructure\Validation\Asset;

use Akeneo\AssetManager\Domain\Query\Asset\AssetQuery;
use Akeneo\AssetManager\Domain\Query\Channel\FindActivatedLocalesPerChannelsInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class FilterAssetsByCompletenessValidator extends ConstraintValidator
{
    private FindActivatedLocalesPerChannelsInterface $findActivatedLocalesPerChannels;

    private ?array $activatedLocalesPerChannels = null;

    public function __construct(FindActivatedLocalesPerChannelsInterface $findActivatedLocalesPerChannels)
    {
        $this->findActivatedLocalesPerChannels = $findActivatedLocalesPerChannels;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($assetQuery, Constraint $constraint)
    {
        $this->checkAssetQueryType($assetQuery);
        $this->checkConstraintType($constraint);

        if ($assetQuery->hasFilter('complete')) {
            $filter = $assetQuery->getFilter('complete');
            $this->validateChannel($filter);
            $this->validateLocales($filter);
        }
    }

    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof FilterAssetsByCompleteness) {
            throw new UnexpectedTypeException($constraint, FilterAssetsByCompleteness::class);
        }
    }

    private function checkAssetQueryType($assetQuery): void
    {
        if (!$assetQuery instanceof AssetQuery) {
            throw new \InvalidArgumentException(sprintf('Expected argument to be of class "%s"', AssetQuery::class));
        }
    }

    private function validateChannel(array $filter): void
    {
        $activatedLocalesPerChannels = $this->getActivatedLocalesPerChannels();
        $channel = $filter['context']['channel'];

        if (!array_key_exists($channel, $activatedLocalesPerChannels)) {
            $this->context->buildViolation(FilterAssetsByCompleteness::CHANNEL_SHOULD_EXIST)
                ->setParameter('channel_identifier', $channel)
                ->atPath('complete.channel')
                ->addViolation();
        }
    }

    private function validateLocales(array $filter): void
    {
        $activatedLocalesPerChannels = $this->getActivatedLocalesPerChannels();
        $channel = $filter['context']['channel'];
        $locales = $filter['context']['locales'];

        $activatedLocales = $activatedLocalesPerChannels[$channel] ?? null;

        if (null === $activatedLocales) {
            return;
        }

        $notActivatedLocales = array_diff($locales, $activatedLocales);

        if (!empty($notActivatedLocales)) {
            $errorMessage = count($notActivatedLocales) > 1
                ? FilterAssetsByCompleteness::LOCALES_SHOULD_BE_ACTIVATED
                : FilterAssetsByCompleteness::LOCALE_SHOULD_BE_ACTIVATED;

            $this->context->buildViolation($errorMessage)
                ->setParameter('locale_identifier', implode('","', $notActivatedLocales))
                ->setParameter('channel_identifier', $channel)
                ->atPath('complete.locales')
                ->addViolation();
        }
    }

    private function getActivatedLocalesPerChannels(): array
    {
        if (null === $this->activatedLocalesPerChannels) {
            $this->activatedLocalesPerChannels = $this->findActivatedLocalesPerChannels->findAll();
        }

        return $this->activatedLocalesPerChannels;
    }
}
