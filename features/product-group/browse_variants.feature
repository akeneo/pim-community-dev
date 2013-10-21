@javascript
Feature: Browse variant groups
  In order to list the existing variant groups for the catalog
  As a user
  I need to be able to see variant groups

  Background:
    Given there is no variant
    And the following attributes:
      | code      | label      | type                     |
      | multi     | Multi      | pim_catalog_multiselect  |
      | color     | Color      | pim_catalog_simpleselect |
      | size      | Size       | pim_catalog_simpleselect |
      | dimension | Dimensions | pim_catalog_simpleselect |
    And the following variants:
      | code           | label          | attributes  | type    |
      | tshirt_akeneo  | T-Shirt Akeneo | size, color | VARIANT |
      | mug_akeneo     | Mug Akeneo     | color       | VARIANT |
      | sticker_akeneo | Sticker Akeneo | dimension   | VARIANT |
    And I am logged in as "admin"

  Scenario: Successfully display variant groups
    Given I am on the variants page
    Then the grid should contain 3 elements
    And I should see variants tshirt_akeneo, mug_akeneo and sticker_akeneo

  Scenario: Successfully display columns
    Given I am on the variants page
    Then I should see the columns Code, Label and Axis
