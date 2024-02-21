@javascript
Feature: Browse group types
  In order to list the existing group types in the catalog
  As an administrator
  I need to be able to see group types

  Scenario: Successfully display group types
    Given a "footwear" catalog configuration
    And I am logged in as "Peter"
    When I am on the group types page
    Then the grid should contain 2 elements
    And I should see the columns Label
    And I should see group types [RELATED] and [XSELL]
