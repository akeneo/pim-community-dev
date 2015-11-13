Feature: Add attribute to a family
  In order to validate exported attributes
  As an administrator
  I need to be able to define which attributes belong to a family

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Peter"

  @jira https://akeneo.atlassian.net/browse/PIM-5147
  Scenario: Successfully list available grouped attributes without any permission restriction
    Given I am on the "colors" attribute group page
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to edit attributes | |
      | Allowed to view attributes | |
    And I save the attribute group
    When I am on the "Sandals" family page
    And I visit the "Attributes" tab
    And I should see available attribute Lace color in group "Colors"
