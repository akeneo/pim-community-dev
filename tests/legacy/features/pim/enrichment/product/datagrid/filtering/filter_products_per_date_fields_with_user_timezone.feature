@javascript
Feature: Filter products by date field with user timezone
  In order to filter products by date
  As a regular user
  I need to be able to filter products with user timezone taken into account

  Scenario: Successfully filter products by date attributes with timezone America/New_York
    Given the "default" catalog configuration
    And the postit product created at "2018-10-03 01:30:00"
    And the mug product created at "2018-10-03 05:30:00"
    And the pen product created at "2018-10-02 23:30:00"
    When I am logged in as "Julia"
    And I am on the products grid
    Then the grid should contain 3 elements
    And I should see products postit, mug and pen
    And I should be able to use the following filters:
      | filter  | operator    | value                     | result         |
      | created | more than   | 10/02/2018                | mug            |
      | created | less than   | 10/03/2018                | postit and pen |
      | created | between     | 10/02/2018 and 10/02/2018 | postit and pen |
      | created | not between | 10/02/2018 and 10/02/2018 | mug            |
