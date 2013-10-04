Feature: Attribute group creation
  In order to organize attributes into group
  As Julia
  I need to be able to create a group

  Scenario: Succesfully create a group
    Given I am logged in as "Julia"
    When I am on the group creation page
    And I change the Code to "seo"
    And I save the group
    Then I should see "Attribute group successfully created"
    And I should be on the "seo" group page
