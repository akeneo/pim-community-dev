<?php

namespace Pim\Bundle\DataGridBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Pim\Bundle\UserBundle\Context\UserContext;

use Pim\Bundle\CatalogBundle\Manager\ProductManager;

use Symfony\Component\Serializer\SerializerInterface;

use Pim\Bundle\DataGridBundle\Extension\MassAction\MassActionDispatcher;

use Oro\Bundle\DataGridBundle\Extension\MassAction\MassActionParametersParser;

class FlexibleExportController extends ExportController
{
    public function __construct(
        Request $request,
        MassActionParametersParser $parametersParser,
        MassActionDispatcher $massActionDispatcher,
        SerializerInterface $serializer,
        ProductManager $productManager,
        UserContext $userContext
    ) {
        parent::__construct($request, $parametersParser, $massActionDispatcher, $serializer);

        $this->productManager = $productManager;
        $this->userContext    = $userContext;

        $this->productManager->setLocale($this->getDataLocale());
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

    /**
     * Create filename
     * @return string
     */
    protected function createFilename()
    {
        $dateTime = new \DateTime();

        return sprintf(
            'products_export_%s_%s_%s.csv',
            $this->getDataLocale(),
            $this->productManager->getScope(),
            $dateTime->format('Y-m-d_H:i:s')
        );
    }
}
