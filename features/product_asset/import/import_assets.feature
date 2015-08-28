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
    code;categories;localized;description;tags;end_of_use
    car;images;0;Photo of a car.;car,cities;2006-05-12
    landscape;other;1;This is a beautiful landscape!;landscape,cities,flowers;
    """
    And the following job "clothing_asset_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_asset_import" import job page
    And I launch the import job
    And I wait for the "clothing_asset_import" job to finish
    Then there should be the following assets:
      | code      | description                    | categories |
      | car       | Photo of a car.                | images     |
      | landscape | This is a beautiful landscape! | other      |
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
    code;categories;localized;description;tags;end_of_use
    paint;other;0;New description of my paint.;car,cities,vintage,awesome;2006-05-12
    akene;images;1;Beautiful akene;cities,flowers,akeneo;
    """
    And the following job "clothing_asset_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_asset_import" import job page
    And I launch the import job
    And I wait for the "clothing_asset_import" job to finish
    Then there should be the following assets:
      | code  | description                  | tags                       | categories |
      | paint | New description of my paint. | awesome,car,cities,vintage | other      |
      | akene | Beautiful akene              | akeneo,cities,flowers      | images     |
    Then I should see "read lines 2"
    And I should see "created 5"
    And I should see "processed 2"

  Scenario: Import asset file with missing required code header
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following CSV file to import:
    """
    categories;localized;description;tags;end_of_use
    images;0;New description of my paint.;car,cities;2006-05-12
    other;1;Beautiful akene;cities,flowers,akeneo;
    """
    And the following job "clothing_asset_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_asset_import" import job page
    And I launch the import job
    And I wait for the "clothing_asset_import" job to finish
    And I should see "Field \"code\" is expected, provided fields are \"categories, localized, description, tags, end_of_use\""

  Scenario: Import asset file with missing required localized header
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following CSV file to import:
    """
    code;categories;description;tags;end_of_use
    paint;other;New description of my paint.;car,cities;2006-05-12
    akene;images;Beautiful akene;cities,flowers,akeneo;
    """
    And the following job "clothing_asset_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_asset_import" import job page
    And I launch the import job
    And I wait for the "clothing_asset_import" job to finish
    And I should see "Field \"localized\" is expected, provided fields are \"code, categories, description, tags, end_of_use\""

  Scenario: Import asset with missing value for code field
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following CSV file to import:
    """
    code;categories;localized;description;tags;end_of_use
    ;image;0;New description of my paint.;car,cities;2006-05-12
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
    code;categories;localized;description;tags;end_of_use
    invalid#$%;images;0;New description of my paint.;car,cities;2006-05-12
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
    code;categories;localized;description;tags;end_of_use
    code;pack;;New description of my paint.;car,cities;2006-05-12
    """
    And the following job "clothing_asset_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_asset_import" import job page
    And I launch the import job
    And I wait for the "clothing_asset_import" job to finish
    Then I should see "Field \"localized\" must be filled"
    And I should see "Skipped 1"

  Scenario: Import asset with invalid value for field localized
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following CSV file to import:
    """
    code;categories;localized;description;tags;end_of_use
    code;3quart;Y;New description of my paint.;car,cities;2006-05-12
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
    code;categories;localized;description;tags;end_of_use
    code;audio;0;Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer venenatis pulvinar accumsan. Nam in leo ut turpis molestie ultricies. Fusce eget nulla fermentum turpis laoreet feugiat vel dapibus massa. Aenean nisi arcu, pulvinar ac dolor non, porttitor faucibus nulla. Maecenas mattis mauris in nulla tincidunt consectetur. Cras sem nisl, aliquet eu quam quis, euismod iaculis mauris. Fusce luctus sodales sodales. Phasellus non purus quis neque tristique tristique sed sit amet est. Mauris at lacus posuere.;car,cities;2006-05-12
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
    code;categories;localized;description;tags;end_of_use
    code;images;0;New description of my paint.;car,cities;2006/05/12
    """
    And the following job "clothing_asset_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_asset_import" import job page
    And I launch the import job
    And I wait for the "clothing_asset_import" job to finish
    Then I should see "Asset expects a string with the format \"yyyy-mm-dd\" as data, \"2006/05/12\" given"
    And I should see "read lines 1"
    And I should see "Skipped 1"

  Scenario: Import assets with non existent category
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following CSV file to import:
    """
    code;categories;localized;description;tags;end_of_use
    car;wrong;0;Photo of a car.;car,cities;2006-05-12
    landscape;not a category;1;This is a beautiful landscape!;landscape,cities,flowers;
    """
    And the following job "clothing_asset_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_asset_import" import job page
    And I launch the import job
    And I wait for the "clothing_asset_import" job to finish
    Then I should see "read lines 2"
    And I should see "created 4"
    And I should see "skipped 2"
    And I should see "Category with \"wrong\" code does not exist"
    And I should see "Category with \"not a category\" code does not exist"

  Scenario: Import assets with several categories
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following CSV file to import:
    """
    code;categories;localized;description;tags;end_of_use
    car;images,other,prioritized_images;0;Photo of a car.;car,cities;2006-05-12
    """
    And the following job "clothing_asset_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_asset_import" import job page
    And I launch the import job
    And I wait for the "clothing_asset_import" job to finish
    Then there should be the following assets:
      | code | description     | categories                      |
      | car  | Photo of a car. | images,other,prioritized_images |
    Then there should be the following tags:
      | code   |
      | car    |
      | cities |
    Then I should see "read lines 1"
    And I should see "created 2"
    And I should see "created 1"

  Scenario: Import assets with several categories including a non existent one
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following CSV file to import:
    """
    code;categories;localized;description;tags;end_of_use
    car;images,nonexistent,prioritized_images;0;Photo of a car.;car,cities;2006-05-12
    """
    And the following job "clothing_asset_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_asset_import" import job page
    And I launch the import job
    And I wait for the "clothing_asset_import" job to finish
    Then I should see "read lines 1"
    And I should see "skipped 1"
    And I should see "Category with \"nonexistent\" code does not exist"
