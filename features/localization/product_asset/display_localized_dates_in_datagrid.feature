@javascript
Feature: Localize dates in asset picker
  In order to have localized UI
  As a product manager
  I need to be able to show localized dates in the asset picker

  Background:
    Given the "clothing" catalog configuration
    And the following products:
      | sku   |
      | shirt |
    And the following asset:
      | code   | end of use at |
      | mascot | 2017-12-25    |

  Scenario: Successfully show english format datetime
    Given I am logged in as "Julia"
    And I am on the "shirt" product page
    When I add available attributes Front view
    And I start to manage assets for "Front view"
    And I change the page size to 25
    Then the row "mascot" should contain:
      | column     | value      |
      | END OF USE | 12/25/2017 |

  Scenario: Successfully show french format datetime
    Given I am logged in as "Julien"
    And I am on the "shirt" product page
    When I add available attributes front_view
    And I start to manage assets for "[front_view]"
    And I change the page size to 25
    Then the row "mascot" should contain:
      | column               | value      |
      | FIN DE L'UTILISATION | 25/12/2017 |
