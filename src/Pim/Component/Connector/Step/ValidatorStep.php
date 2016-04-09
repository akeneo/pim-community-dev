<?php

namespace Pim\Component\Connector\Step;

use Akeneo\Component\Batch\Item\AbstractConfigurableStepElement;
use Akeneo\Component\Batch\Model\ConfigurableInterface;
use Akeneo\Component\Batch\Model\Configuration;
use Akeneo\Component\Batch\Model\StepExecution;
use Akeneo\Component\Batch\Step\AbstractStep;
use Pim\Component\Connector\Item\CharsetValidator;

/**
 * Validator Step for imports
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValidatorStep extends AbstractStep
{
    /** @var CharsetValidator */
    protected $charsetValidator;

    /**
     * {@inheritdoc}
     */
    protected function doExecute(StepExecution $stepExecution)
    {
        $this->charsetValidator->setStepExecution($stepExecution);
        $this->charsetValidator->validate();
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration()
    {
        $configuration = [];
        foreach ($this->getConfigurableStepElements() as $stepElement) {
            if ($stepElement instanceof AbstractConfigurableStepElement) {
                foreach ($stepElement->getConfiguration() as $key => $value) {
                    if (!isset($configuration[$key]) || $value) {
                        $configuration[$key] = $value;
                    }
                }
            }
        }

        return $configuration;
    }

    /**
     * {@inheritdoc}
     */
    public function configure(Configuration $configuration)
    {
        foreach ($this->getConfigurableStepElements() as $stepElement) {
            if ($stepElement instanceof ConfigurableInterface) {
                $stepElement->configure($configuration);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setConfiguration(array $config)
    {
        $configuration = new Configuration($config);
        $this->configure($configuration);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfigurableStepElements()
    {
        return ['charsetValidator' => $this->getCharsetValidator()];
    }

    /**
     * @param CharsetValidator $charsetValidator
     */
    public function setCharsetValidator(CharsetValidator $charsetValidator)
    {
        $this->charsetValidator = $charsetValidator;
    }

    /**
     * @return CharsetValidator
     */
    public function getCharsetValidator()
    {
        return $this->charsetValidator;
    }
}
