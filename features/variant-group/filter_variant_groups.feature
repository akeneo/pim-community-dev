@javascript
Feature: Filter variant groups
  In order to filter variant groups in the catalog
  As a user
  I need to be able to filter variant groups in the catalog

  Scenario: Successfully filter variant groups
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
    And I am on the variant groups page
    Then the grid should contain 3 elements
    And I should see groups TSHIRT_ORO, MUG and TSHIRT_AKENEO
    And I should be able to use the following filters:
      | filter | value  | result                |
      | Code   | MUG    | MUG                   |
      | Label  | Akeneo | MUG and TSHIRT_AKENEO |
      | Axis   | Color  | TSHIRT_ORO and MUG    |
