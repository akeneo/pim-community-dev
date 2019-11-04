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
class LocalizableValuesValidator extends ConstraintValidator
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $localeRepository;

    public function __construct(IdentifiableObjectRepositoryInterface $localeRepository)
    {
        $this->localeRepository = $localeRepository;
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
            } elseif ($localizableValue->isScopable()) {
                $channelCodes = $locale->getChannels()->map(function (ChannelInterface $channel) {
                    return $channel->getCode();
                })->toArray();
                if (!in_array($localizableValue->getScopeCode(), $channelCodes)) {
                    $this->context->buildViolation(
                        $constraint->invalidLocaleForChannelMessage,
                        [
                            '%attribute_code%' => $localizableValue->getAttributeCode(),
                            '%channel_code%' => $localizableValue->getScopeCode(),
                            '%invalid_locale%' => $localizableValue->getLocaleCode(),
                        ]
                    )->atPath(sprintf('[%s]', $key))->addViolation();
                }
            }
        }
    }
}
