@javascript
Feature: Edit a variant group
  In order to manage existing variant groups for the catalog
  As a user
  I need to be able to edit a variant group

  Background:
    Given the following attributes:
      | code      | label      | type                     |
      | color     | Color      | pim_catalog_multiselect  |
      | size      | Size       | pim_catalog_simpleselect |
    And the following variants:
      | code    | label          | attributes    |
      | TSHIRT  | T-Shirt Akeneo | size, color   |
      | MUG     | MUG Akeneo     | color         |
    And I am logged in as "admin"

  Scenario: Successfully display the edit view for a variant group
    Given I am on the "MUG" variant page
    Then I should see the Code and Axis fields
    And the fields Code and Axis should be disabled

  Scenario: Successfully edit a variant group
    Given I am on the "MUG" variant page
    When I fill in the following information:
      | English (United States) | Mug |
    And I press the "Save" button
    Then I should see "Variant group successfully updated"
