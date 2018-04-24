@javascript
Feature: Revoke an API connection
  In order to be able to revoke an API connection
  As an administrator
  I need to be able to revoke an API client

  Background: Successfully revoke an API connection
    Given a "default" catalog configuration
    And I am logged in as "Peter"
    And I am on the clients page
    And the following clients:
      | label             |
      | Magento Connector |

  Scenario: Successfully delete an API connection from the grid
    When I click on the "Revoke" action of the row which contains "Magento Connector"
    And I confirm the deletion
    Then I should see the text "API connection successfully revoked"
    And the grid should contain 0 elements
    And I should not see client Magento Connector
