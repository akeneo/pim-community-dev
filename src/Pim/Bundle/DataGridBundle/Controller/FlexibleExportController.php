<?php

namespace Pim\Bundle\DataGridBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser;

use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Pim\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher;
use Pim\Bundle\UserBundle\Context\UserContext;

/**
 * Override ExportController for flexible exports
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FlexibleExportController extends ExportController
{
    /**
     * Constructor
     *
     * @param Request $request
     * @param MassActionParametersParser $parametersParser
     * @param MassActionDispatcher $massActionDispatcher
     * @param SerializerInterface $serializer
     * @param ProductManager $productManager
     * @param UserContext $userContext
     */
    public function __construct(
        Request $request,
        MassActionParametersParser $parametersParser,
        MassActionDispatcher $massActionDispatcher,
        SerializerInterface $serializer,
        ProductManager $productManager,
        UserContext $userContext
    ) {
        parent::__construct(
            $request,
            $parametersParser,
            $massActionDispatcher,
            $serializer
        );

        $this->productManager = $productManager;
        $this->userContext    = $userContext;

        $this->productManager->setLocale($this->getDataLocale());
    }

    /**
     * {@inheritdoc}
     */
    protected function createFilename()
    {
        $dateTime = new \DateTime();

        return sprintf(
            'products_export_%s_%s_%s.%s',
            $this->getDataLocale(),
            $this->productManager->getScope(),
            $dateTime->format('Y-m-d_H-i-s'),
            $this->getFormat()
        );
    }

    /**
     * Get data locale code
     *
     * @return string
     */
    protected function getDataLocale()
    {
        return $this->userContext->getCurrentLocaleCode();
    }
}
