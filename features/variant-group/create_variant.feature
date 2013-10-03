@javascript
Feature: Variant group creation
  In order to add a non-imported variant group
  As a user
  I need to be able to manually create a variant group

  Background:
    Given the following attributes:
      | code      | label      | type                     |
      | color     | Color      | pim_catalog_multiselect  |
      | size      | Size       | pim_catalog_simpleselect |
      | dimension | Dimensions | pim_catalog_simpleselect |
    And I am logged in as "admin"

  Scenario: Successfully display all required fields in the variant creation form
    Given I am on the variants page
    And I create a new variant
    Then I should see the Code and Attributes fields
