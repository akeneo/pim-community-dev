@javascript
Feature: Show all rules related to an attribute
  In order ease the enrichement of the catalog
  As a regular user
  I need to know which rules are

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following product rules:
      | code                       | priority |
      | copy_description_into_name | 10       |
    And the following product rule conditions:
      | rule                       | field         | operator | value          | locale | scope  |
      | copy_description_into_name | name          | =        | My nice tshirt | en_US  |        |
      | copy_description_into_name | description   | EMPTY    |                |        | mobile |
    And the following product rule setter actions:
      | rule                       | field         | value         | locale |
      | copy_description_into_name | description   | a nice tshirt | en_US  |

  Scenario: Successfully show rules of an attribute
    Given I am on the "description" attribute page
    And I visit the "Rules" tab
    Then I should see the following rule conditions:
      | rule                       | field         | operator | value          | locale | scope  |
      | copy_description_into_name | name          | =        | My nice tshirt | en_US  |        |
      | copy_description_into_name | description   | EMPTY    |                |        | mobile |
    # Then I should see the following rule actions:
    #   | rule  | field         | value         | locale |
    #   | rule1 | description   | a nice tshirt | en_US  |
