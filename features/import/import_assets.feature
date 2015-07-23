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
    code;localized;description;qualification;end_of_use
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
    Then I should see "read lines 2"
    And I should see "created 4"
    And I should see "created 2"

  Scenario: Import and update existing assets
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following CSV file to import:
    """
    code;localized;description;qualification;end_of_use
    paint;0;New description of my paint.;car,cities,vintage,awesome;2006-05-12
    akene;1;Beautiful akene;cities,flowers,akeneo;
    """
    And the following job "clothing_asset_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_asset_import" import job page
    And I launch the import job
    And I wait for the "clothing_asset_import" job to finish
    Then there should be the following assets:
      | code  | description                  | tags                                                                                                        |
      | paint | New description of my paint. | awesome,backless,big_sizes,car,cities,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage    |
      | akene | Beautiful akene              | akeneo,backless,big_sizes,cities,dress_suit,flower,flowers,neckline,pattern,pea,solid_color,stripes,vintage |
    Then I should see "read lines 2"
    And I should see "created 5"
    And I should see "processed 2"

  Scenario: Import asset file with missing required code header
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following CSV file to import:
    """
    localized;description;qualification;end_of_use
    0;New description of my paint.;car,cities;2006-05-12
    1;Beautiful akene;cities,flowers,akeneo;
    """
    And the following job "clothing_asset_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_asset_import" import job page
    And I launch the import job
    And I wait for the "clothing_asset_import" job to finish
    And I should see "Field \"code\" is expected, provided fields are \"localized, description, qualification, end_of_use\""

  Scenario: Import asset file with missing required localized header
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following CSV file to import:
    """
    code;description;qualification;end_of_use
    paint;New description of my paint.;car,cities;2006-05-12
    akene;Beautiful akene;cities,flowers,akeneo;
    """
    And the following job "clothing_asset_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_asset_import" import job page
    And I launch the import job
    And I wait for the "clothing_asset_import" job to finish
    And I should see "Field \"localized\" is expected, provided fields are \"code, description, qualification, end_of_use\""

  Scenario: Import asset with missing value for code field
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following CSV file to import:
    """
    code;localized;description;qualification;end_of_use
    ;0;New description of my paint.;car,cities;2006-05-12
    """
    And the following job "clothing_asset_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_asset_import" import job page
    And I launch the import job
    And I wait for the "clothing_asset_import" job to finish
    Then I should see "Field \"code\" must be filled"

  Scenario: Import asset with invalid value for code field
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following CSV file to import:
    """
    code;localized;description;qualification;end_of_use
    invliad#$%;0;New description of my paint.;car,cities;2006-05-12
    """
    And the following job "clothing_asset_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_asset_import" import job page
    And I launch the import job
    And I wait for the "clothing_asset_import" job to finish
    Then I should see "Attribute code may contain only letters, numbers and underscores."

  Scenario: Import asset with missing value for field localized
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following CSV file to import:
    """
    code;localized;description;qualification;end_of_use
    code;;New description of my paint.;car,cities;2006-05-12
    """
    And the following job "clothing_asset_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_asset_import" import job page
    And I launch the import job
    And I wait for the "clothing_asset_import" job to finish
    Then I should see "Field \"localized\" must be filled"

  Scenario: Import asset with invalid value for field localized
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following CSV file to import:
    """
    code;localized;description;qualification;end_of_use
    code;Y;New description of my paint.;car,cities;2006-05-12
    """
    And the following job "clothing_asset_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_asset_import" import job page
    And I launch the import job
    And I wait for the "clothing_asset_import" job to finish
    Then I should see "Localized field contains invalid data only \"0\" or \"1\" is accepted"

  Scenario: Import asset with too long value for field description
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following CSV file to import:
    """
    code;localized;description;qualification;end_of_use
    code;0;Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer venenatis pulvinar accumsan. Nam in leo ut turpis molestie ultricies. Fusce eget nulla fermentum turpis laoreet feugiat vel dapibus massa. Aenean nisi arcu, pulvinar ac dolor non, porttitor faucibus nulla. Maecenas mattis mauris in nulla tincidunt consectetur. Cras sem nisl, aliquet eu quam quis, euismod iaculis mauris. Fusce luctus sodales sodales. Phasellus non purus quis neque tristique tristique sed sit amet est. Mauris at lacus posuere.;car,cities;2006-05-12
    """
    And the following job "clothing_asset_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_asset_import" import job page
    And I launch the import job
    And I wait for the "clothing_asset_import" job to finish
    Then I should see "description: This value is too long. It should have 500 characters or less"
    And I should see "read lines 1"
    And I should see "Skipped 1"

  Scenario: Import asset with invalid value for field en_of_use_at
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following CSV file to import:
    """
    code;localized;description;qualification;end_of_use
    code;0;New description of my paint.;car,cities;2006/05/12
    """
    And the following job "clothing_asset_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_asset_import" import job page
    And I launch the import job
    And I wait for the "clothing_asset_import" job to finish
    Then I should see "Asset expects a string with the format \"yyyy-mm-dd\" as data, \"2006/05/12\" given"
    And I should see "read lines 1"
    And I should see "Skipped 1"
