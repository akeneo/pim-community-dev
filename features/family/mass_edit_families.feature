@javascript
Feature: Mass Edit Families
  In order to define common data between families
  As an administrator
  I need to be able to mass edit attributes and requirements of families

  Scenario: Successfully add many attributes with their requirements to many families
    Given the "footwear" catalog configuration
    And I am logged in as "Peter"
    And I am on the families page
    # These families don't have attribute Length
    When I mass-edit families boots, sneakers and sandals
    And I choose the "Set attribute requirements" operation
    And I display the Length attribute
    And I switch the attribute "Length" requirement in channel "Mobile"
    And I move on to the next step
    Then attribute "Length" should be required in family "boots" for channel "Mobile"
    And attribute "Length" should be required in family "sneakers" for channel "Mobile"
    And attribute "Length" should be required in family "sandals" for channel "Mobile"
    But attribute "Length" should be optional in family "boots" for channel "Tablet"
    And attribute "Length" should be optional in family "sneakers" for channel "Tablet"
    And attribute "Length" should be optional in family "sandals" for channel "Tablet"

  Scenario: Successfully set existing attribute requirements of many families
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the families page
    # These families already have attribute Name
    When I mass-edit families boots, sneakers and sandals
    And I choose the "Set attribute requirements" operation
    And I display the Name attribute
    And I switch the attribute "Name" requirement in channel "Mobile"
    And I move on to the next step
    Then attribute "Name" should be required in family "boots" for channel "Mobile"
    And attribute "Name" should be required in family "sneakers" for channel "Mobile"
    And attribute "Name" should be required in family "sandals" for channel "Mobile"
    But attribute "Name" should be optional in family "boots" for channel "Tablet"
    And attribute "Name" should be optional in family "sneakers" for channel "Tablet"
    And attribute "Name" should be optional in family "sandals" for channel "Tablet"
