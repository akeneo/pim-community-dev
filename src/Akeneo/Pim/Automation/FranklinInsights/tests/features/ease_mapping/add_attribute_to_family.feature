@acceptance-back
Feature: Add an existing attribute to the family
  In order to ease the attributes mapping in Franklin Insights
  As a system administrator
  I want to quickly add an existing attribute to a family to facilitate its mapping

  Scenario: Successfully add existing attribute to a family
    Given Franklin is configured with a valid token
    And the product "B00EYZY6AC" of the family "router"
    And the product "B00EYZY6AC" is subscribed to Franklin
    And the predefined attributes ean
    And the attribute "ean" is not in family "router"
    When I add the attribute "ean" to the family "router"
    Then the family "router" should have the text attribute "ean"

  Scenario: Successfully bulk add existing attribute to a family
    Given the family "router"
    And the predefined attributes ean
    And the predefined attributes identifier
    And the attribute "ean" is not in family "router"
    And the attribute "identifier" is not in family "router"
    And Franklin is configured with a valid token
    When I bulk add the attributes ean and identifier to the family router
    Then the family "router" should have the text attribute "ean"
    And the family "router" should have the text attribute "identifier"
