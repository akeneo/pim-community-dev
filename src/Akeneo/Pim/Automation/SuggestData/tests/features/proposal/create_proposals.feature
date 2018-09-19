@acceptance-back
Feature: Create proposals
  In order to automatically enrich my products
  As the System
  I want to create proposals from PIM.ai suggested data

  Scenario: Successfully create a proposal from valid suggested data
    Given the product "B00EYZY6AC" of the family "router"
    And there is suggested data for subscribed product "B00EYZY6AC"
    And the product "B00EYZY6AC" has category "hitech"
    When the system creates proposals for suggested data
    Then there should be a proposal for product "B00EYZY6AC"

  Scenario: Do not create a proposal if the product is not categorized
    Given the product "B00EYZY6AC" of the family "router"
    And there is suggested data for subscribed product "B00EYZY6AC"
    And the product "606449099812" of the family "router"
    And there is suggested data for subscribed product "606449099812"
    And the product "606449099812" has category "hitech"
    When the system creates proposals for suggested data
    Then there should not be a proposal for product "B00EYZY6AC"
    But there should be a proposal for product "606449099812"

  Scenario: Do not create a proposal if suggested data is empty
    Given the product "B00EYZY6AC" of the family "router"
    And there is suggested data for subscribed product "B00EYZY6AC"
    And the product "B00EYZY6AC" has category "hitech"
    And the product "606449099812" of the family "router"
    And the product "606449099812" has category "hitech"
    When the system creates proposals for suggested data
    Then there should be a proposal for product "B00EYZY6AC"
    But there should not be a proposal for product "606449099812"
