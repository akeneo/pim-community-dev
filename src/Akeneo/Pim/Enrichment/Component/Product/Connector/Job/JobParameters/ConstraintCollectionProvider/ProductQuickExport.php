<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Connector\Job\JobParameters\ConstraintCollectionProvider;

use Akeneo\Channel\Component\Validator\Constraint\ActivatedLocale;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Constraints for product and product model quick export
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductQuickExport implements ConstraintCollectionProviderInterface
{
    /** @var ConstraintCollectionProviderInterface */
    protected $simpleConstraint;

    /** @var array */
    protected $supportedJobNames;

    /**
     * @param ConstraintCollectionProviderInterface $simple
     * @param array                $supportedJobNames
     */
    public function __construct(ConstraintCollectionProviderInterface $simple, array $supportedJobNames)
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
        $constraintFields['filters'] = [];
        $constraintFields['selected_properties'] = null;
        $constraintFields['selected_locales'] = new Optional(null);
        $constraintFields['with_media'] = new Type('bool');
        $constraintFields['locale'] = new NotBlank(['groups' => 'Execution']);
        $constraintFields['scope'] = new NotBlank(['groups' => 'Execution']);
        $constraintFields['ui_locale'] = new NotBlank(['groups' => 'Execution']);
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
