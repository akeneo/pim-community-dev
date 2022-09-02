<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\TailoredImport\Infrastructure\Connector;

use Akeneo\Platform\Bundle\ImportExportBundle\Infrastructure\Validation\Storage;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\FileKey;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\ImportStructure;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\IsValidFileStructure;
use Akeneo\Tool\Component\Batch\Job\JobInterface;
use Akeneo\Tool\Component\Batch\Job\JobParameters\ConstraintCollectionProviderInterface;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;

class ConstraintCollectionProvider implements ConstraintCollectionProviderInterface
{
    public function __construct(
        private ConstraintCollectionProviderInterface $simpleProvider,
        /** @var string[] */
        private array $supportedJobNames,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function getConstraintCollection(): Collection
    {
        $baseConstraint = $this->simpleProvider->getConstraintCollection();
        $constraintFields = $baseConstraint->fields;

        $constraintFields['import_structure'] = new ImportStructure();
        $constraintFields['file_key'] = new FileKey();
        $constraintFields['file_structure'] = new IsValidFileStructure();
        $constraintFields['error_action'] = new Choice(['skip_value', 'skip_product']);
        $constraintFields['storage'] = new Storage(['xlsx']);

        return new Collection(['fields' => $constraintFields]);
    }

    /**
     * {@inheritdoc}
     */
    public function supports(JobInterface $job): bool
    {
        return in_array($job->getName(), $this->supportedJobNames);
    }
}
