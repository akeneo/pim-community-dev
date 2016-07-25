Feature: Filter on scopable and/or localizable product values
  In order to filter on products
  As an internal process or any user
  I need to be able to filter on scopable and/or localizable values

  Scenario: Successfully filter on localizable and/or scopable product values
    Given a "default" catalog configuration
    And the following attributes:
      | code             | label            | type             | scopable | localizable | group | metric_family | default_metric_unit | allowed_extensions |
      | boolean          | Boolean          | boolean          | 1        | 1           | other |               |                     |                    |
      | date             | Date             | date             | 1        | 1           | other |               |                     |                    |
      | file             | File             | file             | 1        | 1           | other |               |                     | txt                |
      | media            | Image            | image            | 1        | 1           | other |               |                     | jpg,png            |
      | metric           | Metric           | metric           | 1        | 1           | other | Length        | METER               |                    |
      | multi_select     | Multi select     | multiselect      | 1        | 1           | other |               |                     |                    |
      | number           | Number           | number           | 1        | 1           | other |               |                     |                    |
      | price            | Price            | prices           | 1        | 1           | other |               |                     |                    |
      | simple_select    | Simple select    | simpleselect     | 1        | 1           | other |               |                     |                    |
      | text             | Text             | text             | 1        | 1           | other |               |                     |                    |
      | textarea         | Textarea         | textarea         | 1        | 1           | other |               |                     |                    |
    And the following "multi_select" attribute options: option_multi
    And the following "simple_select" attribute options: option_simple
    And the following products:
      | sku            |
      | filled_product |
      | empty_product  |
    And the following product values:
      | product                  | attribute     | locale | scope     | value                 |
      | filled_product           | boolean       | en_US  | ecommerce | 1                     |
      | filled_product           | date          | en_US  | ecommerce | 1970-01-01            |
      | filled_product           | media         | en_US  | ecommerce | %fixtures%/akeneo.jpg |
      | filled_product           | metric        | en_US  | ecommerce | 1 METER               |
      | filled_product           | multi_select  | en_US  | ecommerce | option_multi          |
      | filled_product           | number        | en_US  | ecommerce | 42                    |
      | filled_product           | price         | en_US  | ecommerce | 2 EUR,4 USD           |
      | filled_product           | simple_select | en_US  | ecommerce | option_simple         |
      | filled_product           | text          | en_US  | ecommerce | Lorem                 |
      | filled_product           | textarea      | en_US  | ecommerce | Lorem ipsum           |
    Then I should get the following results for the given filters:
      | filter                                                                                                             | result             |
      | [{"field":"boolean", "operator":"=", "value": true, "scope": "ecommerce", "locale": "en_US"}]                      | ["filled_product"] |
      | [{"field":"boolean", "operator":"=", "value": true, "locale": "en_US"}]                                            | ["filled_product"] |
      | [{"field":"boolean", "operator":"=", "value": true, "scope": "ecommerce"}]                                         | ["filled_product"] |
      | [{"field":"date", "operator":"=", "value": "1970-01-01", "scope": "ecommerce", "locale": "en_US"}]                 | ["filled_product"] |
      | [{"field":"date", "operator":"=", "value": "1970-01-01", "locale": "en_US"}]                                       | ["filled_product"] |
      | [{"field":"date", "operator":"=", "value": "1970-01-01", "scope": "ecommerce"}]                                    | ["filled_product"] |
      | [{"field":"media", "operator":"=", "value": "akeneo.jpg", "scope": "ecommerce", "locale": "en_US"}]                | ["filled_product"] |
      | [{"field":"media", "operator":"=", "value": "akeneo.jpg", "locale": "en_US"}]                                      | ["filled_product"] |
      | [{"field":"media", "operator":"=", "value": "akeneo.jpg", "scope": "ecommerce"}]                                   | ["filled_product"] |
      | [{"field":"metric", "operator":"=", "value": {"data":1, "unit":"METER"}, "scope": "ecommerce", "locale": "en_US"}] | ["filled_product"] |
      | [{"field":"metric", "operator":"=", "value": {"data":1, "unit":"METER"}, "locale": "en_US"}]                       | ["filled_product"] |
      | [{"field":"metric", "operator":"=", "value": {"data":1, "unit":"METER"}, "scope": "ecommerce"}]                    | ["filled_product"] |
      | [{"field":"multi_select", "operator":"IN", "value": ["option_multi"], "scope": "ecommerce", "locale": "en_US"}]                      | ["filled_product"] |
      | [{"field":"multi_select", "operator":"IN", "value": ["option_multi"], "locale": "en_US"}]                                            | ["filled_product"] |
      | [{"field":"multi_select", "operator":"IN", "value": ["option_multi"], "scope": "ecommerce"}]                                         | ["filled_product"] |
      | [{"field":"number", "operator":"=", "value": 42, "scope": "ecommerce", "locale": "en_US"}]                      | ["filled_product"] |
      | [{"field":"number", "operator":"=", "value": 42, "locale": "en_US"}]                                            | ["filled_product"] |
      | [{"field":"number", "operator":"=", "value": 42, "scope": "ecommerce"}]                                         | ["filled_product"] |
      | [{"field":"price", "operator":"=", "value": {"data": 2, "currency": "EUR"}, "scope": "ecommerce", "locale": "en_US"}]                      | ["filled_product"] |
      | [{"field":"price", "operator":"=", "value": {"data": 2, "currency": "EUR"}, "locale": "en_US"}]                                            | ["filled_product"] |
      | [{"field":"price", "operator":"=", "value": {"data": 2, "currency": "EUR"}, "scope": "ecommerce"}]                                         | ["filled_product"] |
      | [{"field":"simple_select", "operator":"IN", "value": ["option_simple"], "scope": "ecommerce", "locale": "en_US"}]                      | ["filled_product"] |
      | [{"field":"simple_select", "operator":"IN", "value": ["option_simple"], "locale": "en_US"}]                                            | ["filled_product"] |
      | [{"field":"simple_select", "operator":"IN", "value": ["option_simple"], "scope": "ecommerce"}]                                         | ["filled_product"] |
      | [{"field":"text", "operator":"=", "value": "Lorem", "scope": "ecommerce", "locale": "en_US"}]                      | ["filled_product"] |
      | [{"field":"text", "operator":"=", "value": "Lorem", "locale": "en_US"}]                                            | ["filled_product"] |
      | [{"field":"text", "operator":"=", "value": "Lorem", "scope": "ecommerce"}]                                         | ["filled_product"] |
      | [{"field":"textarea", "operator":"=", "value": "Lorem ipsum", "scope": "ecommerce", "locale": "en_US"}]                      | ["filled_product"] |
      | [{"field":"textarea", "operator":"=", "value": "Lorem ipsum", "locale": "en_US"}]                                            | ["filled_product"] |
      | [{"field":"textarea", "operator":"=", "value": "Lorem ipsum", "scope": "ecommerce"}]                                         | ["filled_product"] |
