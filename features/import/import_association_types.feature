@javascript
Feature: Import association types
  In order to reuse the association types of my products
  As a product manager
  I need to be able to import association types

  Scenario: Successfully import association types in CSV
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      code;label-en_US;label-fr_FR
      default;;
      X_SELL_footwear;Cross Sell footwear;Vente croisée footwear
      UPSELL_footwear;Upsell footwear;Vente incitative footwear
      """
    And the following job "csv_footwear_association_type_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_association_type_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_association_type_import" job to finish
    Then there should be the following association types:
      | code            | label-en_US         | label-fr_FR               |
      | default         |                     |                           |
      | X_SELL_footwear | Cross Sell footwear | Vente croisée footwear    |
      | UPSELL_footwear | Upsell footwear     | Vente incitative footwear |

  @jira https://akeneo.atlassian.net/browse/PIM-3311
  Scenario: Skip association types with empty code
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following CSV file to import:
      """
      code;label-en_US;label-fr_FR
      ;label US; label FR
      X_SELL_footwear;Cross Sell footwear;Vente croisée footwear
      """
    And the following job "csv_footwear_association_type_import" configuration:
      | filePath | %file to import% |
    When I am on the "csv_footwear_association_type_import" import job page
    And I launch the import job
    And I wait for the "csv_footwear_association_type_import" job to finish
    And I should see the text "Field \"code\" must be filled"

  Scenario: Successfully import association types in XLSX
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following XLSX file to import:
      """
      code;label-en_US;label-fr_FR
      default;;
      X_SELL_footwear;Cross Sell footwear;Vente croisée footwear
      UPSELL_footwear;Upsell footwear;Vente incitative footwear
      """
    And the following job "xlsx_footwear_association_type_import" configuration:
      | filePath | %file to import% |
    When I am on the "xlsx_footwear_association_type_import" import job page
    And I launch the import job
    And I wait for the "xlsx_footwear_association_type_import" job to finish
    Then there should be the following association types:
      | code            | label-en_US         | label-fr_FR               |
      | default         |                     |                           |
      | X_SELL_footwear | Cross Sell footwear | Vente croisée footwear    |
      | UPSELL_footwear | Upsell footwear     | Vente incitative footwear |
