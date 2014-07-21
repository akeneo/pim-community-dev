Feature: Attribute group creation
  In order to organize attributes into group
  As a product manager
  I need to be able to create an attribute group

  Scenario: Succesfully create an attribute group
    Given the "default" catalog configuration
    And I am logged in as "Julia"
    When I am on the attribute group creation page
    And I change the Code to "seo"
    And I save the attribute group
    Then I should see "Attribute group successfully created"
    And I should be on the "seo" attribute group page
