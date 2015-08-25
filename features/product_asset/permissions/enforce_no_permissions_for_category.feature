Feature: Enforce no permissions for an asset category
  In order to be able to prevent some users from viewing some assets
  As an administrator
  I need to be able to enforce no permissions for categories

  Background:
    Given a "clothing" catalog configuration
    And the following assets:
      | code       | categories         |
      | grantedOne | prioritized_images |
      | grantedTwo | prioritized_images |
      | notEdit    | prioritized_videos |
      | notView    | prioritized_videos |

  @javascript
  Scenario: Display only granted assets in assets grid, I see all assets
    Given I am logged in as "Mary"
    And I am on the assets page
    And the grid should contain 15 elements

  @javascript
  Scenario: Display only granted assets in assets grid, I see a sub set of assets
    Given I am logged in as "Mary"
    And I edit the "prioritized_images" asset category
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to view assets | Manager |
      | Allowed to edit assets | Manager |
    And I save the category
    And I am on the assets page
    And the grid should contain 13 elements

  @javascript
  Scenario: Display only granted assets in assets grid when filtering by unclassified
    Given the following assets:
      | code            | categories |
      | unclassifiedOne |            |
      | unclassifiedTwo |            |
      | inProtectedTree | images     |
      | inProtectedNode | images     |
    And I am logged in as "Pamela"
    And I am on the assets page
    When I filter by "asset category" with value "unclassified"
    Then the grid should contain 5 elements
    And I should see assets unclassifiedOne and unclassifiedTwo
    But I should not see assets inProtectedTree and inProtectedNode

  @javascript
  Scenario: Redirect user on view of the asset if he has no permission to edit it
    Given I am logged in as "Mary"
    And I am on the assets page
    And I change the page size to 25
    And I click on the "notEdit" row
    And I should not see the "Save" button

  @javascript
  Scenario: Go to edit form of the asset if he has permission to edit it
    Given I am logged in as "Mary"
    And I am on the assets page
    And I change the page size to 25
    And I click on the "grantedOne" row
    And I should see the "Save" button
