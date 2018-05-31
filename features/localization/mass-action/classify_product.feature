@javascript
Feature: Edit common localized attributes of many products at once
  In order to update many products with the same information
  As a product manager
  I need to be able to edit common attributes of many products at once

  Background:
    Given the "default" catalog configuration
    And the following categories:
      | code    | label-en_US  | label-fr_FR  | parent  |
      | foocat  | enfoo        | frfoo        | default |
      | barcat  | enbar        | frbar        | foocat  |
    And the following products:
      | sku       | categories |
      | bigfoot   | foocat     |
      | horseshoe | foocat     |
    And I am logged in as "Julia"
    And I am on the products grid

  @info https://akeneo.atlassian.net/browse/PIM-7364
  Scenario: Mass edit categories in the catalog locale
    Given I select rows bigfoot and horseshoe
    And I press the "Bulk actions" button
    And I choose the "Add to categories" operation
    And I move on to the choose step
    And I choose the "Add to categories" operation
    And I press the "Master catalog" button
    And I expand the "default" category
    Then I should see the text "enfoo"
    And I expand the "foocat" category
    Then I should see the text "enbar"

    Given I edit the "Julia" user
    And I visit the "Additional" tab
    And I change the "Catalog locale" to "fr_FR"
    And I save the user

    When I am on the products grid
    And I select rows bigfoot and horseshoe
    And I press the "Bulk actions" button
    And I choose the "Add to categories" operation
    And I move on to the choose step
    And I choose the "Add to categories" operation
    And I press the "Catalog principal" button
    And I expand the "default" category
    Then I should see the text "frfoo"
    And I expand the "foocat" category
    Then I should see the text "frbar"
