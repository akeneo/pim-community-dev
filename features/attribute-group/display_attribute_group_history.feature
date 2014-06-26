Feature: Display the attribute group history
  In order to know who, when and what changes has been made to an attribute group
  As a product manager
  I need to have access to attribute group history

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Julia"
    And the following attributes:
      | label       |
      | Description |

  @javascript
  Scenario: Succesfully edit a group and see the history
    Given I am on the attribute group creation page
    And I change the Code to "Technical"
    And I save the group
    When I visit the "History" tab
    Then there should be 1 update
    And I should see history:
      | version | property | value     |
      | 1       | code     | Technical |

    When I visit the "Properties" tab
    And I fill in the following information:
      | English (United States) | My technical group |
    And I save the group
    When I visit the "History" tab
    Then there should be 2 updates
    And I should see history:
      | version | property    | value              |
      | 1       | code        | Technical          |
      | 2       | label-en_US | My technical group |

    When I visit the "Attributes" tab
    And I add available attributes Description
    Then I should see flash message "Attributes successfully added to the attribute group"
    When I visit the "History" tab
    Then there should be 3 updates
    And I should see history:
      | version | property    | value              |
      | 1       | code        | Technical          |
      | 2       | label-en_US | My technical group |
      | 3       | attributes  | description        |

    When I visit the "Attributes" tab
    And I remove the "Description" attribute
    Then I should see flash message "Attribute successfully removed from the attribute group"
    When I visit the "History" tab
    Then there should be 4 updates
    And I should see history:
      | version | property    | value              |
      | 1       | code        | Technical          |
      | 2       | label-en_US | My technical group |
      | 3       | attributes  | description        |
      | 4       | attributes  |                    |
