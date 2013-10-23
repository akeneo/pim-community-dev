@javascript
Feature: Browse product groups
  In order to list the existing product groups for the catalog
  As a user
  I need to be able to see product groups

  Background:
    Given there is no product group
    And the following attributes:
      | code      | label      | type                     |
      | multi     | Multi      | pim_catalog_multiselect  |
      | color     | Color      | pim_catalog_simpleselect |
      | size      | Size       | pim_catalog_simpleselect |
      | dimension | Dimensions | pim_catalog_simpleselect |
    And the following product groups:
      | code           | label          | attributes  | type    |
      | tshirt_akeneo  | T-Shirt Akeneo | size, color | VARIANT |
      | mug_akeneo     | Mug Akeneo     | color       | VARIANT |
      | sticker_akeneo | Sticker Akeneo | dimension   | VARIANT |
    And I am logged in as "admin"

  Scenario: Successfully display product groups
    Given I am on the product groups page
    Then the grid should contain 3 elements
    And I should see groups tshirt_akeneo, mug_akeneo and sticker_akeneo

  Scenario: Successfully display columns
    Given I am on the product groups page
    Then I should see the columns Code, Label, Type and Axis
