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

use Akeneo\AssetManager\Domain\Model\LocaleIdentifierCollection;
use Akeneo\AssetManager\Domain\Query\Locale\FindActivatedLocalesByIdentifiersInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author    Laurent Petard <laurent.petard@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class LocalesShouldBeActivatedValidator extends ConstraintValidator
{
    private FindActivatedLocalesByIdentifiersInterface $findActivatedLocales;

    public function __construct(FindActivatedLocalesByIdentifiersInterface $findActivatedLocales)
    {
        $this->findActivatedLocales = $findActivatedLocales;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($localeIdentifiers, Constraint $constraint)
    {
        $this->checkConstraintType($constraint);
        $this->checkLocaleIdentifierCollectionType($localeIdentifiers);

        if ($localeIdentifiers->isEmpty()) {
            return;
        }

        $activatedLocaleIdentifiers = $this->findActivatedLocales->find($localeIdentifiers);
        $notActivatedLocales = array_diff($localeIdentifiers->normalize(), $activatedLocaleIdentifiers->normalize());

        if (!empty($notActivatedLocales)) {
            $errorMessage = count($notActivatedLocales) > 1
                ? LocalesShouldBeActivated::ERROR_MESSAGE_PLURAL
                : LocalesShouldBeActivated::ERROR_MESSAGE_SINGULAR;

            $this->context->buildViolation($errorMessage)
                ->setParameter('locale_identifier', implode('","', $notActivatedLocales))
                ->atPath('locales')
                ->addViolation();
        }
    }

    /**
     * @throws UnexpectedTypeException
     */
    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof LocalesShouldBeActivated) {
            throw new UnexpectedTypeException($constraint, self::class);
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function checkLocaleIdentifierCollectionType($channelReference): void
    {
        if (null !== $channelReference && !$channelReference instanceof LocaleIdentifierCollection) {
            throw new \InvalidArgumentException(sprintf('Expected argument to be of class "%s", "%s" given',
                LocaleIdentifierCollection::class, get_class($channelReference)));
        }
    }
}
