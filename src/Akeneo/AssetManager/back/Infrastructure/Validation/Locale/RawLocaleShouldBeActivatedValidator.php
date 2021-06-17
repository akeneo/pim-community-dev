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

namespace Akeneo\AssetManager\Infrastructure\Validation\Locale;

use Akeneo\AssetManager\Domain\Model\LocaleIdentifier;
use Akeneo\AssetManager\Domain\Model\LocaleIdentifierCollection;
use Akeneo\AssetManager\Domain\Query\Locale\FindActivatedLocalesByIdentifiersInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class RawLocaleShouldBeActivatedValidator extends ConstraintValidator
{
    private FindActivatedLocalesByIdentifiersInterface $findActivatedLocales;

    public function __construct(FindActivatedLocalesByIdentifiersInterface $findActivatedLocales)
    {
        $this->findActivatedLocales = $findActivatedLocales;
    }

    /**
     * @param string|LocaleIdentifier|null $localeIdentifier
     * @param Constraint $constraint
     */
    public function validate($localeIdentifier, Constraint $constraint)
    {
        $this->checkConstraintType($constraint);

        if (null === $localeIdentifier) {
            return;
        }

        if (is_string($localeIdentifier)) {
            $localeIdentifier = LocaleIdentifier::fromCode($localeIdentifier);
        }

        if (!$localeIdentifier instanceof LocaleIdentifier) {
            throw new \InvalidArgumentException(sprintf('Expected argument to be of class "%s", "%s" given',
                LocaleIdentifierCollection::class, get_class($localeIdentifier)));
        }

        $collection = new LocaleIdentifierCollection([$localeIdentifier]);
        $activatedLocaleIdentifiers = $this->findActivatedLocales->find($collection);

        $foundLocale = $activatedLocaleIdentifiers->getIterator()[0] ?? null;

        if (!$foundLocale instanceof LocaleIdentifier || !$localeIdentifier->equals($foundLocale)) {
            $this->context->buildViolation(RawLocaleShouldBeActivated::ERROR_MESSAGE_SINGULAR)
                ->setParameter('locale_identifier', $localeIdentifier->normalize())
                ->atPath('locale')
                ->addViolation();
        }
    }

    /**
     * @throws UnexpectedTypeException
     */
    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof RawLocaleShouldBeActivated) {
            throw new UnexpectedTypeException($constraint, self::class);
        }
    }
}
