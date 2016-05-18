<?php

namespace Pim\Component\Connector\Job\JobParameters\ConstraintCollectionProvider;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Constraints for variant group XLSX export
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupXlsxExport implements ConstraintCollectionProviderInterface
{
    /** @var ConstraintCollectionProviderInterface */
    protected $simpleProvider;

    /** @var array */
    protected $supportedJobNames;

    /**
     * @param ConstraintCollectionProviderInterface $simpleProvider
     * @param array                                 $supportedJobNames
     */
    public function __construct(ConstraintCollectionProviderInterface $simpleProvider, array $supportedJobNames)
    {
        $this->simpleProvider = $simpleProvider;
        $this->supportedJobNames = $supportedJobNames;
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraintCollection()
    {
        $baseConstraint = $this->simpleProvider->getConstraintCollection();
        $constraintFields = $baseConstraint->fields;
        $constraintFields['decimalSeparator'] = new NotBlank();
        $constraintFields['dateFormat'] = new NotBlank();
        $constraintFields['linesPerFile'] = [
            new NotBlank(),
            new GreaterThan(1)
        ];

        return new Collection(['fields' => $constraintFields]);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job)
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
