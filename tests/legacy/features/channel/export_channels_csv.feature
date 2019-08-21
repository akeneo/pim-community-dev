@javascript
Feature: Export channels
  In order to be able to access and modify channels data outside PIM
  As an administrator
  I need to be able to export channels

  Scenario: Successfully export channels
    Given a "footwear" catalog configuration
    And the following job "csv_footwear_channel_export" configuration:
      | filePath | %tmp%/channel_export/channel_export.csv |
    And I am logged in as "Julia"
    And I am on the "csv_footwear_channel_export" export job page
    When I launch the export job
    And I wait for the "csv_footwear_channel_export" job to finish
    Then I should see the text "Read 2"
    And I should see the text "Written 2"
    And exported file of "csv_footwear_channel_export" should contain:
    """
    code;label-fr_FR;label-en_US;conversion_units;currencies;locales;tree
    mobile;Mobile;Mobile;;EUR;en_US,fr_FR;2014_collection
    tablet;Tablette;Tablet;;USD,EUR;en_US;2014_collection
    """
