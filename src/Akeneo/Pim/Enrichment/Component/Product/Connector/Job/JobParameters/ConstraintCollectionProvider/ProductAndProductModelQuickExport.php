<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\ConstraintCollectionProvider;

use Akeneo\Channel\Infrastructure\Component\Validator\Constraint\ActivatedLocale;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Akeneo\Tool\Component\StorageUtils\Validator\Constraints\WritableDirectory;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Constraints for product and product model quick export
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAndProductModelQuickExport implements ConstraintCollectionProviderInterface
{
    /** @var ConstraintCollectionProviderInterface */
    protected $simpleConstraint;

    /** @var array */
    protected $supportedJobNames;

    /**
     * @param ConstraintCollectionProviderInterface $simple
     * @param array                                 $supportedJobNames
     */
    public function __construct(ConstraintCollectionProviderInterface $simple, array $supportedJobNames, private string $filePathExtension)
    {
        $this->simpleConstraint = $simple;
        $this->supportedJobNames = $supportedJobNames;
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraintCollection()
    {
        $baseConstraint = $this->simpleConstraint->getConstraintCollection();
        $constraintFields = $baseConstraint->fields;
        $constraintFilePath = [
            new NotBlank(['groups' => ['Execution', 'FileConfiguration']]),
            new WritableDirectory(['groups' => ['Execution', 'FileConfiguration']]),
            new Regex([
                'pattern' => sprintf('/.\.%s$/', $this->filePathExtension),
                'message' => sprintf('The extension file must be ".%s"', $this->filePathExtension)
            ])
        ];
        $constraintFields['filePathProduct'] = $constraintFilePath;
        $constraintFields['filePathProductModel'] = $constraintFilePath;
        $constraintFields['with_label'] = new Type(
            [
                'type'   => 'bool',
                'groups' => ['Default', 'FileConfiguration'],
            ]
        );
        $constraintFields['header_with_label'] = new Type(
            [
                'type'   => 'bool',
                'groups' => ['Default', 'FileConfiguration'],
            ]
        );
        $constraintFields['with_uuid'] = new Type(
            [
                'type'   => 'bool',
                'groups' => ['Default', 'FileConfiguration'],
            ]
        );
        $constraintFields['file_locale'] = [
            new ActivatedLocale(['groups' => ['Default', 'FileConfiguration']]),
            new Callback(function ($value, ExecutionContextInterface $context) {
                $fields = $context->getRoot();
                if (true === $fields['with_label'] && empty($value)) {
                    $context
                        ->buildViolation('The locale cannot be empty.')
                        ->addViolation();
                }
            })
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
