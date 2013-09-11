<?php

namespace Pim\Bundle\GridBundle\Action\Export;

/**
 * Export collection action for datagrid managers
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExportCollectionAction extends AbstractExportAction
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $options)
    {
        parent::__construct($options);

        $this->assertRequiredOptions(array('baseUrl'));
    }

    /**
     * {@inheritdoc}
     */
    protected function defineDefaultValues()
    {
        if ($this->getOption('keepParameters') === null) {
            $this->options['keepParameters'] = true;
        }
    }
}
