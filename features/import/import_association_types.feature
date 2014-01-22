@javascript
Feature: Import association types
  In order to reuse the association types of my products
  As Julia
  I need to be able to import association types

  Scenario: Successfully import association types
    Given the "default" catalog configuration
    And the following jobs:
      | connector            | alias                       | code                         | label                            | type   |
      | Akeneo CSV Connector | csv_association_type_import | acme_association_type_import | Association type import for Acme | import |
    And I am logged in as "Julia"
    And the following file to import:
    """
    code;label-en_US;label-fr_FR
    default;;
    X_SELL;Cross Sell;Vente croisée
    UPSELL;Upsell;Vente incitative
    """
    And the following job "acme_association_type_import" configuration:
      | filePath | %file to import% |
    When I am on the "acme_association_type_import" import job page
    And I launch the import job
    And I wait for the job to finish
    Then there should be the following association types:
      | code    | label-en_US | label-fr_FR      |
      | default |             |                  |
      | X_SELL  | Cross Sell  | Vente croisée    |
      | UPSELL  | Upsell      | Vente incitative |
