<?php

namespace Pim\Bundle\ImportExportBundle\JobLabel;

use Symfony\Component\Translation\TranslatorInterface;

/**
 * Provides a UI translated label for Job and Step, these label keys are build based on a convention
 *
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class TranslatedLabelProvider
{
    /** @var TranslatorInterface */
    protected $translator;

    /** @var string */
    protected $keyPrefix;

    /**
     * @param TranslatorInterface $translator
     * @param string              $keyPrefix
     */
    public function __construct(TranslatorInterface $translator, $keyPrefix)
    {
        $this->translator = $translator;
        $this->keyPrefix = $keyPrefix;
    }

    /**
     * Get the Job label with the given $jobName.
     * Example: "batch_jobs.csv_product_import.label"
     *
     * @param string $jobName
     *
     * @return string
     */
    public function getJobLabel($jobName)
    {
        $id = sprintf(
            '%s.%s.label',
            $this->keyPrefix,
            $jobName
        );

        return $this->translator->trans($id);
    }

    /**
     * Get the Step label with the given $stepName, base on the $jobName.
     * Example: "batch_jobs.csv_product_import.perform.label"
     *
     * @param string $jobName
     * @param string $stepName
     *
     * @return string
     */
    public function getStepLabel($jobName, $stepName)
    {
        $id = sprintf(
            '%s.%s.%s.label',
            $this->keyPrefix,
            $jobName,
            $stepName
        );

        return $this->translator->trans($id);
    }
}
