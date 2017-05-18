Feature: Add attribute to a family
  In order to validate exported attributes
  As an administrator
  I need to be able to define which attributes belong to a family

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"

  @javascript @jira https://akeneo.atlassian.net/browse/PIM-6196
  Scenario: Successfully list available grouped attributes without any permission restriction
    Given I am on the "colors" attribute group page
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to edit attributes |  |
      | Allowed to view attributes |  |
    And I save the attribute group
    And I should not see the text "There are unsaved changes."
    When I am on the "sandals" family page
    And I visit the "Attributes" tab
    Then I should see attributes "Color" in group "Colors"
    And I should not see available attribute Lace color in group "Colors"
