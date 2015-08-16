Feature: Edit an asset category
  In order to be able to modify the asset category tree
  As a product manager
  I need to be able to edit an asset category

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"

  @javascript
  Scenario: Successfully edit an asset category
    Given I edit the "images" asset category
    Then I should see the Code field
    And the field Code should be disabled
    When I fill in the following information:
      | English (United States) | My images |
    And I save the category
    Then I should be on the asset category "images" edit page
    And I should see "My images"

  @javascript
  Scenario: Go to category edit page from the asset category tree
    Given I am on the assets categories page
    And I select the "Asset main catalog" tree
    And I click on the "Videos" category
    Then I should be on the asset category "videos" edit page

  Scenario: Successfully display a dialog when we quit a page with unsaved changes
    Given I edit the "client_documents" asset category
    When I fill in the following information:
      | English (United States) | 2015 Clients documents |
    And I click on the Akeneo logo
    Then I should see a confirm dialog with the following content:
      | title   | Are you sure you want to leave this page?                    |
      | content | You will lose changes to the category if you leave the page. |

  @javascript @skip
  Scenario: Successfully display a message when there are unsaved changes
    Given I edit the "images" asset category
    When I fill in the following information:
      | English (United States) | My images |
    Then I should see "There are unsaved changes."
