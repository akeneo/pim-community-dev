Feature: Filter on scopable and localizable product values
  In order to filter on products
  As an internal process or any user
  I need to be able to filter on scopable and/or localizable values

  Background:
    Given a "default" catalog configuration
    And the following attributes:
      | code             | label            | type                        | scopable | localizable | group | metric_family | default_metric_unit | allowed_extensions | reference_data_name |
      | boolean          | Boolean          | boolean                     | 1        | 1           | other |               |                     |                    |                     |
      | date             | Date             | date                        | 1        | 1           | other |               |                     |                    |                     |
      | file             | File             | file                        | 1        | 1           | other |               |                     | txt                |                     |
      | media            | Image            | image                       | 1        | 1           | other |               |                     | jpg,png            |                     |
      | metric           | Metric           | metric                      | 1        | 1           | other | Length        | METER               |                    |                     |
      | multi_select     | Multi select     | multiselect                 | 1        | 1           | other |               |                     |                    |                     |
      | number           | Number           | number                      | 1        | 1           | other |               |                     |                    |                     |
      | price            | Price            | prices                      | 1        | 1           | other |               |                     |                    |                     |
      | reference_multi  | Reference multi  | reference_data_multiselect  | 1        | 1           | other |               |                     |                    | fabrics             |
      | reference_simple | Reference simple | reference_data_simpleselect | 1        | 1           | other |               |                     |                    | color               |
      | simple_select    | Simple select    | simpleselect                | 1        | 1           | other |               |                     |                    |                     |
      | text             | Text             | text                        | 1        | 1           | other |               |                     |                    |                     |
      | textarea         | Textarea         | textarea                    | 1        | 1           | other |               |                     |                    |                     |
    And the following reference data:
      | type   | code   | label  |
      | color  | color  | Color  |
      | fabric | fabric | Fabric |
    And the following "multi_select" attribute options: option_multi
    And the following "simple_select" attribute options: option_simple
    And the following products:
      | sku             |
      | english_product |
      | french_product  |
    And the following product values:
      | product         | attribute        | locale | scope     | value                 |
      | english_product | boolean          | en_US  | ecommerce | 1                     |
      | english_product | date             | en_US  | ecommerce | 1970-01-01            |
      | english_product | media            | en_US  | ecommerce | %fixtures%/akeneo.jpg |
      | english_product | metric           | en_US  | ecommerce | 1 METER               |
      | english_product | multi_select     | en_US  | ecommerce | option_multi          |
      | english_product | number           | en_US  | ecommerce | 42                    |
      | english_product | price            | en_US  | ecommerce | 2 EUR                 |
      | english_product | reference_multi  | en_US  | ecommerce | fabric                |
      | english_product | reference_simple | en_US  | ecommerce | color                 |
      | english_product | simple_select    | en_US  | ecommerce | option_simple         |
      | english_product | text             | en_US  | ecommerce | Lorem                 |
      | english_product | textarea         | en_US  | ecommerce | Lorem ipsum           |
      | french_product  | boolean          | fr_FR  | ecommerce | 1                     |
      | french_product  | date             | fr_FR  | ecommerce | 1970-01-01            |
      | french_product  | media            | fr_FR  | ecommerce | %fixtures%/akeneo.jpg |
      | french_product  | metric           | fr_FR  | ecommerce | 1 METER               |
      | french_product  | multi_select     | fr_FR  | ecommerce | option_multi          |
      | french_product  | number           | fr_FR  | ecommerce | 42                    |
      | french_product  | price            | fr_FR  | ecommerce | 2 EUR                 |
      | french_product  | reference_multi  | fr_FR  | ecommerce | fabric                |
      | french_product  | reference_simple | fr_FR  | ecommerce | color                 |
      | french_product  | simple_select    | fr_FR  | ecommerce | option_simple         |
      | french_product  | text             | fr_FR  | ecommerce | Lorem                 |
      | french_product  | textarea         | fr_FR  | ecommerce | Lorem ipsum           |
      | mobile_product  | boolean          | fr_FR  | mobile    | 1                     |
      | mobile_product  | date             | fr_FR  | mobile    | 1970-01-01            |
      | mobile_product  | media            | fr_FR  | mobile    | %fixtures%/akeneo.jpg |
      | mobile_product  | metric           | fr_FR  | mobile    | 1 METER               |
      | mobile_product  | multi_select     | fr_FR  | mobile    | option_multi          |
      | mobile_product  | number           | fr_FR  | mobile    | 42                    |
      | mobile_product  | price            | fr_FR  | mobile    | 2 EUR                 |
      | mobile_product  | reference_multi  | fr_FR  | mobile    | fabric                |
      | mobile_product  | reference_simple | fr_FR  | mobile    | color                 |
      | mobile_product  | simple_select    | fr_FR  | mobile    | option_simple         |
      | mobile_product  | text             | fr_FR  | mobile    | Lorem                 |
      | mobile_product  | textarea         | fr_FR  | mobile    | Lorem ipsum           |

  Scenario: Successfully filter on localizable and scopable boolean values
    Then I should get the following results for the given filters:
      | filter                                                                                                     | result                                                  |
      | [{"field":"boolean", "operator":"=", "value": true, "context": {"scope": "ecommerce", "locale": "en_US"}}] | ["english_product"]                                     |
      | [{"field":"boolean", "operator":"=", "value": true, "context": {"locale": "en_US"}}]                       | ["english_product"]                                     |
      | [{"field":"boolean", "operator":"=", "value": true, "context": {"scope": "ecommerce"}}]                    | ["english_product", "french_product"]                   |
      | [{"field":"boolean", "operator":"=", "value": true}]                                                       | ["english_product", "french_product", "mobile_product"] |

  Scenario: Successfully filter on localizable and scopable date values
    Then I should get the following results for the given filters:
      | filter                                                                                                          | result                                                  |
      | [{"field":"date", "operator":"=", "value": "1970-01-01", "context": {"scope": "ecommerce", "locale": "en_US"}}] | ["english_product"]                                     |
      | [{"field":"date", "operator":"=", "value": "1970-01-01", "context": {"locale": "en_US"}}]                       | ["english_product"]                                     |
      | [{"field":"date", "operator":"=", "value": "1970-01-01", "context": {"scope": "ecommerce"}}]                    | ["english_product", "french_product"]                   |
      | [{"field":"date", "operator":"=", "value": "1970-01-01"}]                                                       | ["english_product", "french_product", "mobile_product"] |

  Scenario: Successfully filter on localizable and scopable media values
    Then I should get the following results for the given filters:
      | filter                                                                                                           | result                                                  |
      | [{"field":"media", "operator":"=", "value": "akeneo.jpg", "context": {"scope": "ecommerce", "locale": "en_US"}}] | ["english_product"]                                     |
      | [{"field":"media", "operator":"=", "value": "akeneo.jpg", "context": {"locale": "en_US"}}]                       | ["english_product"]                                     |
      | [{"field":"media", "operator":"=", "value": "akeneo.jpg", "context": {"scope": "ecommerce"}}]                    | ["english_product", "french_product"]                   |
      | [{"field":"media", "operator":"=", "value": "akeneo.jpg"}]                                                       | ["english_product", "french_product", "mobile_product"] |

  Scenario: Successfully filter on localizable and scopable metric values
    Then I should get the following results for the given filters:
      | filter                                                                                                                          | result                                                  |
      | [{"field":"metric", "operator":"=", "value": {"data":1, "unit":"METER"}, "context": {"scope": "ecommerce", "locale": "en_US"}}] | ["english_product"]                                     |
      | [{"field":"metric", "operator":"=", "value": {"data":1, "unit":"METER"}, "context": {"locale": "en_US"}}]                       | ["english_product"]                                     |
      | [{"field":"metric", "operator":"=", "value": {"data":1, "unit":"METER"}, "context": {"scope": "ecommerce"}}]                    | ["english_product", "french_product"]                   |
      | [{"field":"metric", "operator":"=", "value": {"data":1, "unit":"METER"}}]                                                       | ["english_product", "french_product", "mobile_product"] |

  Scenario: Successfully filter on localizable and scopable multi select values
    Then I should get the following results for the given filters:
      | filter                                                                                                                            | result                                                  |
      | [{"field":"multi_select.code", "operator":"IN", "value": ["option_multi"], "context": {"scope": "ecommerce", "locale": "en_US"}}] | ["english_product"]                                     |
      | [{"field":"multi_select.code", "operator":"IN", "value": ["option_multi"], "context": {"locale": "en_US"}}]                       | ["english_product"]                                     |
      | [{"field":"multi_select.code", "operator":"IN", "value": ["option_multi"], "context": {"scope": "ecommerce"}}]                    | ["english_product", "french_product"]                   |
      | [{"field":"multi_select.code", "operator":"IN", "value": ["option_multi"]}]                                                       | ["english_product", "french_product", "mobile_product"] |

  Scenario: Successfully filter on localizable and scopable number values
    Then I should get the following results for the given filters:
      | filter                                                                                                  | result                                                  |
      | [{"field":"number", "operator":"=", "value": 42, "context": {"scope": "ecommerce", "locale": "en_US"}}] | ["english_product"]                                     |
      | [{"field":"number", "operator":"=", "value": 42, "context": {"locale": "en_US"}}]                       | ["english_product"]                                     |
      | [{"field":"number", "operator":"=", "value": 42, "context": {"scope": "ecommerce"}}]                    | ["english_product", "french_product"]                   |
      | [{"field":"number", "operator":"=", "value": 42}]                                                       | ["english_product", "french_product", "mobile_product"] |

  Scenario: Successfully filter on localizable and scopable price values
    Then I should get the following results for the given filters:
      | filter                                                                                                                             | result                                                  |
      | [{"field":"price", "operator":"=", "value": {"data": 2, "currency": "EUR"}, "context": {"scope": "ecommerce", "locale": "en_US"}}] | ["english_product"]                                     |
      | [{"field":"price", "operator":"=", "value": {"data": 2, "currency": "EUR"}, "context": {"locale": "en_US"}}]                       | ["english_product"]                                     |
      | [{"field":"price", "operator":"=", "value": {"data": 2, "currency": "EUR"}, "context": {"scope": "ecommerce"}}]                    | ["english_product", "french_product"]                   |
      | [{"field":"price", "operator":"=", "value": {"data": 2, "currency": "EUR"}}]                                                       | ["english_product", "french_product", "mobile_product"] |

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

  Scenario: Successfully filter on localizable and scopable simple select values
    Then I should get the following results for the given filters:
      | filter                                                                                                                              | result                                                  |
      | [{"field":"simple_select.code", "operator":"IN", "value": ["option_simple"], "context": {"scope": "ecommerce", "locale": "en_US"}}] | ["english_product"]                                     |
      | [{"field":"simple_select.code", "operator":"IN", "value": ["option_simple"], "context": {"locale": "en_US"}}]                       | ["english_product"]                                     |
      | [{"field":"simple_select.code", "operator":"IN", "value": ["option_simple"], "context": {"scope": "ecommerce"}}]                    | ["english_product", "french_product"]                   |
      | [{"field":"simple_select.code", "operator":"IN", "value": ["option_simple"]}]                                                       | ["english_product", "french_product", "mobile_product"] |

  Scenario: Successfully filter on localizable and scopable text values
    Then I should get the following results for the given filters:
      | filter                                                                                                     | result                                                  |
      | [{"field":"text", "operator":"=", "value": "Lorem", "context": {"scope": "ecommerce", "locale": "en_US"}}] | ["english_product"]                                     |
      | [{"field":"text", "operator":"=", "value": "Lorem", "context": {"locale": "en_US"}}]                       | ["english_product"]                                     |
      | [{"field":"text", "operator":"=", "value": "Lorem", "context": {"scope": "ecommerce"}}]                    | ["english_product", "french_product"]                   |
      | [{"field":"text", "operator":"=", "value": "Lorem"}]                                                       | ["english_product", "french_product", "mobile_product"] |

  Scenario: Successfully filter on localizable and scopable textarea values
    Then I should get the following results for the given filters:
      | filter                                                                                                               | result                                                  |
      | [{"field":"textarea", "operator":"=", "value": "Lorem ipsum", "context": {"scope": "ecommerce", "locale": "en_US"}}] | ["english_product"]                                     |
      | [{"field":"textarea", "operator":"=", "value": "Lorem ipsum", "context": {"locale": "en_US"}}]                       | ["english_product"]                                     |
      | [{"field":"textarea", "operator":"=", "value": "Lorem ipsum", "context": {"scope": "ecommerce"}}]                    | ["english_product", "french_product"]                   |
      | [{"field":"textarea", "operator":"=", "value": "Lorem ipsum"}]                                                       | ["english_product", "french_product", "mobile_product"] |
