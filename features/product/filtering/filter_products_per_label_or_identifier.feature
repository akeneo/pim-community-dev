@javascript
Feature: Filter products by text field
  In order to filter products by text attributes in the catalog
  As a regular user
  I need to be able to filter products in the catalog

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Mary"

  Scenario: Successfully filter products by empty value for scopable and localizable text attribute
    Given I add the "english" locale to the "mobile" channel
    And the following attributes:
      | label-en_US | type             | localizable | scopable | useable_as_grid_filter | group | code |
      | name        | pim_catalog_text | 0           | 0        | 1                      | other | name |
      | ean         | pim_catalog_text | 0           | 0        | 1                      | other | ean  |
    And the following families:
      | code   | attribute_as_label | attributes |
      | office | name               | name,ean  |
      | home   | ean                | name,ean  |
    And the following products:
      | sku    | family | name    | ean  |
      | 1258   | office | Post it | 1212 |
      | 6589   | office | paper   | post |
      | mug    | home   | Mug     | 4212 |
    And I am on the products page
    Then the grid should contain 3 elements
    And I should see products 1258, 6589 and mug
    And I should be able to use the following filters:
      | filter              | operator | value   | result |
      | label_or_identifier | equals   | 1258    | 1258   |
      | label_or_identifier | equals   | post    | 1258   |
      | label_or_identifier | equals   | post it | 1258   |
      | label_or_identifier | equals   | paper   | 6589   |
      | label_or_identifier | equals   | 4212    | mug    |
