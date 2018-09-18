@javascript
Feature: Attribute group creation
  In order to organize attributes into group
  As a product manager
  I need to be able to create an attribute group

  @skip @critical
  Scenario: Successfully create an attribute group
    Given the "default" catalog configuration
    And I am logged in as "Julia"
    When I am on the attribute group creation page
    And I change the Code to "seo"
    And I save the attribute group
    Then I should see the text "Attribute group successfully created"
    And I should be on the "seo" attribute group page

  Scenario: Newly created attribute group is sorted after existing ones
    Given the "default" catalog configuration
    And I am logged in as "Julia"
    When I am on the attribute group creation page
    And I change the Code to "seo"
    And I save the attribute group
    When I visit the "History" tab
    And I should see history:
      | version | property   | value | date |
      | 1       | sort_order | 101   | now  |
