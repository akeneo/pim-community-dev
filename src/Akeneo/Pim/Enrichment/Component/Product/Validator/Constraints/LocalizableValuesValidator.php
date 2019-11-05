<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Channel\Component\Model\ChannelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class LocalizableValuesValidator extends ConstraintValidator
{
    /** @var IdentifiableObjectRepositoryInterface */
    private $localeRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    private $channelRepository;

    /** @var IdentifiableObjectRepositoryInterface */
    private $attributeRepository;

    public function __construct(
        IdentifiableObjectRepositoryInterface $localeRepository,
        IdentifiableObjectRepositoryInterface $channelRepository,
        IdentifiableObjectRepositoryInterface $attributeRepository
    ) {
        $this->localeRepository = $localeRepository;
        $this->channelRepository = $channelRepository;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param object     $values
     * @param Constraint $constraint
     */
    public function validate($values, Constraint $constraint)
    {
        if (!$constraint instanceof LocalizableValues) {
            throw new UnexpectedTypeException($constraint, LocalizableValues::class);
        }

        if (!($values instanceof WriteValueCollection)) {
            return;
        }

        $localizableValues = $values->filter(
            function (ValueInterface $value): bool {
                return $value->isLocalizable();
            }
        );

        foreach ($localizableValues as $key => $localizableValue) {
            $locale = $this->localeRepository->findOneByIdentifier($localizableValue->getLocaleCode());

            if (null === $locale || !$locale->isActivated()) {
                $this->context->buildViolation(
                    $constraint->nonActiveLocaleMessage,
                    [
                        '%attribute_code%' => $localizableValue->getAttributeCode(),
                        '%invalid_locale%' => $localizableValue->getLocaleCode(),
                    ]
                )->atPath(sprintf('[%s]', $key))->addViolation();

                continue;
            }

            if ($localizableValue->isScopable()) {
                $channel = $this->channelRepository->findOneByIdentifier($localizableValue->getScopeCode());
                if (null !== $channel && !in_array($localizableValue->getLocaleCode(), $channel->getLocaleCodes())) {
                    $this->context->buildViolation(
                        $constraint->invalidLocaleForChannelMessage,
                        [
                            '%attribute_code%' => $localizableValue->getAttributeCode(),
                            '%channel_code%' => $localizableValue->getScopeCode(),
                            '%invalid_locale%' => $localizableValue->getLocaleCode(),
                        ]
                    )->atPath(sprintf('[%s]', $key))->addViolation();

                    continue;
                }
            }

            $attribute = $this->attributeRepository->findOneByIdentifier($localizableValue->getAttributeCode());
            if ($attribute->isLocaleSpecific() && !$attribute->hasLocaleSpecific($locale)) {
                $this->context->buildViolation(
                    $constraint->invalidLocaleSpecificMessage,
                    [
                        '%attribute_code%' => $localizableValue->getAttributeCode(),
                        '%invalid_locale%' => $localizableValue->getLocaleCode(),
                    ]
                )->atPath(sprintf('[%s]', $key))->addViolation();
            }
        }
    }
}
