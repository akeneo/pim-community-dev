@javascript
Feature: Import association types
  In order to reuse the association types of my products
  As Julia
  I need to be able to import association types

  Scenario: Successfully import association types
    Given the "footwear" catalog configuration
    And I am logged in as "Julia"
    And the following file to import:
    """
    code;label-en_US;label-fr_FR
    default;;
    X_SELL_footwear;Cross Sell footwear;Vente croisée footwear
    UPSELL_footwear;Upsell footwear;Vente incitative footwear
    """
    And the following job "footwear_association_type_import" configuration:
      | filePath | %file to import% |
    When I am on the "footwear_association_type_import" import job page
    And I launch the import job
    And I wait for the "footwear_association_type_import" job to finish
    Then there should be the following association types:
      | code            | label-en_US         | label-fr_FR               |
      | default         |                     |                           |
      | X_SELL_footwear | Cross Sell footwear | Vente croisée footwear    |
      | UPSELL_footwear | Upsell footwear     | Vente incitative footwear |
