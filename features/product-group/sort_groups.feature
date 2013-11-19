@javascript
Feature: Sort product groups
  In order to create relations between products with groups in the catalog
  As a user
  I need to be able to sort product groups by several columns in the catalog

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | code  | label | type                     |
      | color | Color | pim_catalog_simpleselect |
    And the following product groups:
      | code       | label          | attributes | type    |
      | TSHIRT     | T-Shirt Akeneo | color      | VARIANT |
      | CROSS_SELL | Cross sell     |            | X_SELL  |
      | MUG        | Mug            |            | X_SELL  |
    And I am logged in as "admin"

  Scenario: Successfully sort product groups
    Given I am on the product groups page
    Then the rows should be sorted ascending by code
    And I should be able to sort the rows by code, label and type
