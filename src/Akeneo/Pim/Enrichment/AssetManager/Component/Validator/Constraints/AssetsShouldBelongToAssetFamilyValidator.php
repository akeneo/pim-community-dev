<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\AssetManager\Component\Validator\Constraints;

use Akeneo\AssetManager\Domain\Model\Asset\AssetCode;
use Akeneo\AssetManager\Domain\Model\AssetFamily\AssetFamilyIdentifier;
use Akeneo\AssetManager\Domain\Query\Asset\AssetExistsInterface;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author    Valentin Dijkstra <valentin.dijkstra@akeneo.com>
 * @copyright 2020 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class AssetsShouldBelongToAssetFamilyValidator extends ConstraintValidator
{
    /** @var AssetExistsInterface */
    private $assetExists;

    /** @var GetAttributes */
    private $getAttributes;

    public function __construct(AssetExistsInterface $assetExists, GetAttributes $getAttributes)
    {
        $this->assetExists = $assetExists;
        $this->getAttributes = $getAttributes;
    }

    public function validate($value, Constraint $constraint)
    {
        $this->checkConstraintType($constraint);

        $attribute = $this->getAttributes->forCode($value->getAttributeCode());
        $assetFamilyIdentifier = AssetFamilyIdentifier::fromString($attribute->properties()['reference_data_name']);

        /** @var AssetCode $assetCode */
        foreach ($value->getData() as $assetCode) {
            if (!$this->assetExists->withAssetFamilyAndCode($assetFamilyIdentifier, $assetCode)) {
                $this->context->buildViolation(
                    AssetsShouldBelongToAssetFamily::ERROR_MESSAGE,
                    [
                        '%asset_code%' => $assetCode->__toString(),
                        '%asset_family_identifier%' => $assetFamilyIdentifier->__toString(),
                    ]
                )->addViolation();
            }
        }
    }

    /**
     * @throws UnexpectedTypeException
     */
    private function checkConstraintType(Constraint $constraint): void
    {
        if (!$constraint instanceof AssetsShouldBelongToAssetFamily) {
            throw new UnexpectedTypeException($constraint, self::class);
        }
    }
}
