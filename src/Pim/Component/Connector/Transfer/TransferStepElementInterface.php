<?php

namespace Pim\Component\Connector\Transfer;

use Akeneo\Component\Batch\Step\StepExecutionAwareInterface;

/**
 * Step element able to transfer files. For example, it can be used for instance to:
 *  - move files to the local expected directory after an export
 *  - move files to another server by SFTP after an import
 *  - retrieve files that are located on another server before an import
 *
 * If you plan to transfer files to/from another server,
 * you'd better take a look at http://flysystem.thephpleague.com/ ;)
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface TransferStepElementInterface extends StepExecutionAwareInterface
{
    /**
     * Transfer files
     *
     * @throws TransferException
     */
    public function transfer();

    /**
     * @return string
     */
    public function getOriginalFilename();
}
