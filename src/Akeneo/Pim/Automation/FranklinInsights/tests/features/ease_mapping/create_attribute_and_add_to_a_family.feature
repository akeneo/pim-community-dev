@acceptance-back
Feature: Create an attribute and add it to the family
  In order to ease the attributes mapping in Franklin Insights
  As a system administrator
  I want to quickly create an attribute and add it to a family to facilitate its mapping

  @end-to-end @javascript
  Scenario: Successfully create a text attribute and add it to a family
    Given Franklin is configured with a valid token
    And the product "B00EYZY6AC" of the family "router"
    And the product "B00EYZY6AC" is subscribed to Franklin
    When I create the text attribute "Warranty" in the family "router"
    Then the family "router" should have the text attribute "Warranty"

  Scenario: Succesfully create a numeric attribute and add it to a family
    Given the family "router"
    And the attribute group "franklin"
    When I create the number attribute "ports count" in the family "router"
    Then the family "router" should have the number attribute "ports_count"

  Scenario: Succesfully create a simpleselect attribute and add it to a family
    Given the family "router"
    And the attribute group "franklin"
    When I create the select attribute "ports count" in the family "router"
    Then the family "router" should have the select attribute "ports_count"

  Scenario: Succesfully create a multiselect attribute and add it to a family
    Given the family "router"
    And the attribute group "franklin"
    When I create the multiselect attribute "ports count" in the family "router"
    Then the family "router" should have the multiselect attribute "ports_count"

  Scenario: Succesfully create a boolean attribute and add it to a family
    Given the family "router"
    And the attribute group "franklin"
    When I create the boolean attribute "ports count" in the family "router"
    Then the family "router" should have the boolean attribute "ports_count"

  Scenario: Fail to create a metric attribute and add it to a family
    Given the family "router"
    And the attribute group "franklin"
    When I create the metric attribute "ports count" in the family "router"
    Then the attribute "ports_count" should not be created
    And a not supported metric type message should be sent

  Scenario: Successfully create an attribute in an unexisting group
    Given the family "router"
    When I create the text attribute "Product name" in the family "router"
    Then the family "router" should have the text attribute "Product_name"
    And the attribute "Product_name" should belongs to the "Franklin" attribute group

  Scenario: Fail to create an attribute that already exist
    Given the family "router"
    And the predefined attributes connectivity
    When I create the text attribute "connectivity" in the family "router"
    Then the family "router" should have the text attribute "connectivity"
