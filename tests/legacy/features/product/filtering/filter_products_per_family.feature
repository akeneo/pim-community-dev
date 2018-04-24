@javascript
Feature: Filter products per family
  In order to enrich my catalog
  As a regular user
  I need to be able to manually filter products per family

  Background:
    Given the "default" catalog configuration
    And the following families:
      | code             |
      | computers        |
      | hi_fi            |
      | washing_machines |
    And the following products:
      | sku        | family           |
      | PC         | computers        |
      | Laptop     | computers        |
      | Amplifier  | hi_fi            |
      | CD changer | hi_fi            |
      | Whirlpool  | washing_machines |
      | Electrolux | washing_machines |
      | Mug        |                  |
    And I am logged in as "Mary"

  Scenario: Successfully filter products by a single family
    Given I am on the products grid
    And the grid should contain 7 elements
    Then I should see the filter family
    And I should be able to use the following filters:
      | filter | operator     | value            | result                                                      |
      | family | in list      | computers        | PC and Laptop                                               |
      | family | in list      | hi_fi, computers | Amplifier, CD changer, PC and Laptop                        |
      | family | in list      | washing_machines | Whirlpool and Electrolux                                    |
      | family | is empty     |                  | Mug                                                         |
      | family | is not empty |                  | PC, Amplifier, CD changer, Whirlpool, Electrolux and Laptop |

  Scenario: Successfully filter 20 first families on search input
    Given the following families:
      | code   | label-fr_FR | label-en_US | label-de_DE |
      | code1  | code1fr     | code1en     | code1de     |
      | code2  | code2fr     | code2en     | code2de     |
      | code3  | code3fr     | code3en     | code3de     |
      | code4  | code4fr     | code4en     | code4de     |
      | code5  | code5fr     | code5en     | code5de     |
      | code6  | code6fr     | code6en     | code6de     |
      | code7  | code7fr     | code7en     | code7de     |
      | code8  | code8fr     | code8en     | code8de     |
      | code9  | code9fr     | code9en     | code9de     |
      | code10 | code10fr    | code10e     | code10de    |
      | code11 | code11fr    | code11en    | code11de    |
      | code12 | code12fr    | code12en    | code12de    |
      | code13 | code13fr    | code13en    | code13de    |
      | code14 | code14fr    | code14en    | code14de    |
      | code15 | code15fr    | code15en    | code15de    |
      | code16 | code16fr    | code16en    | code16de    |
      | code17 | code17fr    | code17en    | code17de    |
      | code18 | code18fr    | code18en    | code18de    |
      | code19 | code19fr    | code19en    | code19de    |
      | code20 | code20fr    | code20en    | code20de    |
      | code21 | code21fr    | code21en    | code21de    |
    And I am on the products grid
    And I should see the filter family
    When I open the "family" filter
    Then I should see 20 items in the autocomplete
