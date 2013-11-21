Feature: Display the family history
  In order to know who, when and what changes has been made to an family
  As a user
  I need to have access to a family history

  Background:
    Given the "default" catalog configuration
    And I am logged in as "admin"
    And the following attribute group:
      | code    | label   |
      | general | General |
    And the following product attributes:
      | label       | group   |
      | Description | General |

  @javascript
  Scenario: Succesfully edit a family and see the history
    Given I am on the family creation page
    And I change the Code to "Flyer"
    And I save the family
    And I edit the "Flyer" family
    When I visit the "History" tab
    Then there should be 1 update
    And I should see history:
      | action | version | property | value |
      | create | 1       | code     | Flyer |

    When I visit the "Properties" tab
    And I change the english Label to "Fly"
    And I save the family
    When I visit the "History" tab
    Then there should be 2 updates
    And I should see history:
      | action | version | property    | value |
      | create | 1       | code        | Flyer |
      | update | 2       | label-en_US | Fly   |

    When I visit the "Attributes" tab
    And I add available attributes Description
    When I visit the "History" tab
    Then there should be 3 updates
    And I should see history:
      | action | version | property    | value           |
      | create | 1       | code        | Flyer           |
      | update | 2       | label-en_US | Fly             |
      | update | 3       | attributes  | sku,description |

    When I visit the "Attributes" tab
    And I remove the "Description" attribute
    Then I should see flash message "Attribute successfully removed from the family"
    When I visit the "History" tab
    Then there should be 4 updates
    And I should see history:
      | action | version | property    | value           |
      | create | 1       | code        | Flyer           |
      | update | 2       | label-en_US | Fly             |
      | update | 3       | attributes  | sku,description |
      | update | 4       | attributes  | sku             |
