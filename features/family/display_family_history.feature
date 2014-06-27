Feature: Display the family history
  In order to know who, when and what changes has been made to an family
  As an administrator
  I need to have access to a family history

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Peter"
    And the following attribute group:
      | code    | label-en_US |
      | general | General     |
    And the following attributes:
      | label       | group   |
      | Description | General |

  @javascript
  Scenario: Succesfully create a family and see the history
    Given I am on the families page
    And I create a new family
    And I fill in the following information in the popin:
      | Code | Flyer |
    And I save the family
    And I edit the "Flyer" family
    When I visit the "History" tab
    Then there should be 1 update
    And I should see history:
      | version | property | value |
      | 1       | code     | Flyer |

    When I visit the "Properties" tab
    And I fill in the following information:
      | English (United States) | Fly |
    And I save the family
    When I visit the "History" tab
    Then there should be 2 updates
    And I should see history:
      | version | property    | value |
      | 1       | code        | Flyer |
      | 2       | label-en_US | Fly   |
