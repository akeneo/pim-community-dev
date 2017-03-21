<?php

namespace Pim\Component\Connector\Validator\Constraints;

use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for the product export builder structure filter about locales.
 * Attributes filter structure restricts the attribute columns to export.
 *
 * This validator checks if given locales exist and if they belong to selected
 * scope.
 *
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FilterStructureLocaleValidator extends ConstraintValidator
{
    /** @var ChannelRepositoryInterface */
    protected $channelRepository;

    /**
     * @param ChannelRepositoryInterface $channelRepository
     */
    public function __construct(ChannelRepositoryInterface $channelRepository)
    {
        $this->channelRepository = $channelRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value['scope'] || null === $value['locales']) {
            return;
        }

        $filterStructureScope = $value['scope'];
        $filterStructureLocales = $value['locales'];

        $scope = $this->channelRepository->findOneByIdentifier($filterStructureScope);
        $localesCodes = [];

        if (null !== $scope) {
            $localesCodes = $scope->getLocaleCodes();
        }

        $errorCount = 0;
        foreach ($filterStructureLocales as $localeCode) {
            if (!in_array($localeCode, $localesCodes)) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('%localeCode%', $localeCode)
                    ->atPath(sprintf('[locales][%d]', $errorCount))
                    ->addViolation();
                $errorCount++;
            }
        }
    }
}
