@ce @optional @javascript
Feature: Edit a connection
  In order to connect my PIM
  As an administrator
  I need to be able to edit a connection

  Scenario: Peter can edit connection settings
    Given a "default" catalog configuration
    And I am logged in as "Peter"
    And I am on the Connections index page
    And I have the following connections:
      | label   | flow type   |
      | Magento | Data source |
      | BigCommerce | Data destination |
    And I should see the "Magento" connection in the "Data source" list
    When I click on the "Magento" connection in the "Data source" list
    Then I am on the "Magento" connection edit page
    When I update the connection label with "NEWLABEL"
    Then I should not see the text "There are unsaved changes."
    And I am on the Connections index page
    Then I should see the "NEWLABEL" connection in the "Data source" list
