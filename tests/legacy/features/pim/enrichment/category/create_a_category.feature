@javascript
Feature: Create a category
  In order to provide a tree of my product categories
  As a product manager
  I need to be able to create a new category tree or a sub-category

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Julia"

  @critical
  Scenario: Create a category tree
    Given I am on the category tree creation page
    When I fill in the following information:
      | Code | shoe |
    And I save the category
    Then I should not see the text "There are unsaved changes."
    And I should be on the category "shoe" edit page
    And The tree "[shoe]" should be open

  @critical
  Scenario: Create a sub-category
    Given the following category:
      | code | label-en_US |
      | shoe | Shoe        |
    And I am on the category "shoe" node creation page
    When I fill in the following information:
      | Code | flipflap |
    And I save the category
    Then I should be on the category "flipflap" edit page
