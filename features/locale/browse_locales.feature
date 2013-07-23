@javascript
Feature: Browse locales
  In order to check wether or not a locale is available is the catalog
  As an user
  I need to be able to see active and inactive locales in the catalog
  
  Background:
    Given the following locales:
      | code  | fallback | activated |
      | de_DE | en_US    | yes       |
      | en_US |          | yes       |
      | fr_FR | en_US    | yes       |

    And I am logged in as "admin"

  Scenario: Successfully filter locales
    Given I am on the locales page
    And I filter my locales per Code "e"
    And I filter my locales per Fallback "en_US"
    Then I should see activated locales de_DE
    And I should not see locales en_US and fr_FR
