Feature: Filter on reference data
  In order to filter on products
  As an internal process or any user
  I need to be able to filter on product by reference data

  Background:
    Given a "default" catalog configuration
    And the following attributes:
      | code             | label            | type                        | scopable | localizable | group | reference_data_name |
      | reference_multi  | Reference multi  | reference_data_multiselect  | 1        | 1           | other | fabrics             |
      | reference_simple | Reference simple | reference_data_simpleselect | 1        | 1           | other | color               |
    And the following reference data:
      | type   | code   | label  |
      | color  | color  | Color  |
      | fabric | fabric | Fabric |
    And the following products:
      | sku             |
      | english_product |
      | french_product  |
      | empty_product   |
    And the following product values:
      | product         | attribute        | locale | scope     | value                 |
      | english_product | reference_multi  | en_US  | ecommerce | fabric                |
      | english_product | reference_simple | en_US  | ecommerce | color                 |
      | french_product  | reference_multi  | fr_FR  | ecommerce | fabric                |
      | french_product  | reference_simple | fr_FR  | ecommerce | color                 |
      | mobile_product  | reference_multi  | fr_FR  | mobile    | fabric                |
      | mobile_product  | reference_simple | fr_FR  | mobile    | color                 |

  Scenario: Successfully filter on localizable and scopable multi reference data values
    Then I should get the following results for the given filters:
      | filter                                                                                                                         | result                                                  |
      | [{"field":"reference_multi.code", "operator":"IN", "value": ["fabric"], "context": {"scope": "ecommerce", "locale": "en_US"}}] | ["english_product"]                                     |
      | [{"field":"reference_multi.code", "operator":"IN", "value": ["fabric"], "context": {"locale": "en_US"}}]                       | ["english_product"]                                     |
      | [{"field":"reference_multi.code", "operator":"IN", "value": ["fabric"], "context": {"scope": "ecommerce"}}]                    | ["english_product", "french_product"]                   |
      | [{"field":"reference_multi.code", "operator":"IN", "value": ["fabric"]}]                                                       | ["english_product", "french_product", "mobile_product"] |

  Scenario: Successfully filter on localizable and scopable simple reference data values
    Then I should get the following results for the given filters:
      | filter                                                                                                                         | result                                                  |
      | [{"field":"reference_simple.code", "operator":"IN", "value": ["color"], "context": {"scope": "ecommerce", "locale": "en_US"}}] | ["english_product"]                                     |
      | [{"field":"reference_simple.code", "operator":"IN", "value": ["color"], "context": {"locale": "en_US"}}]                       | ["english_product"]                                     |
      | [{"field":"reference_simple.code", "operator":"IN", "value": ["color"], "context": {"scope": "ecommerce"}}]                    | ["english_product", "french_product"]                   |
      | [{"field":"reference_simple.code", "operator":"IN", "value": ["color"]}]                                                       | ["english_product", "french_product", "mobile_product"] |

  Scenario: Successfully filter on empty reference data values
    Then I should get the following results for the given filters:
      | filter                                                                       | result            |
      | [{"field":"reference_simple.code", "operator":"EMPTY", "value": ["fabric"]}] | ["empty_product"] |
      | [{"field":"reference_simple.code", "operator":"EMPTY", "value": ["color"]}]  | ["empty_product"] |
