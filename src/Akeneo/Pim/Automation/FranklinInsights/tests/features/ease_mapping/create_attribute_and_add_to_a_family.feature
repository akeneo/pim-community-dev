@acceptance-back
Feature: Create an attribute and add it to the family
  In order to ease the attributes mapping in Franklin Insights
  As a system administrator
  I want to quickly create an attribute and add it to a family to facilitate its mapping

  @critical
  Scenario: Successfully create a text attribute and add it to a family
    Given the family "router"
    And the attribute group "franklin"
    When I create the text attribute "Product name" in the family "router"
    Then the family "router" should have the text attribute "Product_name"

  Scenario: Succesfully create a numeric attribute and add it to a family
    Given the family "router"
    And the attribute group "franklin"
    When I create the numeric attribute "ports count" in the family "router"
    Then the family "router" should have the numeric attribute "ports_count"

  Scenario: Succesfully create a simpleselect attribute and add it to a family
    Given the family "router"
    And the attribute group "franklin"
    When I create the simpleselect attribute "ports count" in the family "router"
    Then the family "router" should have the simpleselect attribute "ports_count"

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
    Then a not supported metric type message should be sent

  Scenario: Successfully create an attribute in an unexisting group
    Given the family "router"
    When I create the text attribute "Product name" in the family "router"
    Then the family "router" should have the text attribute "Product_name"
    And the attribute "Product_name" should belongs to the "Franklin" attribute group
