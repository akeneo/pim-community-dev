@javascript
Feature: Export asset variations
  In order to be able to access and modify asset data outside PIM
  As an asset manager
  I need to be able to import and export asset variations

  Scenario: Successfully export asset variation
    Given a "clothing" catalog configuration
    And the following job "clothing_asset_variation_export" configuration:
      | filePath | %tmp%/asset_export/asset_variation_export.csv |
    And I am logged in as "Pamela"
    And I am on the "clothing_asset_variation_export" export job page
    When I launch the export job
    And I wait for the "clothing_asset_variation_export" job to finish
    And I should see "read 34"
    And I should see "written 34"
    Then file "%tmp%/asset_export/asset_variation_export.csv" should contain 35 rows
    Then exported file of "clothing_asset_variation_export" should contain:
    """
    asset;locale;channel;reference_file;variation_file
    paint;;tablet;;
    paint;;mobile;;
    chicagoskyline;de_DE;tablet;;
    chicagoskyline;de_DE;mobile;;
    chicagoskyline;en_US;tablet;;
    chicagoskyline;en_US;mobile;;
    chicagoskyline;fr_FR;tablet;;
    chicagoskyline;fr_FR;mobile;;
    akene;;tablet;;
    akene;;mobile;;
    autumn;;tablet;;
    autumn;;mobile;;
    bridge;;tablet;;
    bridge;;mobile;;
    dog;;tablet;;
    dog;;mobile;;
    eagle;;tablet;;
    eagle;;mobile;;
    machine;;tablet;;
    machine;;mobile;;
    man_wall;;tablet;;
    man_wall;;mobile;;
    minivan;;tablet;;
    minivan;;mobile;;
    mouette;;tablet;;
    mouette;;mobile;;
    mountain;;tablet;;
    mountain;;mobile;;
    mugs;;tablet;;
    mugs;;mobile;;
    photo;;tablet;;
    photo;;mobile;;
    tiger;;tablet;;
    tiger;;mobile;;
    """
