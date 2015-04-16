Feature: Update a single product by applying rules
  In order ease the enrichment of the catalog
  As a regular user
  I need that the relevant rules are executed and correctly applied to the product

  Background:
    Given a "footwear" catalog configuration
    And I add the "french" locale to the "mobile" channel
    And I add the "french" locale to the "tablet" channel
    And the following reference data:
      | type   | code     |
      | color  | yellow   |
      | color  | blue     |
      | color  | red      |
      | color  | orange   |
      | fabric | chiffon  |
      | fabric | satin    |
      | fabric | wool     |
      | fabric | kevlar   |
      | fabric | leather  |
      | fabric | gore-tex |
      | fabric | toile    |
      | fabric | cashmere |
    And I am logged in as "Julia"

  Scenario: Successfully execute a rule with setter actions to update non empty values on reference data attributes
    Given the following products:
      | sku       | family |
      | red-heels | heels  |
    And the following product values:
      | product   | attribute   | value          | locale | scope  |
      | red-heels | sole_color  | yellow         |        |        |
      | red-heels | sole_fabric | chiffon, satin |        |        |
      | red-heels | cap_color   | blue           | en_US  | mobile |
      | red-heels | lace_fabric | wool, kevlar   | fr_FR  | tablet |
    And the following product rules:
      | code           | priority |
      | rule_set_heels | 10       |
    And the following product rule conditions:
      | rule           | field | operator | value     |
      | rule_set_heels | sku   | =        | red-heels |
    And the following product rule setter actions:
      | rule           | field       | locale | scope  | value                     |
      | rule_set_heels | sole_color  |        |        | orange                    |
      | rule_set_heels | sole_fabric |        |        | chiffon, satin, leather   |
      | rule_set_heels | cap_color   | en_US  | mobile | red                       |
      | rule_set_heels | lace_fabric | fr_FR  | tablet | cashmere                  |
      | rule_set_heels | lace_fabric | en_US  | tablet | toile, cashmere           |
      | rule_set_heels | lace_fabric | en_US  | mobile | gore-tex, toile, cashmere |
    Given the product rule "rule_set_heels " is executed
    Then the product "red-heels" should have the following values:
      | sole_color               | [orange]                        |
      | sole_fabric              | [chiffon], [satin], [leather]   |
      | cap_color-en_US-mobile   | [red]                           |
      | lace_fabric-fr_FR-tablet | [cashmere]                      |
      | lace_fabric-en_US-tablet | [toile], [cashmere]             |
      | lace_fabric-en_US-mobile | [gore-tex], [toile], [cashmere] |

  Scenario: Successfully execute a rule with copier actions to update non empty values on reference data attributes
    Given the following products:
      | sku       | family |
      | red-heels | heels  |
    And the following product values:
      | product   | attribute   | value          | locale | scope  |
      | red-heels | sole_color  | yellow         |        |        |
      | red-heels | sole_fabric | chiffon, satin |        |        |
      | red-heels | cap_color   | blue           | en_US  | mobile |
      | red-heels | lace_fabric | wool, kevlar   | fr_FR  | tablet |
    And the following product rules:
      | code            | priority |
      | rule_copy_heels | 10       |
    And the following product rule conditions:
      | rule            | field | operator | value     |
      | rule_copy_heels | sku   | =        | red-heels |
    And the following product rule copier actions:
      | rule            | from_field  | to_field    | from_locale | to_locale | from_scope | to_scope |
      | rule_copy_heels | sole_color  | cap_color   |             | en_US     |            | mobile   |
      | rule_copy_heels | sole_color  | cap_color   |             | fr_FR     |            | tablet   |
      | rule_copy_heels | lace_fabric | sole_fabric | fr_FR       |           | tablet     |          |
    Given the product rule "rule_copy_heels " is executed
    Then the product "red-heels" should have the following values:
      | sole_color               | [yellow]         |
      | sole_fabric              | [wool], [kevlar] |
      | cap_color-en_US-mobile   | [yellow]         |
      | cap_color-fr_FR-tablet   | [yellow]         |
      | lace_fabric-fr_FR-tablet | [wool], [kevlar] |
