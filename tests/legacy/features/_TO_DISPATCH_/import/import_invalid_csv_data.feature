@javascript
Feature: Handle import of invalid CSV data
  In order to ease the correction of an invalid CSV file import
  As a product manager
  I need to be able to download a CSV file containing all invalid data of an import

  Background:
    Given the "clothing" catalog configuration

  Scenario: From an asset category CSV import, create an invalid data file and be able to download it
    Given the following CSV file to import:
      """
      code;parent;label-en_US
      asset_main_catalog;;Asset main catalog
      images;asset_main_catalog;Images
      invalid code;images;Invalid data
      situ;images;In situ pictures
      prioritized_images;NO_PARENT;Prioritised images
      """
    And the following job "csv_clothing_asset_category_import" configuration:
      | filePath | %file to import% |
    And I am logged in as "Julia"
    And I am on the "csv_clothing_asset_category_import" import job page
    And I launch the "csv_clothing_asset_category_import" import job
    And I wait for the "csv_clothing_asset_category_import" job to finish
    Then I should see "Download invalid data" on the "Download generated files" dropdown button
    And the invalid data file of "csv_clothing_asset_category_import" should contain:
      """
      code;parent;label-en_US
      invalid code;images;Invalid data
      prioritized_images;NO_PARENT;Prioritised images
      """

  Scenario: From an asset CSV import, create an invalid data file and be able to download it
    Given the following CSV file to import:
      """
      code;description;localized;enabled;end_of_use;tags;categories
      paint;Photo of a paint.;0;1;2006-05-12;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage;images,situ
      invalid code;This is chicago!;1;1;;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage;images
      akene;Because Akeneo;0;1;2015-08-01;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage;images
      autumn;Leaves and water;0;1;2015-12-01;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage;other,images
      bridge;Architectural bridge of a city, above water;NOPE;1;;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage;other,images
      dog;Obviously not a cat, but still an animal;0;1;2006-05-12;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage;other
      """
    And the following job "csv_clothing_asset_import" configuration:
      | filePath | %file to import% |
    And I am logged in as "Julia"
    And I am on the "csv_clothing_asset_import" import job page
    And I launch the "csv_clothing_asset_import" import job
    And I wait for the "csv_clothing_asset_import" job to finish
    Then I should see "Download invalid data" on the "Download generated files" dropdown button
    And the invalid data file of "csv_clothing_asset_import" should contain:
      """
      code;description;localized;enabled;end_of_use;tags;categories
      invalid code;This is chicago!;1;1;;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage;images
      bridge;Architectural bridge of a city, above water;NOPE;1;;backless,big_sizes,dress_suit,flower,neckline,pattern,pea,solid_color,stripes,vintage;other,images
      """

  Scenario: From a product proposal CSV import, create an invalid data file and be able to download it
    Given the following products:
      | sku       |
      | my-jacket |
    And the following CSV file to import:
      """
      sku;enabled;description-en_US-mobile
      my-jacket;1;My desc
      my-jacket 2;0;My desc
      """
    And the following job "csv_clothing_product_proposal_import" configuration:
      | filePath | %file to import% |
    And I am logged in as "Julia"
    And I am on the "csv_clothing_product_proposal_import" import job page
    And I launch the "csv_clothing_product_proposal_import" import job
    And I wait for the "csv_clothing_product_proposal_import" job to finish
    Then I should see "Download invalid data" on the "Download generated files" dropdown button
    And the invalid data file of "csv_clothing_product_proposal_import" should contain:
      """
      sku;enabled;description-en_US-mobile
      my-jacket 2;0;My desc
      """
