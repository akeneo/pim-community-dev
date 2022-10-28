Feature: Supplier Portal - Product File Import - list product file imports

  Scenario:
    Given there is no product file imports
    When I retrieve the product file imports
    Then I should have an empty list

  Scenario:
    Given there is a product file import "test1"
    And there is a product file import "test2"
    When I retrieve the product file imports
    Then I should have the product file imports "test1, test2"
