@acceptance-back
Feature: Create an attribute and add it to the family
  In order to ease the attributes mapping in Franklin Insights
  As a system administrator
  I want to quickly create an attribute and add it to a family to facilitate its mapping

  @critical
  Scenario: Successfully create an attribute and add it to the family
    Given the family "router"
    And the attribute group "franklin"
    When I create the attribute text "Product color" in the family "router"
    Then the family "router" contains the attribute text "Product_color"
