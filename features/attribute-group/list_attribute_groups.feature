Feature: Display a message on the attribute groups index page
  In order to manage attributes into group
  As a product manager
  I need to be able to go on the index page

  Scenario: Successfully display a message on attribute group index page
    Given the "default" catalog configuration
    And I am logged in as "Julia"
    When I am on the attribute groups page
    Then I should see "Please select an attribute group on the left or Create a new attribute group"
