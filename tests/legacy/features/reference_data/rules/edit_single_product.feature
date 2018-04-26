@javascript
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
    Given the family "heels" has the attributes "cap_color, lace_fabric"
    And the following products:
      | sku       | family |
      | red-heels | heels  |
    And the following product values:
      | product   | attribute   | value          | locale | scope  |
      | red-heels | sole_color  | yellow         |        |        |
      | red-heels | sole_fabric | chiffon, satin |        |        |
      | red-heels | cap_color   | blue           | en_US  | mobile |
      | red-heels | lace_fabric | wool, kevlar   | fr_FR  | tablet |
    And the following product rule definitions:
      """
      rule_set_heels:
        priority: 10
        conditions:
          - field:    sku
            operator: =
            value:    red-heels
        actions:
          - type:   set
            field:  sole_color
            value:  orange
          - type:   set
            field:  sole_fabric
            value:
              - chiffon
              - satin
              - leather
          - type:   set
            field:  cap_color
            value:  red
            locale: en_US
            scope:  mobile
          - type:   set
            field:  lace_fabric
            value:
              - cashmere
            locale: fr_FR
            scope:  tablet
          - type:   set
            field:  lace_fabric
            value:
              - toile
              - cashmere
            locale: en_US
            scope:  tablet
          - type:   set
            field:  lace_fabric
            value:
              - gore-tex
              - toile
              - cashmere
            locale: en_US
            scope:  mobile
      """
    Given the product rule "rule_set_heels " is executed
    Then the product "red-heels" should have the following values:
      | sole_color               | [orange]                        |
      | sole_fabric              | [chiffon], [leather], [satin]   |
      | cap_color-en_US-mobile   | [red]                           |
      | lace_fabric-fr_FR-tablet | [cashmere]                      |
      | lace_fabric-en_US-tablet | [cashmere], [toile]             |
      | lace_fabric-en_US-mobile | [cashmere], [gore-tex], [toile] |

  Scenario: Successfully execute a rule with copier actions to update non empty values on reference data attributes
    Given the family "heels" has the attributes "cap_color"
    And the following products:
      | sku       | family |
      | red-heels | heels  |
    And the following product values:
      | product   | attribute   | value          | locale | scope  |
      | red-heels | sole_color  | yellow         |        |        |
      | red-heels | sole_fabric | chiffon, satin |        |        |
      | red-heels | cap_color   | blue           | en_US  | mobile |
      | red-heels | lace_fabric | wool, kevlar   | fr_FR  | tablet |
    And the following product rule definitions:
      """
      rule_copy_heels:
        priority: 10
        conditions:
          - field:    sku
            operator: =
            value:    red-heels
        actions:
          - type:       copy
            from_field: sole_color
            to_field:   cap_color
            to_scope:   mobile
            to_locale:  en_US
          - type:       copy
            from_field: sole_color
            to_field:   cap_color
            to_scope:   tablet
            to_locale:  fr_FR
          - type:       copy
            from_field: lace_fabric
            to_field:   sole_fabric
            from_scope:   tablet
            from_locale:  fr_FR
      """
    Given the product rule "rule_copy_heels " is executed
    Then the product "red-heels" should have the following values:
      | sole_color               | [yellow]         |
      | sole_fabric              | [kevlar], [wool] |
      | cap_color-en_US-mobile   | [yellow]         |
      | cap_color-fr_FR-tablet   | [yellow]         |
      | lace_fabric-fr_FR-tablet | [kevlar], [wool] |
