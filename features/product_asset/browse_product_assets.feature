@javascript
Feature: Browse product assets
  In order to list the existing product assets
  As an asset manager
  I need to be able to see product assets

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Pamela"

  Scenario: Successfully display product assets
    And I am on the "paint" asset page
    And I visit the "Variations" tab
    And I upload the reference file akene.jpg
    And I save the asset
    And I should not see the text "There are unsaved changes."
    And I should see the text "akene.jpg"
    And I am on the "chicagoskyline" asset page
    And I visit the "Variations" tab
    And I switch the locale to "de_DE"
    And I upload the reference file akene.jpg
    And I save the asset
    And I should not see the text "There are unsaved changes."
    And I should see the text "akene.jpg"
    And I visit the "Variations" tab
    And I switch the locale to "en_US"
    And I upload the reference file akene.jpg
    And I save the asset
    And I should not see the text "There are unsaved changes."
    And I should see the text "akene.jpg"
    And I am on the assets grid
    Then the grid should contain 15 elements
    And I should see the columns Thumbnail, Code, Description, Tags, End of use, Created at and Last updated at
    And the row "paint" should contain the thumbnail for channel "mobile"
    And the row "chicagoskyline" should contain the thumbnail for channel "mobile" and locale "en_US"
    When I switch the locale to "de_DE"
    And I switch the scope to "Tablet"
    Then the row "paint" should contain the thumbnail for channel "tablet"
    And the row "chicagoskyline" should contain the thumbnail for channel "tablet" and locale "de_DE"

  @jira https://akeneo.atlassian.net/browse/PIM-5400
  Scenario: Successfully display 10 product asset rows in the grid
    And I am on the assets grid
    And the row "paint" should contain:
      | column      | value             |
      | description | Photo of a paint. |
    And the row "chicagoskyline" should contain:
      | column      | value            |
      | description | This is chicago! |
    And the row "akene" should contain:
      | column      | value          |
      | description | Because Akeneo |
    And the row "autumn" should contain:
      | column      | value            |
      | description | Leaves and water |
    And the row "bridge" should contain:
      | column      | value                                       |
      | description | Architectural bridge of a city, above water |
    And the row "dog" should contain:
      | column      | value                                    |
      | description | Obviously not a cat, but still an animal |
    And the row "eagle" should contain:
      | column      | value |
      | description |       |
    And the row "machine" should contain:
      | column      | value         |
      | description | A big machine |
    And the row "man_wall" should contain:
      | column      | value |
      | description |       |
    And the row "minivan" should contain:
      | column      | value  |
      | description | My car |
