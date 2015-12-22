@javascript
Feature: Display the variant group history
  In order to know who, when and what changes has been made to a variant group
  As a product manager
  I need to have access to a variant group history

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully edit a variant group and see the history
    And I am on the variant groups page
    When I create a new variant group
    And I fill in the following information in the popin:
      | Code | converse_sneakers |
      | Axis | Size              |
    And I press the "Save" button
    And I am on the variant groups page
    Then I should see groups converse_sneakers and caterpillar_boots
    When I am on the "converse_sneakers" variant group page
    And I visit the "History" tab
    Then there should be 1 update
    And I should see history:
      | version | property | value             |
      | 1       | code     | converse_sneakers |
      | 1       | type     | VARIANT           |
      | 1       | axis     | size              |

  Scenario: Successfully edit a variant group attribute and see the change in history
    And I am on the "caterpillar_boots" variant group page
    And I visit the "Attributes" tab
    And I add available attributes Name and Length
    And I change the "Name" to "Ultra boots"
    And I change the "Length" to "5"
    And I save the variant group
    And I visit the "History" tab
    And I should see history:
      | version | property   | value       |
      | 2       | name-en_US | Ultra boots |
      | 2       | length     | 5           |
