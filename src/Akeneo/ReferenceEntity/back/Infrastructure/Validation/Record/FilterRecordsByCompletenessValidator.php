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

namespace Akeneo\ReferenceEntity\Infrastructure\Validation\Record;

use Akeneo\ReferenceEntity\Domain\Query\Channel\FindActivatedLocalesPerChannelsInterface;
use Akeneo\ReferenceEntity\Domain\Query\Record\RecordQuery;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class FilterRecordsByCompletenessValidator extends ConstraintValidator
{
    /** @var FindActivatedLocalesPerChannelsInterface */
    private $findActivatedLocalesPerChannels;

    /** @var null|array */
    private $activatedLocalesPerChannels;

    public function __construct(FindActivatedLocalesPerChannelsInterface $findActivatedLocalesPerChannels)
    {
        $this->findActivatedLocalesPerChannels = $findActivatedLocalesPerChannels;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($recordQuery, Constraint $constraint)
    {
        $this->checkRecordQueryType($recordQuery);
        $this->checkConstraintType($constraint);

        if ($recordQuery->hasFilter('complete')) {
            $filter = $recordQuery->getFilter('complete');
            $this->validateChannel($filter);
            $this->validateLocales($filter);
        }
    }

    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof FilterRecordsByCompleteness) {
            throw new UnexpectedTypeException($constraint, FilterRecordsByCompleteness::class);
        }
    }

    private function checkRecordQueryType($recordQuery): void
    {
        if (!$recordQuery instanceof RecordQuery) {
            throw new \InvalidArgumentException(sprintf('Expected argument to be of class "%s"', RecordQuery::class));
        }
    }

    private function validateChannel(array $filter): void
    {
        $activatedLocalesPerChannels = $this->getActivatedLocalesPerChannels();
        $channel = $filter['context']['channel'];

        if (!array_key_exists($channel, $activatedLocalesPerChannels)) {
            $this->context->buildViolation(FilterRecordsByCompleteness::CHANNEL_SHOULD_EXIST)
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
                ? FilterRecordsByCompleteness::LOCALES_SHOULD_BE_ACTIVATED
                : FilterRecordsByCompleteness::LOCALE_SHOULD_BE_ACTIVATED;

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
            $this->activatedLocalesPerChannels = ($this->findActivatedLocalesPerChannels)();
        }

        return $this->activatedLocalesPerChannels;
    }
}
