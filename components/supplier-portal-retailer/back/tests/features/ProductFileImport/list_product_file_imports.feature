Feature: Supplier Portal - Product File Import - list product file import configurations

  Scenario:
    Given there is no product file import configuration
    When I retrieve the product file import configurations
    Then I should have an empty list of product file import configurations

  Scenario:
    Given there is a product file import configuration "test1"
    And there is a product file import configuration "test2"
    When I retrieve the product file import configurations
    Then I should have the product file import configurations "test1, test2"
