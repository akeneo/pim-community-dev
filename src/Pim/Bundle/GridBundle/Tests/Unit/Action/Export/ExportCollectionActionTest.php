<?php

namespace Pim\Bundle\GridBundle\Tests\Unit\Action\Export;

use Pim\Bundle\GridBundle\Action\Export\ExportCollectionAction;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ExportCollectionActionTest extends ExportActionTestCase
{
    /**
     * {@inheritdoc}
     *
     * @return ExportCollectionAction
     */
    protected function createExportAction(array $options)
    {
        return new ExportCollectionAction($options);
    }

    /**
     * {@inheritdoc}
     */
    public static function constructDataProvider()
    {
        return array(
            'full set of options' => array(
                'expectedOptions' => array(
                    'acl_resource'   => 'root',
                    'baseUrl'        => 'MyBaseUrl',
                    'name'           => 'MyExport',
                    'label'          => 'MyLabel',
                    'icon'           => 'my-icon',
                    'keepParameters' => true
                ),
                'inputOptions' => array(
                    'acl_resource'   => 'root',
                    'baseUrl'        => 'MyBaseUrl',
                    'name'           => 'MyExport',
                    'label'          => 'MyLabel',
                    'icon'           => 'my-icon',
                    'keepParameters' => true
                )
            ),
            'minimum_set_of_options' => array(
                'expectedOptions' => array(
                    'baseUrl'        => 'MyBaseUrl',
                    'keepParameters' => true,
                    'name'           => 'MyExport'
                ),
                'inputOptions' => array(
                    'baseUrl' => 'MyBaseUrl',
                    'name'    => 'MyExport',
                )
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function invalidArgumentExceptionDataProvider()
    {
        return array(
            'name_required' => array(
                array(
                    'acl_resource'   => 'root',
                    'baseUrl'        => 'MyBaseUrl',
                    'label'          => 'MyLabel',
                    'icon'           => 'my-icon',
                    'keepParameters' => true
                )
            ),
            'baseUrl_required' => array(
                array(
                    'acl_resource'   => 'root',
                    'name'           => 'MyExport',
                    'label'          => 'MyLabel',
                    'icon'           => 'my-icon',
                    'keepParameters' => true
                )
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function dataProviderGetters()
    {
        return array(
            array(
                array(
                    'acl_resource'   => 'root',
                    'baseUrl'        => 'MyBaseUrl',
                    'name'           => 'MyExport',
                    'label'          => 'MyLabel',
                    'icon'           => 'my-icon',
                    'keepParameters' => true
                )
            )
        );
    }
}
