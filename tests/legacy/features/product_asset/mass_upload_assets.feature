@javascript
Feature: Mass uploads assets
  In order to create product assets
  As a product manager
  I need to be able to upload multiple assets

  Background:
    Given the "clothing" catalog configuration
    And the asset temporary file storage has been cleared
    Given I am logged in as "Pamela"

  Scenario: Successfully add and retrieve two assets
    Given I am on the asset mass upload page
    And I select the assets to upload:
    | name        |
    | akeneo.jpg  |
    | akeneo2.jpg |
    Then I should see "added" status for asset "akeneo.jpg"
    Then I should see "added" status for asset "akeneo2.jpg"
    When I start assets mass upload
    Then I should see "success" status for asset "akeneo.jpg"
    Then I should see "success" status for asset "akeneo2.jpg"
    Given I am on the dashboard page
    When I am on the asset mass upload page
    Then I should see "success" status for asset "akeneo.jpg"
    Then I should see "success" status for asset "akeneo2.jpg"

  Scenario: Validate assets file names
    Given I am on the asset mass upload page
    And I select the assets to upload:
      | name                  |
      | akeneo.jpg            |
      | akeneo-fr_FR.jpg      |
      | logo_akeneo-fr_FR.jpg |
      | chicagoskyline-de.jpg |
      | man-wall.jpg          |
      | akeneo (copy).jpg     |
      | akeneo-fo_FO.jpg      |
    Then I should see "added" status for asset "akeneo.jpg"
    Then I should see "added" status for asset "akeneo-fr_FR.jpg"
    Then I should see "added" status for asset "logo_akeneo-fr_FR.jpg"
    Then I should see "added" status for asset "man-wall.jpg"
    Then I should see "added" status for asset "chicagoskyline-de.jpg"
    Then I should see "error" status for asset "akeneo (copy).jpg"
    Then I should see "error" status for asset "akeneo-fo_FO.jpg"

  Scenario: Cannot add the same file two times
    Given I am on the asset mass upload page
    And I select the assets to upload:
    | name       |
    | akeneo.jpg |
    Then I should see "added" status for asset "akeneo.jpg"
    And I start assets mass upload
    When I select the assets to upload:
      | name       |
      | akeneo.jpg |
    Then I should see "error" status for asset "akeneo.jpg"

  Scenario: Cancel uploads
    Given I am on the asset mass upload page
    And I select the assets to upload:
      | name       |
      | akeneo.jpg |
    And I remove assets mass upload
    Then I should not see "akeneo.jpg"
    And The button "Import" should be disabled
    When I select the assets to upload:
      | name       |
      | akeneo.jpg |
    And I start assets mass upload
    Then I should see "success" status for asset "akeneo.jpg"
    And I should see the text "Import"
    When I delete asset upload
    Then I should not see "akeneo.jpg"
    And The button "Import" should be disabled
    When I select the assets to upload:
      | name        |
      | akeneo.jpg  |
      | akeneo2.jpg |
    And I start assets mass upload
    Then I should see "success" status for asset "akeneo.jpg"
    And I should see "success" status for asset "akeneo2.jpg"
    And I should see the text "Import"
    When I remove assets mass upload
    Then I should not see "akeneo.jpg"
    And I should not see "akeneo2.jpg"
    And The button "Import" should be disabled

  Scenario: Complete mass upload
    Given I am on the asset mass upload page
    And I select the assets to upload:
      | name                  |
      | akeneo.jpg            |
      | akeneo2.jpg           |
      | logo_akeneo-fr_FR.jpg |
      | man-wall.jpg          |
    And I start assets mass upload
    And I import assets mass upload
    And I wait 5 seconds
    Then I should be on the last "apply_assets_mass_upload" import job page
    And I should see the text "Asset created from file 3"
    And I should see the text "Asset updated 1"
    When I am on the job tracker page
    Then I should see the text "Mass Upload Assets"
    And I should see the text "COMPLETED"
    When I am on the assets grid
    Then I should see the text "akeneo"
    And I should see the text "akeneo2"
    And I should see the text "logo_akeneo"
    And I should see the text "man_wall"
    And I should have 1 new notification
    And I should see notification:
      | type    | message              |
      | success | Mass upload executed |
