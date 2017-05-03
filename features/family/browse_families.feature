@javascript
Feature: Browse families
  In order to view the families that have been created
  As an administrator
  I need to be able to view a list of them

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Peter"
    When I am on the families page
    Then the grid should contain 5 elements

  Scenario: Successfully view and sort families
    Then I should see the columns Code, Label and Attribute as label
    And I should see families boots, sandals and sneakers
    And the rows should be sorted ascending by Code
    And I should be able to sort the rows by Code, Label and Attribute as label

  Scenario Outline: Successfully filter families
    When I show the filter "<filter>"
    And I filter by "<filter>" with operator "<operator>" and value "<value>"
    Then the grid should contain <count> elements
    Then I should see entities <result>

    Examples:
      | filter           | operator | value | result                                      | count |
      | code             | contains | a     | sandals and sneakers                        | 2     |
      | attributeAsLabel |          | Name  | boots, heels, led_tvs, sandals and sneakers | 5     |

  Scenario: Successfully search on label
    When I search "Boo"
    Then the grid should contain 1 element
    And I should see entity boots

  Scenario: Successfully keep descending sorting order after refreshing the page
    And I sort by "code" value descending
    When I refresh current page
    And I wait 3 seconds
    Then the rows should be sorted descending by Code

  Scenario: Successfully keep ascending sorting order after refreshing the page
    And I sort by "code" value descending
    And I sort by "code" value ascending
    When I refresh current page
    And I wait 3 seconds
    Then the rows should be sorted ascending by code

  @jira https://akeneo.atlassian.net/browse/PIM-4494
  Scenario: Successfully sort families and use them for mass edit
    Given the following product:
      | sku         | family   |
      | caterpillar | boots    |
      | dr-martens  | boots    |
      | tbs         | sneakers |
      | vans        | sneakers |
    And I am logged in as "Julia"
    And I am on the products page
    When I sort by "family" value ascending
    Then the rows should be sorted ascending by family
    When I select rows caterpillar and dr-martens
    And I press "Change product information" on the "Bulk Actions" dropdown button
    Then I should see "Mass Edit (2 products)"
