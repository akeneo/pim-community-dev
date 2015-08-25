Feature: Create an asset category
  In order to provide a tree of my asset categories
  As an asset manager
  I need to be able to create a new asset category tree or a node of a category tree

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Pamela"

  Scenario: Create an asset category tree
    Given I am on the asset category tree creation page
    When I fill in the following information:
      | Code | images |
    And I save the asset category
    Then I should be on the asset category "images" edit page
    And I should see "Tree successfully created"

  Scenario: Create an asset category node
    Given the following assets category:
      | code   | label-en_US |
      | images | images      |
    And I am on the asset category "images" node creation page
    When I fill in the following information:
      | Code | logo |
    And I save the asset category
    Then I should be on the asset category "logo" edit page
    And I should see "Category successfully created"
