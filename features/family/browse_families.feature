@javascript
Feature: Browse families
  In order to view the families that have been created
  As an administrator
  I need to be able to view a list of them

  Scenario: Successfully display all the families
    Given a "footwear" catalog configuration
    And I am logged in as "Peter"
    When I am on the families page
    Then the grid should contain 3 elements
    And I should see the columns Code, Label and Attribute as label
    And I should see families boots, sandals and sneakers
    And the rows should be sorted ascending by code
    And I should be able to sort the rows by code, label and attribute as label
    And I should be able to use the following filters:
      | filter             | value | result                      |
      | Code               | a     | sandals and sneakers        |
      | Label              | Boo   | boots                       |
      | Attribute as label | Name  | boots, sandals and sneakers |
