<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Controller\Public;

use Akeneo\Catalogs\Application\Exception\CatalogNotFoundException;
use Akeneo\Catalogs\Application\Persistence\Attribute\SearchAttributesQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\ProductMappingSchema\ExistsProductMappingSchemaQueryInterface;
use Akeneo\Catalogs\Infrastructure\Validation\ProductMappingSchema;
use Akeneo\Catalogs\ServiceAPI\Messenger\QueryBus;
use Akeneo\Catalogs\ServiceAPI\Query\GetProductMappingSchemaQuery;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetAutoFilledMappingWithAI
{
    public function __construct(
        private QueryBus $queryBus,
        readonly private GetCatalogQueryInterface $getCatalogQuery,
        readonly private ExistsProductMappingSchemaQueryInterface $existsProductMappingSchemaQuery,
        private SearchAttributesQueryInterface $searchAttributesQuery,
    ) {
    }

    public function __invoke(Request $request, string $catalogId): Response
    {

        $scope = $request->query->get('scope', 'ecommerce');
        $locale = (int) $request->query->get('locale', 'en_US');

        try {
            // on a notre mapping
            $catalog = $this->getCatalogQuery->execute($catalogId);

            //+ on veut les targets de notre schema
            /** @var ProductMappingSchema $productMappingSchema */
            $productMappingSchema = $this->queryBus->execute(new GetProductMappingSchemaQuery($catalogId));

            $targets = array_keys(json_decode(json_encode($productMappingSchema), true)['properties']);

//            dump($targets);

            //+ on récupère les attributs du pim
            $attributes = array_column($this->searchAttributesQuery->execute(null,1,200),'code');

//            dd($attributes);


            $content = "Complete the two-column spreadsheet of word association by meaning, between the List A and the List B, the first column of the spreadsheet must include all and only the words from the List A.\n\n";

            $content .= "\n List A :\n";
            $content .= implode("\n", $targets);

            $content .= "\n List B :\n";
            $content .= implode("\n", $attributes);

            $content .= "\n" ;
            $content .= "Here is the format we want : \n";
            $content .= "\n" ;
            $content .= "-------##------\n";
            $content .= "Name##name";

            dump($content);

            //+ on fait un appel openai
            $apiKey = '';
            $url = 'https://api.openai.com/v1/chat/completions';

            $headers = array(
                "Authorization: Bearer {$apiKey}",
                "Content-Type: application/json"
            );

            // Define messages
            $messages = array();
            $messages[] = array("role" => "user", "content" => $content);

            // Define data
            $data = array();
            $data["model"] = "gpt-3.5-turbo";
            $data["messages"] = $messages;
            $data["max_tokens"] = 450;

            // init curl
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

            $result = curl_exec($curl);
            if (curl_errno($curl)) {
                echo 'Error:' . curl_error($curl);
            } else {
                dump(json_decode($result));
            }

            curl_close($curl);

            exit;

        } catch (CatalogNotFoundException) {
            throw new NotFoundHttpException(\sprintf('catalog "%s" does not exist.', $catalogId));
        }

        return new JsonResponse([
            ...$catalog->normalize(),
            'has_product_mapping_schema' => $this->existsProductMappingSchemaQuery->execute($catalogId),
        ]);
    }
}
