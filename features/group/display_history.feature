Feature: Display the attribute group history
  In order to know who, when and what changes has been made to an attribute group
  As Julia
  I need to have access to a attribute group history

  Background:
    Given I am logged in as "Julia"
    And the following product attributes:
    | label       |
    | Description |

  @javascript
  Scenario: Succesfully edit a group and see the history
    Given I am on the group creation page
    And I change the Code to "Tecnical"
    And I save the group
    When I visit the "History" tab
    Then there should be 1 update
    And I should see history:
    | action | version | data          |
    | create | 1       | code:Tecnical |
    When I visit the "Properties" tab
    And I change the Code to "Technical"
    And I save the group
    When I visit the "History" tab
    Then there should be 2 updates
    And I should see history:
    | action | version | data                   |
    | create | 1       | code:Tecnical          |
    | update | 2       | code:TecnicalTechnical |
    When I visit the "Attributes" tab
    And I add available attributes Description
    Then I should see "Attribute successfully added to the group"
    When I visit the "History" tab
    Then there should be 3 updates
    And I should see history:
    | action | version | data                   |
    | create | 1       | code:Tecnical          |
    | update | 2       | code:TecnicalTechnical |
    | update | 3       | attributes:description |
    When I visit the "Attributes" tab
    And I remove the "Description" attribute
    Then I should see "Attribute successfully removed from the group"
    When I visit the "History" tab
    Then there should be 4 updates
    And I should see history:
    | action | version | data                   |
    | create | 1       | code:Tecnical          |
    | update | 2       | code:TecnicalTechnical |
    | update | 3       | attributes:description |
    | update | 4       | attributes:description |
