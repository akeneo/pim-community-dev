Feature: Filter on date
  In order filter on products
  As an internal process or any user
  I need to be able to filter on product by date

  Scenario: Successfully filter on date
    Given a "footwear" catalog configuration
    And the following products:
      | sku     | destocking_date |
      | BOOTBXS | 2015-01-25      |
      | BOOTWXS | 2015-03-25      |
      | BOOTBS  | 2015-01-20      |
      | BOOTBL  | 2013-01-25      |
      | BOOTRXS |                 |
    Then I should get the following results for the given filters:
      | filter                                                                                                                                   | result                           |
      | [{"field":"destocking_date", "operator":"=", "value": "2015-01-25"}]                                                                     | ["BOOTBXS"]                      |
      | [{"field":"destocking_date", "operator":">", "value": "2015-01-19"}]                                                                     | ["BOOTBXS", "BOOTBS", "BOOTWXS"] |
      | [{"field":"destocking_date", "operator":"<", "value": "2015-01-21"}]                                                                     | ["BOOTBS", "BOOTBL"]             |
      | [{"field":"destocking_date", "operator":"BETWEEN", "value": ["2015-01-20", "2015-03-25"]}]                                               | ["BOOTBXS", "BOOTWXS", "BOOTBS"] |
      | [{"field":"destocking_date", "operator":"NOT BETWEEN", "value": ["2015-01-20", "2015-03-25"]}]                                           | ["BOOTBL", "BOOTBS"]             |
      | [{"field":"destocking_date", "operator":"=", "value": "2015-01-25"}, {"field":"destocking_date", "operator":">", "value": "2015-01-19"}] | ["BOOTBXS"]                      |
