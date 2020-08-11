<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\AssetManager\Component\Validator\Constraints;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Query\Asset\AssetExistsInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author    Valentin Dijkstra <valentin.dijkstra@akeneo.com>
 * @copyright 2020 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 *
 * @deprecated Please use Akeneo\Pim\Enrichment\AssetManager\Component\Validator\Constraints\AssetsShouldBelongToAssetFamilyValidator instead
 * @todo Merge master/5.0 remove this class
 */
class AssetShouldExistValidator extends ConstraintValidator
{
    /** @var AssetExistsInterface */
    private $assetExists;

    public function __construct(AssetExistsInterface $assetExists)
    {
        $this->assetExists = $assetExists;
    }

    public function validate($value, Constraint $constraint)
    {
        $this->checkConstraintType($constraint);

        /** @var AssetCode $assetCode */
        foreach ($value->getData() as $assetCode) {
            if (!$this->assetExists->withCode($assetCode)) {
                $this->context->buildViolation(
                    AssetShouldExist::ERROR_MESSAGE,
                    ['%asset_code%' => $assetCode->__toString()]
                )->addViolation();
            }
        }
    }

    /**
     * @throws UnexpectedTypeException
     */
    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof AssetShouldExist) {
            throw new UnexpectedTypeException($constraint, self::class);
        }
    }
}
