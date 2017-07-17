@javascript
Feature: Create attribute options
  In order to define choices for a choice attribute
  As a product manager
  I need to create options for attributes of type "Multi select" and "Simple select"

  Background:
    Given the "default" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully create some attribute options
    Given I am on the attributes page
    And I create a "Simple select" attribute
    And I fill in the following information:
      | Code            | size  |
      | Attribute group | Other |
    When I save the attribute
    And I visit the "Options" tab
    And I should not see the text "There are unsaved changes."
    When I check the "Sort automatically options by alphabetical order" switch
    And I create the following attribute options:
      | Code  |
      | red   |
      | blue  |
      | green |
    Then I should see the text "green"
    When I save the attribute
    Then I should see the flash message "Attribute successfully updated"
    And I should see the text "green"

  @jira https://akeneo.atlassian.net/browse/PIM-6033
  Scenario: Successfully create several empty attribute options but save only filled options
    Given the following attributes:
      | code | type                     | localizable | scopable | group |
      | size | pim_catalog_simpleselect | 0           | 0        | other |
    And I am on the "size" attribute page
    When I visit the "Options" tab
    And I create the following attribute options:
      | Code  | en_US | fr_FR |
      | red   |       |       |
      | blue  |       |       |
      | green |       |       |
      | grey  | Grey  | Gris  |
    And I update the last attribute option
    And I add an empty attribute option
    And I add an empty attribute option
    And I add an empty attribute option
    And I save the attribute
    Then the Options section should contain 4 options

  @jira https://akeneo.atlassian.net/browse/PIM-6322
  Scenario: Successfully display validation message for empty attribute option code
    Given the following attributes:
      | code | type                     | localizable | scopable | group |
      | size | pim_catalog_simpleselect | 0           | 0        | other |
    And I am on the "size" attribute page
    When I visit the "Options" tab
    And I create the following attribute options:
      | Code      |
      | shoe size |
    Then I should see validation tooltip "Option code may contain only letters, numbers and underscores"
