@javascript
Feature: Import assets
  In order to use the assets
  As a product manager
  I need to be able to import assets

  Scenario: Import assets with tags from CSV
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following CSV file to import:
    """
    code;categories;localized;description;tags;end_of_use
    car;images;0;Photo of a car.;car,cities;2006-05-12
    landscape;other;1;This is a beautiful landscape!;landscape,cities,flowers;
    """
    And the following job "csv_clothing_asset_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_clothing_asset_import" import job page
    And I launch the import job
    And I wait for the "csv_clothing_asset_import" job to finish
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
    Then I should see the text "read lines 2"
    And I should see the text "created 4"
    And I should see the text "created 2"

  Scenario: Import and update existing assets
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following CSV file to import:
    """
    code;categories;localized;description;tags;end_of_use
    paint;other;0;New description of my paint.;car,cities,vintage,awesome;2006-05-12
    akene;images;0;Beautiful akene;cities,flowers,akeneo;
    """
    And the following job "csv_clothing_asset_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_clothing_asset_import" import job page
    And I launch the import job
    And I wait for the "csv_clothing_asset_import" job to finish
    Then there should be the following assets:
      | code  | description                  | tags                       | categories |
      | paint | New description of my paint. | awesome,car,cities,vintage | other      |
      | akene | Beautiful akene              | akeneo,cities,flowers      | images     |
    Then I should see the text "read lines 2"
    And I should see the text "created 5"
    And I should see the text "processed 2"

  Scenario: Import asset file with missing required code header
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following CSV file to import:
    """
    categories;localized;description;tags;end_of_use
    images;0;New description of my paint.;car,cities;2006-05-12
    other;1;Beautiful akene;cities,flowers,akeneo;
    """
    And the following job "csv_clothing_asset_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_clothing_asset_import" import job page
    And I launch the import job
    And I wait for the "csv_clothing_asset_import" job to finish
    And I should see the text "Field \"code\" is expected, provided fields are \"categories, localized, description, tags, end_of_use\""

  Scenario: Import asset file with missing required localized header
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following CSV file to import:
    """
    code;categories;description;tags;end_of_use
    paint;other;New description of my paint.;car,cities;2006-05-12
    akene;images;Beautiful akene;cities,flowers,akeneo;
    """
    And the following job "csv_clothing_asset_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_clothing_asset_import" import job page
    And I launch the import job
    And I wait for the "csv_clothing_asset_import" job to finish
    And I should see the text "Field \"localized\" is expected, provided fields are \"code, categories, description, tags, end_of_use\""

  Scenario: Import asset with missing value for code field
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following CSV file to import:
    """
    code;categories;localized;description;tags;end_of_use
    ;image;0;New description of my paint.;car,cities;2006-05-12
    """
    And the following job "csv_clothing_asset_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_clothing_asset_import" import job page
    And I launch the import job
    And I wait for the "csv_clothing_asset_import" job to finish
    Then I should see the text "Field \"code\" must be filled"

  Scenario: Import asset with invalid value for code field
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following CSV file to import:
    """
    code;categories;localized;description;tags;end_of_use
    invalid#$%;images;0;New description of my paint.;car,cities;2006-05-12
    """
    And the following job "csv_clothing_asset_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_clothing_asset_import" import job page
    And I launch the import job
    And I wait for the "csv_clothing_asset_import" job to finish
    Then I should see the text "Asset code may contain only letters, numbers and underscores: invalid#$%"

  Scenario: Import asset with missing value for field localized
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following CSV file to import:
    """
    code;categories;localized;description;tags;end_of_use
    code;pack;;New description of my paint.;car,cities;2006-05-12
    """
    And the following job "csv_clothing_asset_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_clothing_asset_import" import job page
    And I launch the import job
    And I wait for the "csv_clothing_asset_import" job to finish
    Then I should see the text "Field \"localized\" must be filled"

  Scenario: Import asset with invalid value for field localized
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following CSV file to import:
    """
    code;categories;localized;description;tags;end_of_use
    code;3quart;Y;New description of my paint.;car,cities;2006-05-12
    """
    And the following job "csv_clothing_asset_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_clothing_asset_import" import job page
    And I launch the import job
    And I wait for the "csv_clothing_asset_import" job to finish
    Then I should see the text "Localized field contains invalid data only \"0\" or \"1\" is accepted"

  Scenario: Import asset with too long value for field description
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following CSV file to import:
    """
    code;categories;localized;description;tags;end_of_use
    code;audio;0;Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer venenatis pulvinar accumsan. Nam in leo ut turpis molestie ultricies. Fusce eget nulla fermentum turpis laoreet feugiat vel dapibus massa. Aenean nisi arcu, pulvinar ac dolor non, porttitor faucibus nulla. Maecenas mattis mauris in nulla tincidunt consectetur. Cras sem nisl, aliquet eu quam quis, euismod iaculis mauris. Fusce luctus sodales sodales. Phasellus non purus quis neque tristique tristique sed sit amet est. Mauris at lacus posuere.;car,cities;2006-05-12
    """
    And the following job "csv_clothing_asset_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_clothing_asset_import" import job page
    And I launch the import job
    And I wait for the "csv_clothing_asset_import" job to finish
    Then I should see the text "description: This value is too long. It should have 500 characters or less"
    And I should see the text "read lines 1"
    And I should see the text "Skipped 1"

  Scenario: Import asset with invalid value for field end_of_use_at
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following CSV file to import:
    """
    code;categories;localized;description;tags;end_of_use
    code;images;0;New description of my paint.;car,cities;2006/05/12
    """
    And the following job "csv_clothing_asset_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_clothing_asset_import" import job page
    And I launch the import job
    And I wait for the "csv_clothing_asset_import" job to finish
    Then I should see the text "Property \"end_of_use\" expects a string with the format \"Y-m-d\TH:i:sO\" as data, \"2006/05/12\" given"
    And I should see the text "read lines 1"
    And I should see the text "Skipped 1"

  Scenario: Import assets with non existent category
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following CSV file to import:
    """
    code;categories;localized;description;tags;end_of_use
    car;wrong;0;Photo of a car.;car,cities;2006-05-12
    landscape;not a category;1;This is a beautiful landscape!;landscape,cities,flowers;
    """
    And the following job "csv_clothing_asset_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_clothing_asset_import" import job page
    And I launch the import job
    And I wait for the "csv_clothing_asset_import" job to finish
    Then I should see the text "read lines 2"
    And I should see the text "created 4"
    And I should see the text "skipped 2"
    And I should see the text "Property \"categories\" expects a valid category code. The category does not exist, \"wrong\" given."
    And I should see the text "Property \"categories\" expects a valid category code. The category does not exist, \"not a category\" given."

  Scenario: Import assets with several categories
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following CSV file to import:
    """
    code;categories;localized;description;tags;end_of_use
    car;images,other,prioritized_images;0;Photo of a car.;car,cities;2006-05-12
    """
    And the following job "csv_clothing_asset_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_clothing_asset_import" import job page
    And I launch the import job
    And I wait for the "csv_clothing_asset_import" job to finish
    Then there should be the following assets:
      | code | description     | categories                      |
      | car  | Photo of a car. | images,other,prioritized_images |
    Then there should be the following tags:
      | code   |
      | car    |
      | cities |
    Then I should see the text "read lines 1"
    And I should see the text "created 2"
    And I should see the text "created 1"

  Scenario: Import assets with several categories including a non existent one
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following CSV file to import:
    """
    code;categories;localized;description;tags;end_of_use
    car;images,nonexistent,prioritized_images;0;Photo of a car.;car,cities;2006-05-12
    """
    And the following job "csv_clothing_asset_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_clothing_asset_import" import job page
    And I launch the import job
    And I wait for the "csv_clothing_asset_import" job to finish
    Then I should see the text "read lines 1"
    And I should see the text "skipped 1"
    And I should see the text "Property \"categories\" expects a valid category code. The category does not exist, \"nonexistent\" given."

  Scenario: Import assets with tags from XLSX
    Given the "clothing" catalog configuration
    And I am logged in as "Peter"
    And the following XLSX file to import:
    """
    code;categories;localized;description;tags;end_of_use
    car;images;0;Photo of a car.;car,cities;2006-05-12
    landscape;other;1;This is a beautiful landscape!;landscape,cities,flowers;
    """
    And the following job "xlsx_clothing_asset_import" configuration:
      | filePath | %file to import% |
    When I am on the "xlsx_clothing_asset_import" import job page
    And I launch the import job
    And I wait for the "xlsx_clothing_asset_import" job to finish
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
    Then I should see the text "read lines 2"
    And I should see the text "created 4"
    And I should see the text "created 2"
