@javascript
Feature: Sort variant groups
  In order to create relations between products with groups in the catalog
  As a user
  I need to be able to sort variant groups by several columns in the catalog

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | code      | label      | type                     |
      | color     | Color      | pim_catalog_simpleselect |
      | size      | Size       | pim_catalog_simpleselect |
      | dimension | Dimensions | pim_catalog_simpleselect |
    And the following product groups:
      | code          | label          | attributes  | type    |
      | TSHIRT_ORO    | T-Shirt Oro    | size, color | VARIANT |
      | MUG           | Mug Akeneo     | color       | VARIANT |
      | TSHIRT_AKENEO | T-Shirt Akeneo | size        | VARIANT |
      | CROSS_SELL    | Cross sell     |             | X_SELL  |
    And I am logged in as "admin"

  Scenario: Successfully sort variant groups
    Given I am on the variant groups page
    Then the rows should be sorted ascending by code
    And I should be able to sort the rows by code and label
