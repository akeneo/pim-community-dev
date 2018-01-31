<?php

declare(strict_types=1);

namespace Pim\Component\Connector\Job\JobParameters\ConstraintCollectionProvider;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Pim\Component\Catalog\Validator\Constraints\Channel;
use Pim\Component\Connector\Validator\Constraints\FilterData;
use Pim\Component\Connector\Validator\Constraints\FilterStructureAttribute;
use Pim\Component\Connector\Validator\Constraints\FilterStructureLocale;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

/**
 * Constraints for product model CSV export
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductModelCsvExport implements ConstraintCollectionProviderInterface
{
    /** @var ConstraintCollectionProviderInterface */
    private $simpleProvider;

    /** @var array */
    private $supportedJobNames;

    /**
     * @param ConstraintCollectionProviderInterface $simpleCsv
     * @param array                                 $supportedJobNames
     */
    public function __construct(ConstraintCollectionProviderInterface $simpleCsv, array $supportedJobNames)
    {
        $this->simpleProvider = $simpleCsv;
        $this->supportedJobNames = $supportedJobNames;
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraintCollection()
    {
        $baseConstraint = $this->simpleProvider->getConstraintCollection();
        $constraintFields = $baseConstraint->fields;
        $constraintFields['decimalSeparator'] = new NotBlank(['groups' => ['Default', 'FileConfiguration']]);
        $constraintFields['dateFormat'] = new NotBlank(['groups' => ['Default', 'FileConfiguration']]);
        $constraintFields['with_media'] = new Type(
            [
                'type'   => 'bool',
                'groups' => ['Default', 'FileConfiguration'],
            ]
        );

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
