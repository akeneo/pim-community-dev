<?php

namespace Pim\Component\Connector\Job\JobParameters\ConstraintCollectionProvider;

use Akeneo\Component\Batch\Job\JobInterface;
use Akeneo\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Pim\Component\Connector\Validator\Constraints\Channel;
use Pim\Component\Connector\Validator\Constraints\FilterData;
use Pim\Component\Connector\Validator\Constraints\FilterStructureAttribute;
use Pim\Component\Connector\Validator\Constraints\FilterStructureLocale;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;

/**
 * Constraints for product CSV export
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductCsvExport implements ConstraintCollectionProviderInterface
{
    /** @var ConstraintCollectionProviderInterface */
    protected $simpleProvider;

    /** @var array */
    protected $supportedJobNames;

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
        $constraintFields['decimalSeparator'] = new NotBlank();
        $constraintFields['dateFormat'] = new NotBlank();
        $constraintFields['filters'] = [new FilterData(), new Collection(
            [
                'fields' => [
                    'structure' => [
                        new FilterStructureLocale(),
                        new Collection(
                            [
                                'fields' => [
                                    'locales'    => new NotBlank(),
                                    'scope'      => new Channel(),
                                    'attributes' => new FilterStructureAttribute(),
                                ],
                                'allowMissingFields' => true,
                            ]
                        ),
                    ],
                ],
                'allowExtraFields' => true,
            ]
        )];
        $constraintFields['with_media'] = new Type('bool');

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
