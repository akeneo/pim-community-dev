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
    Given I am on the categories page
    When I press the "Create tree" button
    And I create the category with code shoe
    Then I should see the text "[shoe]"
    And I should see the text "successfully created"

  @critical
  Scenario: Create a sub-category
    Given I am on the category tree "default" page
    When I hover over the category tree item "Master catalog"
    And I press the "New category" button
    And I create the category with code shoe
    Then I should see the text "[shoe]"
    And I should see the text "successfully created"
