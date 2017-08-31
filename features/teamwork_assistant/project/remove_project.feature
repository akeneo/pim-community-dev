@javascript
Feature: Remove project
  In order to keep a clean teamwork assistant
  As a project creator
  I need to be able to remove a project

  Background:
    Given a "footwear" catalog configuration
    And the following products:
      | sku         | family   | categories        |
      | blue_sandal | Sneakers | summer_collection |
    And I am logged in as "Julia"
    And I am on the products grid
    And I click on the create project button
    And I fill in the following information in the popin:
      | project-label    | Star Wars Collection |
      | project-due-date | 01/31/2051           |
    And I press the "Save" button
    And I go on the last executed job resume of "project_calculation"
    And I wait for the "project_calculation" job to finish
    And I logout

  Scenario: A project creator can remove his project
    Given I am logged in as "Julia"
    And I am on the products grid
    And I switch view selector type to "Projects"
    And I should see the text "Star Wars Collection"
    When I click on the remove project button
    And I confirm the deletion
    Then I should see the text "Views"
    When I switch view selector type to "Projects"
    Then I should not see the text "Star Wars Collection"

  Scenario: A contributor doesn't see the button to remove a project if he's not the creator
    Given I am logged in as "Mary"
    And I am on the products grid
    When I switch view selector type to "Projects"
    Then I should see the text "Star Wars Collection"
    But I should not see the "Delete project" icon button
