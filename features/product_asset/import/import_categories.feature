@javascript
Feature: Import categories
  In order to reuse the categories of my assets
  As an asset manager
  I need to be able to import categories

  Scenario: Successfully import new assets categories
    Given the "clothing" catalog configuration
    And I am logged in as "Pamela"
    And the following CSV file to import:
      """
      code;parent;label-de_DE;label-en_US;label-fr_FR
      asset_main_catalog;;;"Asset main catalog";"Catalogue principal d'Assets"
      images;asset_main_catalog;;images;images
      others;images;;"Other picture";"Autre images"
      back;images;;"Back picture";"image de dos"
      """
    And the following job "clothing_asset_category_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_asset_category_import" import job page
    And I launch the import job
    And I wait for the "clothing_asset_category_import" job to finish
    And I should see "read lines 4"
    And I should see "processed 3"
    And I should see "created 1"
    Then there should be the following assets categories:
      | code               | label-de_DE | label-en_US        | label-fr_FR                  | parent             |
      | asset_main_catalog |             | Asset main catalog | Catalogue principal d'Assets |                    |
      | images             |             | images             | images                       | asset_main_catalog |
      | others             |             | Other picture      | Autre images                 | images             |
      | back               |             | Back picture       | image de dos                 | images             |

  Scenario: Import assets categories with missing parent
    Given the "clothing" catalog configuration
    And I am logged in as "Pamela"
    And the following CSV file to import:
      """
      code;parent;label-de_DE;label-en_US;label-fr_FR
      asset_main_catalog;clothes;;"Asset main catalog";"Catalogue principal d'Assets"
      """
    And the following job "clothing_asset_category_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_asset_category_import" import job page
    And I launch the import job
    And I wait for the "clothing_asset_category_import" job to finish
    Then I should see "The parent category \"clothes\" does not exist"

  Scenario: Skip assets categories with empty code
    Given the "clothing" catalog configuration
    And I am logged in as "Pamela"
    And the following CSV file to import:
      """
      code;parent;label-en_US
      ;;label US
      """
    And the following job "clothing_asset_category_import" configuration:
      | filePath | %file to import% |
    When I am on the "clothing_asset_category_import" import job page
    And I launch the import job
    And I wait for the "clothing_asset_category_import" job to finish
    And I should see "Field \"code\" must be filled"
