@javascript
Feature: Delete only possible values
  In order to ease the cleaning of proposals
  As a product manager
  I need to be able to delete all values I can edit on a product draft

  Background:
    Given a "clothing" catalog configuration
    And the following attribute group accesses:
      | attribute group | user group | access | group | type             |
      | sizes           | Manager    | view   | other | pim_catalog_text |
      | sizes           | Redactor   | edit   | other | pim_catalog_text |
    And the following products:
      | sku  | family  | categories | name-en_US               | weather_conditions |
      | sp-1 | hoodies | tops       | South Park Hoodie - Timm | dry                |

  Scenario: I can partially remove a product draft if I have no edit access on all values
    Given Mary started to propose the following change to "sp-1":
      | field              | value                     | locale | scope  | tab                 |
      | Name               | South Park Hoodie - Timmy | en_US  |        | Product information |
      | Description        | Timmy!!!                  | en_US  | mobile | Product information |
      | Weather conditions | Dry, Cold                 |        |        | Product information |
      | Manufacturer       | Volcom                    |        |        | Product information |
      | Size               | M                         |        |        | Sizes               |
    And I am logged in as "Julia"
    And I edit the "sp-1" product
    And I visit the "Proposals" tab
    And I click on the "remove" action of the row which contains "Mary"
    And I press the "Send" button in the popin
    Then the row "Mary" should contain:
      | column | value            |
      | Status | Can't be deleted |
    When I logout
    And I am logged in as "Mary"
    And I edit the "sp-1" product
    And I switch the locale to "en_US"
    And I switch the scope to "mobile"
    Then the "Description" field should contain ""
    And the "Name" field should contain "South Park Hoodie - Timm"
    When I visit the "Sizes" group
    Then the field Size should contain "M"
