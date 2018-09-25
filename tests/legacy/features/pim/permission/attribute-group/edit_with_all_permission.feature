@javascript
Feature: Edit attribute group with all permissions
  In order to be able to permit all groups
  As a product manager
  I need to be able to allow 'all' group to view and edit attributes

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku    | price  |
      | socket | 20 USD |

  @critical @jira https://akeneo.atlassian.net/browse/PIM-5478
  Scenario: Successfully edit an attribute group with 'all' permission
    Given I am logged in as "Peter"
    And I am on the "marketing" attribute group page
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to view attributes | All |
      | Allowed to edit attributes | All |
    And I press the "Save" button
    And I edit the "socket" product
    And I visit the "Marketing" group
    Then I should see the text "price"
