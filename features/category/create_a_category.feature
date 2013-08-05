Feature: Create a category
  In order to provide a tree of my product categories
  As a user
  I need to be able to create a new category tree or a node of a category tree

  Scenario: Create a category tree
    Given I am logged in as "admin"
    And I am on the category tree creation page
    When I fill in the following information:
      | Code    | shoe |
      | Default | Shoe |
    And I save the category
    Then I should be on the category "shoe" edit page
    And I should see "Tree successfully created"

  Scenario: Create a category node
    Given the following category:
      | code | title |
      | shoe | Shoe  |
    Given I am logged in as "admin"
    And I am on the category "shoe" node creation page
    When I fill in the following information:
      | Code    | flipflap |
      | Default | FlipFlap |
    And I save the category
    Then I should be on the category "flipflap" edit page
    And I should see "Category successfully created"
