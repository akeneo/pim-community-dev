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
    And I am on the asset mass upload page
    And I select the assets to upload:
    | name        |
    | akeneo.jpg  |
    | akeneo2.jpg |
    Then I should see "Added" status for asset "akeneo.jpg"
    Then I should see "Added" status for asset "akeneo2.jpg"
    When I start assets mass upload
    Then I should see "Success" status for asset "akeneo.jpg"
    Then I should see "Success" status for asset "akeneo2.jpg"
    Given I am on the dashboard page
    When I am on the asset mass upload page
    Then I should see "Success" status for asset "akeneo.jpg"
    Then I should see "Success" status for asset "akeneo2.jpg"

  Scenario: Validate assets file names
    And I am on the asset mass upload page
    And I select the assets to upload:
      | name        |
      | akeneo.jpg  |
      | akeneo-fr_FR.jpg |
      | logo_akeneo-fr_FR.jpg |
      | chicagoskyline-de.jpg |
      | akeneo (copy).jpg |
    Then I should see "Added" status for asset "akeneo.jpg"
    Then I should see "Added" status for asset "akeneo-fr_FR.jpg"
    Then I should see "Error" status for asset "logo_akeneo-fr_FR.jpg"
    Then I should see "Error" status for asset "chicagoskyline-de.jpg"
    Then I should see "Error" status for asset "akeneo (copy).jpg"

  Scenario: Cannot add the same file two times
    And I am on the asset mass upload page
    And I select the assets to upload:
    | name        |
    | akeneo.jpg  |
    Then I should see "Added" status for asset "akeneo.jpg"
    And I start assets mass upload
    When I select the assets to upload:
      | name        |
      | akeneo.jpg  |
    Then I should see "Error" status for asset "akeneo.jpg"

  Scenario: Enforce asset validation before upload
    And I am on the asset mass upload page
    And I select the assets to upload:
    | name              |
    | akeneo.jpg        |
    | akeneo-fr_FR.jpg  |
    | bic-core-148.gif  |
    Then I should see "Added" status for asset "akeneo.jpg"
    Then I should see "Added" status for asset "akeneo-fr_FR.jpg"
    Then I should see "Error" status for asset "bic-core-148.gif"

  Scenario: Cancel uploads
    And I am on the asset mass upload page
    And I select the assets to upload:
      | name        |
      | akeneo.jpg  |
    And I cancel assets mass upload
    Then I should not see "akeneo.jpg"
    And I should not see "schedule"
    When I select the assets to upload:
      | name        |
      | akeneo.jpg  |
    And I start assets mass upload
    Then I should see "Success" status for asset "akeneo.jpg"
    And I should see "Schedule"
    When I delete asset upload
    Then I should not see "akeneo.jpg"
    And I should not see "Schedule"
    When I select the assets to upload:
      | name              |
      | akeneo.jpg        |
      | akeneo2.jpg |
    And I start assets mass upload
    Then I should see "Success" status for asset "akeneo.jpg"
    And I should see "Success" status for asset "akeneo2.jpg"
    And I should see "Schedule"
    When I cancel assets mass upload
    Then I should not see "akeneo.jpg"
    And I should not see "akeneo2.jpg"
    And I should not see "schedule"

  Scenario: Complete mass upload
    And I am on the asset mass upload page
    And I select the assets to upload:
      | name        |
      | akeneo.jpg  |
      | akeneo2.jpg  |
    And I start assets mass upload
    And I schedule assets mass upload
    And I wait 2 seconds
    Then I should not see "akeneo.jpg"
    And I should not see "akeneo2.jpg"
    And I am on the assets page
    And I change the page size to 25
    Then I should see "akeneo"
    And I should see "akeneo2"
    And I should have 1 new notification
    And I should see notification:
      | type    | message                                        |
      | success | Mass upload executed |
    When I am on the job tracker page
    Then I should see "mass_upload"
    And I should see "COMPLETED"
