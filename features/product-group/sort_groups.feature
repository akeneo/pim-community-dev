@javascript
Feature: Sort product groups
  In order to create relations bewteen products with groups in the catalog
  As a user
  I need to be able to sort product groups by several columns in the catalog

  Background:
    Given there is no product group
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
    And I am logged in as "admin"

  Scenario: Successfully sort groups
    Given I am on the product groups page
    Then the rows should be sorted ascending by code
    And I should be able to sort the rows by code and label
