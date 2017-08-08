@javascript
Feature: Create an API connection
  In order to be able to use the API
  As an administrator
  I need to be able to create a client

  Background: Successfully create an API connection
    Given a "default" catalog configuration
    And I am logged in as "Peter"
    And I am on the clients page
    And I create a new client

  Scenario: Successfully create an API connection
    Then I should see the Label field
    When I fill in the following information in the popin:
      | Label | Magento connector |
    And I press the "Save" button
    Then I should see the text "API connection successfully created"
    Then the grid should contain 1 elements
    And I should see client "Magento connector"

  Scenario: Fail to create an API connection with an empty
    And I press the "Save" button
    Then I should see validation error "This value should not be blank."
