@javascript
Feature: Filter products per permissions
  In order to enrich my catalog
  As a manager
  I need to be able to manually filter products per permissions

  Background:
    Given the "clothing" catalog configuration
    And the following products:
      | sku          | categories |
      | owned        | jackets    |
      | editable     | tees       |
      | viewable     | tshirts    |
      | notviewable  | jeans      |
      | unclassified |            |
    And I am logged in as "Julia"

  Scenario: Successfully filter products I can review or publish
    Given I am on the products page
    And the grid should contain 4 elements
    Then I should see the filter permission
    And I should be able to use the following filters:
      | filter     | operator | value            | result                                  |
      | permission |          | Review / publish | owned, unclassified                     |
      | permission |          | Edit             | owned, editable, unclassified           |
      | permission |          | View             | owned, editable, viewable, unclassified |
