@javascript
Feature: Browse product groups
  In order to list the existing product groups for the catalog
  As a user
  I need to be able to see product groups

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | code  | label | type                     |
      | multi | Multi | pim_catalog_multiselect  |
      | color | Color | pim_catalog_simpleselect |
      | size  | Size  | pim_catalog_simpleselect |
    And the following product groups:
      | code          | label          | attributes  | type    |
      | tshirt_akeneo | T-Shirt Akeneo | size, color | VARIANT |
      | mug_akeneo    | Mug Akeneo     | color       | VARIANT |
      | CROSS_SELL_1  | Cross Sell     |             | X_SELL  |
      | CROSS_SELL_2  | Relational     |             | X_SELL  |
    And I am logged in as "admin"

  Scenario: Successfully display product groups
    Given I am on the product groups page
    Then the grid should contain 2 elements
    And I should see the columns Code, Label and Type
    And I should see groups CROSS_SELL_1 and CROSS_SELL_2
    And I should not see group tshort_akeneo and mug_akeneo
