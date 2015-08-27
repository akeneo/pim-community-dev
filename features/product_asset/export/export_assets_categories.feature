Feature: Export assets categories
  In order to be able to access and modify asset category data outside PIM
  As a product manager
  I need to be able to import and export assets categories

  @javascript
  Scenario: Successfully export assets categories
    Given a "clothing" catalog configuration
    And the following job "clothing_asset_category_export" configuration:
      | filePath | %tmp%/asset_category_export/asset_category_export.csv |
    And I am logged in as "Julia"
    And I am on the "clothing_asset_category_export" export job page
    When I launch the export job
    And I wait for the "clothing_asset_category_export" job to finish
    And I should see "read 14"
    And I should see "written 14"
    Then file "%tmp%/asset_category_export/asset_category_export.csv" should contain 15 rows
    Then exported file of "clothing_asset_category_export" should contain:
    """
    code;parent;label-de_DE;label-en_US;label-fr_FR
    asset_main_catalog;;;"Asset main catalog";
    images;asset_main_catalog;;Images;
    other;images;;"Other picture";
    situ;images;;"In situ pictures";
    prioritized_images;images;;"Prioritised images";
    print;asset_main_catalog;;Print;
    videos;asset_main_catalog;;Videos;
    prioritized_videos;videos;;"Prioritised videos";
    audio;asset_main_catalog;;Audio;
    client_documents;asset_main_catalog;;"Client documents";
    store_documents;asset_main_catalog;;"Store documents";
    technical_documents;asset_main_catalog;;"Technical documents";
    sales_documents;asset_main_catalog;;"Sales documents";
    archives;asset_main_catalog;;Archives;
    """
