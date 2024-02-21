<?php

namespace Oro\Bundle\PimDataGridBundle\Controller;

use Oro\Bundle\DataGridBundle\Datagrid\MetadataParser;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Datagrid controller
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DatagridController
{
    protected MetadataParser $metadata;

    public function __construct(MetadataParser $metadata)
    {
        $this->metadata = $metadata;
    }

    /**
     * Load a datagrid
     */
    public function loadAction(Request $request, string $alias): JsonResponse
    {
        $params = $request->get('params', []);

        return new JsonResponse([
            'metadata' => $this->metadata->getGridMetadata($alias, $params),
            'data' => $this->metadata->getGridData($alias, $params)
        ]);
    }
}
