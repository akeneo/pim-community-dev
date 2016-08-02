<?php

namespace Pim\Bundle\ImportExportBundle\Validator\Constraints;

use Pim\Component\Catalog\Model\ChannelInterface;
use Pim\Component\Catalog\Repository\ChannelRepositoryInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
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

        foreach ($filterStructureLocales as $localeCode) {
            if (!in_array($localeCode, $localesCodes)) {
                $this->context->buildViolation($constraint->message)
                    ->setParameter('%localeCode%', $localeCode)
                    ->atPath('[locales]')
                    ->addViolation();
            }
        }
    }
}
