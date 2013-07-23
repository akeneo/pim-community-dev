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
     
    Given the following currencies:
      | code | activated |
      | EUR  | yes       |
      | USD  | yes       |

    And I am logged in as "admin"

  Scenario: Successfully create a new locale
    Given I am on the locales page
    When I create a new locale
    Then I should be on the locale creation page
    Then I should see the Locale, Inherited locale and Default currency fields
    And I fill in the following fields:
      | Locale           | Spanish (Spain)  |
      | Default currency | EUR              |
    And I press the "Save" button
    Then I should see "Locale successfully saved"
    Then I should be on the locales page
    Then I should see activated locales de_DE, en_US, fr_FR and sp_SP
