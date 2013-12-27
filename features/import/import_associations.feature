@javascript
Feature: Import associations
  In order to reuse the associations of my products
  As Julia
  I need to be able to import associations

  Scenario: Successfully import associations
    Given the "default" catalog configuration
    And the following jobs:
      | connector            | alias                  | code                    | label                       | type   |
      | Akeneo CSV Connector | csv_association_import | acme_association_import | Association import for Acme | import |
    And I am logged in as "Julia"
    And the following file to import:
    """
    code;label-en_US;label-fr_FR
    default;;
    X_SELL;Cross Sell;Vente croisée
    UPSELL;Upsell;Vente incitative
    """
    And the following job "acme_association_import" configuration:
      | filePath | {{ file to import }} |
    When I am on the "acme_association_import" import job page
    And I launch the import job
    And I wait for the job to finish
    Then there should be the following associations:
      | code    | label-en_US | label-fr_FR      |
      | default |             |                  |
      | X_SELL  | Cross Sell  | Vente croisée    |
      | UPSELL  | Upsell      | Vente incitative |
