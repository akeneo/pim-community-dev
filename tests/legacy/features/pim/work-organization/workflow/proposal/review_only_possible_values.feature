@javascript
Feature: Review only possible values giving permissions on draft changes
  In order to ease the approve process of proposals
  As a product manager
  I need to be able to approve/refuse all values I can edit on a proposal

  Background:
    Given a "clothing" catalog configuration
    And I am logged in as "Julia"
    And I am on the "hoodies" family page
    And I visit the "Attributes" tab
    And I add available attribute Weather conditions
    And I save the family
    And I logout
    And the following attribute group accesses:
      | attribute group | user group | access |
      | sizes           | Manager    | view   |
      | sizes           | Redactor   | edit   |
    And the following products:
      | sku  | family  | categories | name-en_US               | weather_conditions |
      | sp-1 | hoodies | tops       | South Park Hoodie - Timm | dry                |

  Scenario: I can approve the whole proposal and it accepts only values I can edit
    Given Mary proposed the following change to "sp-1":
      | field              | value                     | locale | scope  | tab                 |
      | Name               | South Park Hoodie - Timmy | en_US  |        | Product information |
      | Description        | Timmy!!!                  | en_US  | mobile | Product information |
      | Weather conditions | Dry, Cold                 |        |        | Product information |
      | Manufacturer       | Volcom                    |        |        | Product information |
      | Size               | M                         |        |        | Sizes               |
    And I am logged in as "Julia"
    And I am on the proposals page
    When I click on the "Approve all" action of the row which contains "South Park Hoodie - Timm"
    And I press the "Send" button in the popin
    Then the product "sp-1" should have the following values:
      | name-en_US               | South Park Hoodie - Timmy |
      | description-en_US-mobile | Timmy!!!                  |
      | weather_conditions       | [cold], [dry]             |
      | manufacturer             | [Volcom]                  |
      | size                     |                           |
    And the row "South Park Hoodie - Timmy" should contain:
      | column | value             |
      | Status | Can't be reviewed |

  Scenario: I can refuse the whole proposal and it refuses only values I can edit
    Given Mary proposed the following change to "sp-1":
      | field              | value                     | locale | scope  | tab                 |
      | Name               | South Park Hoodie - Timmy | en_US  |        | Product information |
      | Description        | Timmy!!!                  | en_US  | mobile | Product information |
      | Weather conditions | Dry, Cold                 |        |        | Product information |
      | Manufacturer       | Volcom                    |        |        | Product information |
      | Size               | M                         |        |        | Sizes               |
    And I am logged in as "Julia"
    And I am on the proposals page
    When I click on the "Reject all" action of the row which contains "South Park Hoodie - Timm"
    And I press the "Send" button in the popin
    Then the product "sp-1" should have the following values:
      | name-en_US               | South Park Hoodie - Timm |
      | description-en_US-mobile |                          |
      | weather_conditions       | [dry]                    |
      | manufacturer             |                          |
      | size                     |                          |
    And the row "South Park Hoodie - Timm" should contain:
      | column | value             |
      | Status | Can't be reviewed |
