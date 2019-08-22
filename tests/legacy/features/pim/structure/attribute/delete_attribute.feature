@javascript
Feature: Delete an attribute
  In order to remove an attribute
  As a product manager
  I need to delete a text attribute

  @critical
  @jira https://akeneo.atlassian.net/browse/PIM-7199
  Scenario: An identifier attribute cannot be deleted
    Given the "default" catalog configuration
    And I am logged in as "Julia"
    When I am on the attributes page
    And I click on the "delete" action of the row which contains "SKU"
    And I confirm the deletion
    Then I should see the text "Identifier attribute can not be removed"
    And there should be a "SKU" attribute
