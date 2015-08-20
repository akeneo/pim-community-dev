<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ProductAssetBundle\Validator\Constraints;

use Akeneo\Component\StorageUtils\Repository\IdentifiableObjectRepositoryInterface;
use PimEnterprise\Component\ProductAsset\Model\AssetInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for localizable asset
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 */
class LocalizableAssetValidator extends ConstraintValidator
{
    /** @var IdentifiableObjectRepositoryInterface */
    protected $localeRepository;

    /**
     * @param IdentifiableObjectRepositoryInterface $localeRepository
     */
    public function __construct(IdentifiableObjectRepositoryInterface $localeRepository)
    {
        $this->localeRepository = $localeRepository;
    }

    /**
     * @param object     $asset
     * @param Constraint $constraint
     */
    public function validate($asset, Constraint $constraint)
    {
        /** @var AssetInterface */
        if ($asset instanceof AssetInterface) {
            $references    = $asset->getReferences();
            $nbReferences  = count($references);
            $nbLocalizable = 0;

            foreach ($references as $reference) {
                $locale = $reference->getLocale();
                if (null !== $locale) {
                    $nbLocalizable++;
                }
            }

            if (1 === $nbReferences && 0 !== $nbLocalizable) {
                $this->addUnexpectedLocaleViolation($constraint, $asset);
            }

            if ($nbReferences > 1 && $nbReferences !== $nbLocalizable) {
                $this->addExpectedLocaleViolation($constraint, $asset);
            }
        }
    }

    /**
     * @param string $localeCode
     *
     * @return bool
     */
    protected function doesLocaleExist($localeCode)
    {
        $locale = $this->localeRepository->findOneByIdentifier($localeCode);

        return null !== $locale;
    }

    /**
     * @param LocalizableAsset $constraint
     * @param AssetInterface   $asset
     */
    protected function addExpectedLocaleViolation(LocalizableAsset $constraint, AssetInterface $asset)
    {
        $this->context->buildViolation(
            $constraint->expectedLocaleMessage,
            [
                '%asset%' => $asset->getCode()
            ]
        )->addViolation();
    }

    /**
     * @param LocalizableAsset $constraint
     * @param AssetInterface   $asset
     * @param string           $localeCode
     */
    protected function addUnexistingLocaleViolation(
        LocalizableAsset $constraint,
        AssetInterface $asset,
        $localeCode
    ) {
        $this->context->buildViolation(
            $constraint->inexistingLocaleMessage,
            [
                '%asset%'  => $asset->getCode(),
                '%locale%' => $localeCode
            ]
        )->addViolation();
    }

    /**
     * @param LocalizableAsset $constraint
     * @param AssetInterface   $asset
     */
    protected function addUnexpectedLocaleViolation(LocalizableAsset $constraint, AssetInterface $asset)
    {
        $this->context->buildViolation(
            $constraint->unexpectedLocaleMessage,
            [
                '%asset%' => $asset->getCode()
            ]
        )->addViolation();
    }
}
