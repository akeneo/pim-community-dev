@javascript
Feature: Browse variant groups
  In order to list the existing variant groups for the catalog
  As a user
  I need to be able to see variant groups

  Background:
    Given the "default" catalog configuration
    And the following attributes:
      | code      | label      | type                     |
      | multi     | Multi      | pim_catalog_multiselect  |
      | color     | Color      | pim_catalog_simpleselect |
      | size      | Size       | pim_catalog_simpleselect |
      | dimension | Dimensions | pim_catalog_simpleselect |
    And the following product groups:
      | code           | label          | attributes  | type    |
      | tshirt_akeneo  | T-Shirt Akeneo | size, color | VARIANT |
      | mug_akeneo     | Mug Akeneo     | color       | VARIANT |
      | sticker_akeneo | Sticker Akeneo | dimension   | VARIANT |
      | cross_sell     | Cross Sell     |             | X_SELL  |
    And I am logged in as "admin"

  Scenario: Successfully display variant groups
    Given I am on the variant groups page
    Then the grid should contain 3 elements
    And I should see the columns Code, Label and Axis
    And I should see groups tshirt_akeneo, mug_akeneo and sticker_akeneo
    And I should not see group cross_sell
