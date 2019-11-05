<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Model\WriteValueCollection;
use Akeneo\Tool\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates the existence of channels used in product values.
 *
 * @author    Nicolas Marniesse <nicolas.marniesse@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ScopableValuesValidator extends ConstraintValidator
{
    /** @var IdentifiableObjectRepositoryInterface */
    private $channelRepository;

    public function __construct(IdentifiableObjectRepositoryInterface $channelRepository)
    {
        $this->channelRepository = $channelRepository;
    }

    public function validate($values, Constraint $constraint)
    {
        if (!$constraint instanceof ScopableValues) {
            throw new UnexpectedTypeException($constraint, ScopableValues::class);
        }

        if (!($values instanceof WriteValueCollection)) {
            return;
        }

        foreach ($values as $key => $value) {
            if ($value->isScopable() && null === $this->channelRepository->findOneByIdentifier($value->getScopeCode())) {
                $this->context->buildViolation(
                    $constraint->unknownScopeMessage,
                    [
                        '%attribute_code%' => $value->getAttributeCode(),
                        '%channel%' => $value->getScopeCode(),
                    ]
                )->atPath(sprintf('[%s]', $key))->addViolation();
            }
        }
    }
}
