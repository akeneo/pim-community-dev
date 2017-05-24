@javascript
Feature: Assign assets to a variant group
  In order to assign assets to a variant group
  As a product manager
  I need to be able to link multiple assets to a variant group

  Background:
    Given the "clothing" catalog configuration
    And the following variant groups:
      | code       | label-en_US          | axis | type    |
      | hh_jackets | Helly Hansen jackets | size | VARIANT |
    And the following products:
      | sku    | groups     | size |
      | jacket | hh_jackets | XS   |
    And I generate missing variations
    And I am logged in as "Julia"

  Scenario: Succesfully assign assets to a variant group
    Given I am on the "hh_jackets" variant group page
    And I visit the "Attribute" tab
    And I add available attributes Front view
    And I start to manage assets for "Front view"
    And I should see the columns Thumbnail, Code, Description, End of use, Created at and Last updated at
    And I change the page size to 100
    And I check the row "paint"
    And I check the row "machine"
    Then the asset basket should contain paint, machine
    And I confirm the asset modification
    Then the "Front view" asset gallery should contain paint, machine
    And I save the variant group
    Then the "Front view" asset gallery should contain paint, machine
    And I am on the "jacket" product page
    And I visit the "Media" group
    Then the "Front view" asset gallery should contain paint, machine

  @jira https://akeneo.atlassian.net/browse/PIM-6267
  Scenario: Picking assets for a variant group doesn't affect product selection of the variant group
    Given I am on the "hh_jackets" variant group page
    Then the row "jacket" should be checked
    When I visit the "Attribute" tab
    And I add available attributes Front view
    And I start to manage assets for "Front view"
    And I check the row "paint"
    And I check the row "machine"
    Then the asset basket should contain paint, machine
    When I confirm the asset modification
    And I visit the "Products" tab
    Then the row "jacket" should be checked
