Feature: Expose product data via a REST API
  In order to provide access to product data to an external application
  As a developer
  I need to expose product data via a REST API

  Background:
    Given a "clothing" catalog configuration
    And the following product:
      | sku        | name-en_US | description-en_US-mobile | description-en_US-tablet | price-EUR | price-USD | categories  | legacy_attribute |
      | sandals    | My sandals | My great sandals         | My great new sandals     | 20        | 30        | sandals     | old value        |
      | oldsandals | My sandals | My great sandals         | My great new sandals     | 20        | 30        | old_sandals | old value        |

  Scenario: Successfully retrieve a product by applying permissions on attribute groups
    Given I am authenticating as "admin" with "admin_api_key" api key
    And I request information for product "sandals"
    Then the response code should be 200
    And the response should be valid json
    And the response should contain json:
    """
    {
      "identifier": "sandals",
      "family":null,
      "groups":[],
      "variant_group":null,
      "categories":["sandals"],
      "enabled":true,
      "values": {
        "sku":[
          {"locale":null,"scope":null,"data":"sandals"}
        ],
        "name":[
          {"locale":"en_US","scope":null,"data":"My sandals"}
        ],
        "description":[
          {"locale":"en_US","scope":"mobile","data":"My great sandals"},
          {"locale":"en_US","scope":"tablet","data":"My great new sandals"}
        ],
        "price":[
          {"locale":null,"scope":null,"data":[
            {"amount":"20.00","currency":"EUR"},
            {"amount":"30.00","currency":"USD"}
          ]}
        ]
      },
      "created": "2016-11-08T10:43:00+01:00",
      "updated": "2016-11-08T10:43:00+01:00",
      "associations": [],
      "resource":"{baseUrl}/api/rest/products/sandals"
    }
    """

  Scenario: Fail to fetch a not granted product by applying permissions on categories
    Given I am authenticating as "admin" with "admin_api_key" api key
    And I request information for product "oldsandals"
    Then the response code should be 403
