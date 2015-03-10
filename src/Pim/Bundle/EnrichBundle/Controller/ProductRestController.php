<?php

namespace Pim\Bundle\EnrichBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Product controller
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductRestController
{
    public function getAction($id)
    {
        return new JsonResponse(json_decode('{
            "family":null,
            "groups":[],
            "categories":["2014_collection"],
            "enabled":true,
            "associations":[],
            "values": {
                "sku":[
                    {"locale":null,"scope":null,"value":"sandals"}
                ],
                "name":[
                    {"locale":"en_US","scope":null,"value":"My sandals"}
                ],
                "description":[
                    {"locale":"en_US","scope":"mobile","value":"My great sandals"},
                    {"locale":"en_US","scope":"tablet","value":"My great new sandals"}
                ],
                "price":[
                    {"locale":null,"scope":null,"value":[
                        {"data":"20.00","currency":"EUR"},
                        {"data":"30.00","currency":"USD"}
                    ]}
                ]
            },
            "resource":"{baseUrl}/api/rest/products/sandals"
        }', true));
    }
}
