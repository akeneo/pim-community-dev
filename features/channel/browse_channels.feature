@javascript
Feature: Browse channels
  In order to list the existing channels for the catalog
  As an administrator
  I need to be able to see channels

  Background:
    Given an "apparel" catalog configuration
    And I am logged in as "Peter"
    And I am on the channels page
    Then the grid should contain 3 elements
    And I should see the columns Code, Label and Category tree

  Scenario: Successfully view, sort channels
    And I should see channels ecommerce, tablet and print
    And the rows should be sorted ascending by Code
    And I should be able to sort the rows by Code, Label and Category tree

  Scenario Outline: Successfully filter channels
    When I show the filter "<filter>"
    And I filter by "<filter>" with operator "<operator>" and value "<value>"
    Then the grid should contain <count> elements
    Then I should see entities <result>

    Examples:
      | filter   | operator | value           | result               | count |
      | code     | contains | t               | tablet and print     | 2     |
      | category |          | 2015 collection | print                | 1     |

  Scenario: Successfully search on label
    When I search "e"
    Then the grid should contain 2 elements
    And I should see entities ecommerce and tablet
