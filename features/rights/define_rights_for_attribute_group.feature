@javascript
Feature: Define rights for an attribute group
  In order to be able to restrict access to some product data
  As Peter
  I need to be able to define rights for attribute groups

  Scenario: Succesfully display the fields for attribute group rights
    Given a "footwear" catalog configuration
    And I am logged in as "admin"
    When I am on the "info" attribute group page
    Then I visit the "Rights" tab
    Then I should see the Rights to view attributes and Rights to edit attributes fields
