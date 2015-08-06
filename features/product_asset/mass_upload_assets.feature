@javascript
Feature: Mass uploads assets
  In order to create product assets
  As a product manager
  I need to be able to upload multiple assets

  Background:
    Given the "clothing" catalog configuration

  Scenario: Successfully add and retrieve two assets
    Given I am logged in as "Pamela"
    And I am on the asset mass upload page
    And I select the assets to upload:
    | name        |
    | akeneo.jpg  |
    | akeneo2.jpg |
    Then I should see "ADDED" for asset "akeneo.jpg"
    Then I should see "ADDED" for asset "akeneo2.jpg"
    When I start assets mass upload
    Then I should see "SUCCESS" for asset "akeneo.jpg"
    Then I should see "SUCCESS" for asset "akeneo2.jpg"
    Given I am on the dashboard page
    When I am on the asset mass upload page
    Then I should see "SUCCESS" for asset "akeneo.jpg"
    Then I should see "SUCCESS" for asset "akeneo2.jpg"

  Scenario: Cannot add the same file two times
    Given I am logged in as "Pamela"
    And I am on the asset mass upload page
    And I select the assets to upload:
    | name        |
    | akeneo.jpg  |
    Then I should see "ADDED" for asset "akeneo.jpg"
    And I start assets mass upload
    When I select the assets to upload:
      | name        |
      | akeneo.jpg  |
    Then I should see "ERROR" for asset "akeneo.jpg"

  Scenario: Cannot schedule the same file two times
    Given I am logged in as "Pamela"
    And I am on the asset mass upload page
    And I select the assets to upload:
    | name        |
    | akeneo.jpg  |
    Then I should see "ADDED" for asset "akeneo.jpg"
    And I start assets mass upload
    And I schedule assets mass upload
    When I select the assets to upload:
      | name        |
      | akeneo.jpg  |
    Then I should see "ERROR" for asset "akeneo.jpg"

  Scenario: Enforce asset validation before upload
    Given I am logged in as "Pamela"
    And I am on the asset mass upload page
    And I select the assets to upload:
    | name              |
    | akeneo.jpg        |
    | akeneo-fr_FR.jpg  |
    | bic-core-148.gif  |
    Then I should see "ADDED" for asset "akeneo.jpg"
    Then I should see "ADDED" for asset "akeneo-fr_FR.jpg"
    Then I should see "ERROR" for asset "bic-core-148.gif"
