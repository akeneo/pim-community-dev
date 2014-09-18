<?php

namespace Pim\Bundle\BaseConnectorBundle\Writer\File;

use Symfony\Component\Validator\Constraints as Assert;
use Akeneo\Bundle\BatchBundle\Item\ItemWriterInterface;
use Akeneo\Bundle\BatchBundle\Item\AbstractConfigurableStepElement;
use Akeneo\Bundle\BatchBundle\Entity\StepExecution;
use Akeneo\Bundle\BatchBundle\Step\StepExecutionAwareInterface;
use Akeneo\Bundle\BatchBundle\Job\RuntimeErrorException;
use Pim\Bundle\ImportExportBundle\Validator\Constraints\WritableDirectory;

/**
 * Resolve path pattern into final path
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PathPatternResolver
{
    /**
     * Get the final file path from the path with patterns
     *
     * @param string $path
     *
     * @return string
     */
    static public function resolve($path)
    {
        return strtr(
            $path,
            array(
                '%datetime%' => date('Y-m-d_H-i-s'),
                '%tmpdir%'   => sys_get_temp_dir()
            )
        );
    }
}
