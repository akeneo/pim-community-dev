@javascript
Feature: Import assets
  In order to use the assets
  As a product manager
  I need to be able to import assets

  Scenario: Import assets with tags
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following CSV file to import:
    """
    code;localized;description;qualification;end_of_use_at
    car;0;Photo of a car.;car,cities;2006-05-12
    landscape;1;This is a beautiful landscape!;landscape,cities,flowers;
    """
    And the following job "clothing_asset_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_asset_import" import job page
    And I launch the import job
    And I wait for the "clothing_asset_import" job to finish
    Then there should be the following assets:
      | code      | description                    |
      | car       | Photo of a car.                |
      | landscape | This is a beautiful landscape! |
    Then there should be the following tags:
      | code      |
      | car       |
      | landscape |
      | cities    |
      | flowers   |
    Then I should see "read 2"
    And I should see "created 4"
    And I should see "created 2"
