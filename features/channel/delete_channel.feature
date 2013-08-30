@javascript
Feature: Delete a channel
  In order to manage channels for the catalog
  As a user
  I need to be able to delete channels

  Background:
    Given the following categories:
      | code           | title          |
      | ipad_catalog   | iPad Catalog   |
    And the following channels:
      | code | name  | locales      | category       |
      | FOO  | foo   | fr_FR, en_US | ipad_catalog   |
    And I am logged in as "admin"

  Scenario: Successfully delete a channel from the grid
    Given I am on the channels page
    And I should see channel FOO
    When I click on the "Delete" action of the row which contains "FOO"
    