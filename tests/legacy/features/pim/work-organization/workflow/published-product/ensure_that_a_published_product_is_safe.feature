@javascript @published-product-feature-enabled
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

  @skip @jira https://akeneo.atlassian.net/browse/PIM-6314
  Scenario: Fail to remove a product that has been published
    Given I am on the "my-jacket" product page
    And I press the secondary action "Delete"
    And I confirm the removal
    Then I am on the products grid
    And I should see product my-jacket

  Scenario: Successfully remove a category that is not linked to a published product
    Given I am on the "winter_top" category page
    And I should see the text "Winter tops"
    And I press the secondary action "Delete"
    And I confirm the removal
    And I should see the text "The category \"Winter tops\" was successfully deleted"
    And I should see the text "2014 collection"

  Scenario: Successfully remove a family that is not linked to a published product
    Given I am on the "pants" family page
    And I press the secondary action "Delete"
    And I confirm the removal
    When I am on the families page
    Then I should not see family Pants

  Scenario: Fail to remove an attribute that is linked to a published product
    Given I am on the "handmade" attribute page
    And I press the secondary action "Delete"
    And I fill the input labelled 'Please type "handmade"' with 'handmade'
    And I press the "Delete" button
    Then I am on the attributes page
    And I should see attribute Handmade

  Scenario: Successfully remove an attribute that is not linked to a published product
    Given I am on the "comment" attribute page
    And I press the secondary action "Delete"
    And I fill the input labelled 'Please type "comment"' with 'comment'
    And I press the "Delete" button
    Then I am on the attributes page
    And I should not see attribute Comment

  Scenario: Fail to mass delete products if one of them has been published
    Given the following products:
      | sku          | categories | family  |
      | black-jacket | jackets    | jackets |
    And I am on the products grid
    And I select rows my-jacket and black-jacket
    And I press the "Delete" button
    And I confirm the removal
    And the grid should contain 2 elements
    And I should see products my-jacket and black-jacket

  Scenario: Fail to remove an option linked to a published product
    Given I am on the "manufacturer" attribute page
    And I visit the "Options" tab
    When I remove the "Volcom" option
    And I confirm the deletion
    Then I should see the text "Entities linked to published products cannot be removed."
    And the Options section should contain 4 options

  Scenario: Successfully remove an option not linked to a published product
    Given I am on the "manufacturer" attribute page
    And I visit the "Options" tab
    And I remove the "Desigual" option
    And I confirm the deletion
    When I save the attribute
    And I should see the flash message "Attribute successfully updated"
    Then the Options section should contain 3 options

  Scenario: Fail to remove a multi-option linked to a published product
    Given I am on the "weather_conditions" attribute page
    And I visit the "Options" tab
    When I remove the "dry" option
    And I confirm the deletion
    Then I should see the text "Entities linked to published products cannot be removed."
    And the Options section should contain 5 options

  @jira https://akeneo.atlassian.net/browse/PIM-4600
  Scenario: Successfully remove a multi-option not linked to a published product
    Given I am on the "weather_conditions" attribute page
    And I visit the "Options" tab
    And I remove the "hot" option
    And I confirm the deletion
    When I save the attribute
    And I should see the flash message "Attribute successfully updated"
    Then the Options section should contain 4 options
