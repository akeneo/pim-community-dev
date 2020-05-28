Feature: Import association types
  In order to reuse the association types of my products
  As a product manager
  I need to be able to import association types

  Scenario: Successfully import association types in CSV
    Given the "footwear" catalog configuration
    And the following CSV file to import:
      """
      code;is_two_way;label-en_US;label-fr_FR
      default;0;;
      X_SELL_footwear;1;Cross Sell footwear;Vente croisée footwear
      UPSELL_footwear;0;Upsell footwear;Vente incitative footwear
      """
    When the associations types are imported via the job csv_footwear_association_type_import
    Then there should be the following association types:
      | code            | label-en_US         | label-fr_FR               | is_two_way       |
      | default         |                     |                           | false            |
      | X_SELL_footwear | Cross Sell footwear | Vente croisée footwear    | true             |
      | UPSELL_footwear | Upsell footwear     | Vente incitative footwear | false            |
