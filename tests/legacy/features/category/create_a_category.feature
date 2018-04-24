@javascript
Feature: Create a category
  In order to provide a tree of my product categories
  As a product manager
  I need to be able to create a new category tree or a node of a category tree

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Julia"

  Scenario: Create a category tree
    Given I am on the category tree creation page
    When I fill in the following information:
      | Code | shoe |
    And I save the category
    Then I should not see the text "There are unsaved changes."
    And I should be on the category "shoe" edit page
    And The tree "[shoe]" should be open

  Scenario: Create a category node
    Given the following category:
      | code | label-en_US |
      | shoe | Shoe        |
    And I am on the category "shoe" node creation page
    When I fill in the following information:
      | Code | flipflap |
    And I save the category
    Then I should be on the category "flipflap" edit page

  @skip
  Scenario: Go to category creation page from the category tree right click menu
    Given the following category:
      | code | label-en_US | parent  |
      | shoe | Shoe        | default |
    And I am on the categories page
    When I right click on the "shoe" category
    And I click on "Create" in the right click menu
    And I blur the category node
    Then I should be on the category "shoe" node creation page
