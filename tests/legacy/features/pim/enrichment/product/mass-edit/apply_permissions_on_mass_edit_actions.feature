@javascript
Feature: Apply ACL permissions on mass edit actions
  In order to let users use mass edit actions
  As an administrator
  I need to be able manage ACL on mass edit actions

  Background:
    Given an "apparel" catalog configuration
    And the following products:
      | sku          | family  |
      | kickers      | sandals |
      | hiking_shoes | sandals |
    And I am logged in as "Julia"

  @jira https://akeneo.atlassian.net/browse/PIM-5171
  Scenario: View only the mass edit operations I have permissions on
    Given I edit the "Catalog manager" Role
    And I visit the "Permissions" tab
    And I grant rights to group Products
    And I revoke rights to resource Change product family and Change the status of a product
    And I save the Role
    Then I should not see the text "There are unsaved changes."
    When I am on the products grid
    And I select rows kickers and hiking_shoes
    And I press the "Bulk actions" button
    Then I should see the text "Edit attributes"
    And I should see the text "Add to groups"
    And I should not see the text "Change family"
    And I should not see the text "Change status"
