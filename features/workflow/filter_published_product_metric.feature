Feature: Filter on metric attributes
  In order to filter on published products
  As an internal process or any user
  I need to be able to filter on product by metric attributes

  Scenario: Successfully filter on metric attributes
    Given a "clothing" catalog configuration
    And the following published products:
      | sku       | volume              |
      | MOUTH     | 12 CUBIC_MILLIMETER |
      | SAUCEPAN  | 20 CUBIC_MILLIMETER |
      | POOL      | 15 CUBIC_METER      |
    Then I should get the following published products results for the given filters:
      | filter                                                                                  | result                |
      | [{"field":"volume", "operator":"=", "value":{"data": 12, "unit":"CUBIC_MILLIMETER"}}]   | ["MOUTH"]             |
      | [{"field":"volume", "operator":"<", "value":{"data": 1, "unit":"CUBIC_METER"}}]         | ["MOUTH", "SAUCEPAN"] |
      | [{"field":"volume", "operator":">", "value":{"data": 100, "unit":"CUBIC_MILLIMETER"}}]  | ["POOL"]              |
