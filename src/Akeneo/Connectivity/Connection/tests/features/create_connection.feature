@skip @javascript
Feature: Create a connection
  In order to connect my PIM
  As an administrator
  I need to be able to create a new connection

  Scenario: Successfully create a new connection
    Given a "default" catalog configuration
    And I am logged in as "Peter"
    When I create a connection with the following information:
      | label   | flow type   |
      | Magento | Data source |
    Then I should see the "Magento" connection in the "Data source" list
