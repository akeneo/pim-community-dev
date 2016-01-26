@javascript
Feature: Edit an association type
  In order to manage existing association types in the catalog
  As a product manager
  I need to be able to edit an association type

  Background:
    Given a "footwear" catalog configuration
    And I am logged in as "Julia"

  Scenario: Successfully edit an association type
    Given I am on the "SUBSTITUTION" association type page
    Then I should see the Code field
    And the field Code should be disabled
    When I fill in the following information:
      | English (United States) | My substitution |
    And I press the "Save" button
    Then I should see "My substitution"

  Scenario: Successfully display a dialog when we quit a page with unsaved changes
    Given I am on the "PACK" association type page
    When I fill in the following information:
      | English (United States) | My pack |
    And I click on the Akeneo logo
    Then I should see a confirm dialog with the following content:
      | title   | Are you sure you want to leave this page?                             |
      | content | You will lose changes to the association type if you leave this page. |

  Scenario: Successfully display a message when there are unsaved changes
    Given I am on the "PACK" association type page
    When I fill in the following information:
      | English (United States) | My pack |
    Then I should see the text "There are unsaved changes."

  Scenario: Successfully retrieve the last visited tab
    Given I am on the "PACK" association type page
    And I visit the "History" tab
    And I am on the products page
    Then I am on the "PACK" association type page
    And I should see "version"
    And I should see "author"

  Scenario: Successfully retrieve the last visited tab after a save
    Given I am on the "PACK" association type page
    And I visit the "History" tab
    And I save the "association type"
    And I should see "version"
    And I should see "author"
