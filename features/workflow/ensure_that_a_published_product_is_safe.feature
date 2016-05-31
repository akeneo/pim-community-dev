@javascript
Feature: Ensure that a published product is safe
  In order to keep published product consistent
  As a product manager
  I need to be forbidden from removing structural part of a published product

  Background:
    Given a "clothing" catalog configuration
    And the following published products:
      | sku       | categories | family  | groups          | handmade | manufacturer | weather_conditions |
      | my-jacket | jackets    | jackets | similar_jackets | 1        | Volcom       | dry                |
    And I am logged in as "Julia"

  Scenario: Fail to remove a product that has been published
    Given I am on the "my-jacket" product page
    And I press the "Delete" button
    And I confirm the removal
    Then I am on the products page
    And I should see product my-jacket

  Scenario: Fail to remove a category that is linked to a published product
    Given I am on the "jackets" category page
    And I press the "Delete" button
    And I confirm the removal
    Then I should see the "jackets" category under the "summer_collection" category

  Scenario: Fail to remove a category if one of these children is linked to a published product
    Given I am on the "summer_collection" category page
    And I press the "Delete" button
    And I confirm the removal
    Then I am on the "jackets" category page
    And I should see the "jackets" category under the "summer_collection" category

  Scenario: Successfully remove a category that is not linked to a published product
    Given I am on the "winter_top" category page
    And I press the "Delete" button
    And I confirm the removal
    Then I should not see the "winter_top" category under the "winter_collection" category

  Scenario: Fail to remove a family that is linked to a published product
    Given I am on the "jackets" family page
    And I press the "Delete" button
    And I confirm the removal
    Then I am on the families page
    And I should see family jackets

  Scenario: Successfully remove a family that is not linked to a published product
    Given I am on the "pants" family page
    And I press the "Delete" button
    And I confirm the removal
    When I am on the families page
    Then I should not see family pants

  Scenario: Fail to remove a group that is linked to a published product
    Given I am on the "similar_jackets" product group page
    And I press the "Delete" button
    And I confirm the removal
    Then I am on the product groups page
    And I should see group similar_jacket

  Scenario: Successfully remove a group that is not linked to a published product
    Given I am on the "hm_jackets" variant group page
    And I press the "Delete" button
    And I confirm the removal
    When I am on the variant groups page
    Then I should not see group hm_jackets

  Scenario: Fail to remove an attribute that is linked to a published product
    Given I am on the "handmade" attribute page
    And I press the "Delete" button
    And I confirm the removal
    Then I am on the attributes page
    And I should see attribute handmade

  Scenario: Successfully remove an attribute that is not linked to a published product
    Given I am on the "comment" attribute page
    And I press the "Delete" button
    And I confirm the removal
    Then I am on the attributes page
    And I should not see attribute comment

  Scenario: Fail to mass delete products if one of them has been published
    Given the following products:
      | sku          | categories | family  |
      | black-jacket | jackets    | jackets |
    And I am on the products page
    And I mass-delete products my-jacket and black-jacket
    And I confirm the removal
    And the grid should contain 2 elements
    And I should see products my-jacket and black-jacket

  Scenario: Fail to remove an option linked to a published product
    Given I am on the "manufacturer" attribute page
    And I visit the "Values" tab
    And I remove the "Volcom" option
    And I confirm the deletion
    And I confirm the error message
    When I save the attribute
    And I should see the flash message "Attribute successfully updated"
    Then the Options section should contain 4 options

  Scenario: Successfully remove an option not linked to a published product
    Given I am on the "manufacturer" attribute page
    And I visit the "Values" tab
    And I remove the "Desigual" option
    And I confirm the deletion
    When I save the attribute
    And I should see the flash message "Attribute successfully updated"
    Then the Options section should contain 3 options

  Scenario: Fail to remove a multi-option linked to a published product
    Given I am on the "weather_conditions" attribute page
    And I visit the "Values" tab
    And I remove the "dry" option
    And I confirm the deletion
    And I confirm the error message
    When I save the attribute
    And I should see the flash message "Attribute successfully updated"
    Then the Options section should contain 5 options

  @jira https://akeneo.atlassian.net/browse/PIM-4600
  Scenario: Successfully remove a multi-option not linked to a published product
    Given I am on the "weather_conditions" attribute page
    And I visit the "Values" tab
    And I remove the "hot" option
    And I confirm the deletion
    When I save the attribute
    And I should see the flash message "Attribute successfully updated"
    Then the Options section should contain 4 options
