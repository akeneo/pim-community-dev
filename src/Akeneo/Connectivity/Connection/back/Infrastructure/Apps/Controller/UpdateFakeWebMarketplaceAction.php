<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\Apps\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UpdateFakeWebMarketplaceAction extends AbstractController
{
    private const EMPTY_JSON = '{"total": 0, "offset": 0, "limit": 120, "items": []}';

    public function __invoke(Request $request): Response
    {
        if ('POST' === $request->getMethod()) {
            $jsonEditor = $request->request->get('json-editor', '');
            $jsonEditor = empty($jsonEditor) ? self::EMPTY_JSON : $jsonEditor;

            $sql = <<<SQL
REPLACE INTO pim_configuration(`code`, `values`) VALUES('fake-web-marketplace-json', :fakeWebMarketplaceJson);
SQL;

            $this->getDoctrine()->getConnection()->executeQuery(
                $sql,
                ['fakeWebMarketplaceJson' => $jsonEditor],
                ['fakeWebMarketplaceJson' => \PDO::PARAM_STR]
            );
        }

        $sql = <<<SQL
SELECT `values` FROM pim_configuration WHERE code = 'fake-web-marketplace-json'LIMIT 1;
SQL;

        $jsonEditor = $this->getDoctrine()->getConnection()->executeQuery($sql)->fetchOne();
        $jsonEditor = false === $jsonEditor ? self::EMPTY_JSON : $jsonEditor;

        return $this->render('@PimUI/marketplace/update-fake-web-marketplace.html.twig', [
            'jsonEditor' => json_encode(json_decode($jsonEditor),  JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES),
        ]);
    }
}
