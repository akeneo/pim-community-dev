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
    And I wait for the "set-attribute-requirements" mass-edit job to finish
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
    And I wait for the "set-attribute-requirements" mass-edit job to finish
    Then attribute "Name" should be required in family "boots" for channel "Mobile"
    And attribute "Name" should be required in family "sneakers" for channel "Mobile"
    And attribute "Name" should be required in family "sandals" for channel "Mobile"
    But attribute "Name" should be optional in family "boots" for channel "Tablet"
    And attribute "Name" should be optional in family "sneakers" for channel "Tablet"
    And attribute "Name" should be optional in family "sandals" for channel "Tablet"

  Scenario: Successfully return to the family page when cancelling family mass edit
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the families page
    When I mass-edit families boots, sneakers and sandals
    And I press the "Cancel" button
    Then I should be on the families page

  Scenario: Successfully mass edit more than 10 families
    Given the "default" catalog configuration
    And the following families:
    | code     |
    | first    |
    | second   |
    | third    |
    | fourth   |
    | fifth    |
    | sixth    |
    | seventh  |
    | eigth    |
    | ninth    |
    | tenth    |
    | eleventh |
    And I am logged in as "Julia"
    And I am on the families page
    When I mass-edit families first, second, third, fourth, fifth, sixth, seventh, eigth, ninth, tenth and eleventh
    Then I should see "Mass Edit (11 families)"

  @jira https://akeneo.atlassian.net/browse/PIM-4203
  Scenario: Successfully mass edit families after sorting by label
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And I am on the families page
    When I sort by "label" value ascending
    And I mass-edit families boots, sneakers and sandals
    Then I should see "Mass Edit (3 families)"
