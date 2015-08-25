@javascript
Feature: Define permissions for an asset category
  In order to be able to prevent some users from viewing some assets
  As an administrator
  I need to be able to define permissions for categories

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Peter"

  Scenario: Create an asset category keeps the parent's permissions
    Given I am on the asset category "asset_main_catalog" node creation page
    And I fill in the following information:
      | Code | newcategory |
    And I save the category
    When I edit the "newcategory" asset category
    And I visit the "Permissions" tab
    Then I should see the permission Allowed to view assets with user groups All, IT support, Manager and Redactor
    And I should see the permission Allowed to edit assets with user groups All, IT support, Manager and Redactor

  Scenario: By default, update children when the parent's permissions are changed
    Given the following assets categories:
      | code            | label-en_US     | parent |
      | books           | Books           |        |
      | comics          | Comics          | books  |
      | science_fiction | Science fiction | books  |
    And I edit the "books" asset category
    And I visit the "Permissions" tab
    And I fill in the following information:
      | Allowed to view assets | Manager |
    And I save the category
    When I edit the "comics" asset category
    And I visit the "Permissions" tab
    Then I should see the permission Allowed to view assets with user groups Manager
