@javascript
Feature: Edit common attributes with permissions
  In order to update attributes with edit common attributes
  As a product manager
  I need to be able to add attributes with edit common attributes when I have no 'add attribute' permission

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku   | family |
      | boots | boots  |

  @jira https://akeneo.atlassian.net/browse/PIM-5727
  Scenario: Successfully select attribute when user have no "add attributes" permission
    Given I am logged in as "Mary"
    And I am on the products page
    And I select row boots
    And I press "Change product information" on the "Bulk Actions" dropdown button
    When I choose the "Edit common attributes" operation
    Then I should see the text "Select attributes"
    And I display the Name attribute
    And I change the "Name" to "boots"
    And I move on to the next step
    And I wait for the "edit-common-attributes" mass-edit job to finish
    And the english localizable value name of "boots" should be "boots"
