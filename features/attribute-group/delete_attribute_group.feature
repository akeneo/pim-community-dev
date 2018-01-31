@javascript
Feature: Attribute group creation
  In order to organize attributes into group
  As Julia
  I need to be able to delete an attribute group

  Background:
    Given the "default" catalog configuration
    And the following attribute group:
      | code   | label-en_US |
      | sizes  | Sizes       |
      | colors | Colors      |
    And the following attributes:
      | code | group  | type             |
      | red  | colors | pim_catalog_text |
    And I am logged in as "Julia"

  Scenario: Successfully delete an attribute group
    Given I am on the "sizes" attribute group page
    When I press the secondary action "Delete"
    And I confirm the deletion
    Then I should see the flash message "Attribute group successfully removed"

  Scenario: Fail to delete an attribute group that contains attributes
    Given I am on the "colors" attribute group page
    When I press the secondary action "Delete"
    And I confirm the deletion
    Then I should see the flash message "Attribute group containing attributes cannot be removed. Please remove its attributes prior to delete it."

  Scenario: Fail to delete the attribute "other"
    Given I am on the "other" attribute group page
    When I press the secondary action "Delete"
    And I confirm the deletion
    Then I should see the flash message "Attribute group \"other\" cannot be removed."
