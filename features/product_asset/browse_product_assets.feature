@javascript
Feature: Browse product assets
  In order to list the existing product assets
  As an asset manager
  I need to be able to see product assets

  Background:
    Given a "clothing" catalog configuration
    And I generate missing variations
    And I am logged in as "Pamela"
    And I am on the assets page

  Scenario: Successfully display product assets
    Then the grid should contain 16 elements
    And I should see the columns Thumbnail, Code, Description, Tags, End of use, Created at and Last updated at
    And the row "paint" should contain the thumbnail for channel "mobile"
    And the row "chicagoskyline" should contain the thumbnail for channel "mobile" and locale "en_US"
    When I switch the locale to "German (Germany)"
    And I filter by "channel" with value "Tablet"
    Then the row "paint" should contain the thumbnail for channel "tablet"
    And the row "chicagoskyline" should contain the thumbnail for channel "tablet" and locale "de_DE"
