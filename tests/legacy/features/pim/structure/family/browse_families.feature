@javascript
Feature: Browse families
  In order to view the families that have been created
  As an administrator
  I need to be able to view a list of them

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Peter"
    When I am on the families grid
    Then the grid should contain 5 elements

  Scenario: Successfully view and sort families
    Then I should see the columns Label and Attribute as label
    And I should see families Boots, Sandals and Sneakers
    And the rows should be sorted ascending by Label
    And I should be able to sort the rows by Label and Attribute as label

  Scenario: Successfully search on label
    When I search "Boo"
    Then the grid should contain 1 element
    And I should see entity Boots
