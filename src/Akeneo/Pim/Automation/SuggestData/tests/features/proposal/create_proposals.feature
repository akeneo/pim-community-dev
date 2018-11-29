@acceptance-back
# TODO: TO DELETE and link to subscription, bulk subscription and fetch products
Feature: Create proposals
  In order to automatically enrich my products
  As the System
  I want to create proposals from Franklin suggested data

  Scenario: Successfully create a proposal from valid suggested data
    Given the product "B00EYZY6AC" of the family "router"
    And there is suggested data for subscribed product "B00EYZY6AC"
    And the product "B00EYZY6AC" has category "hitech"
    When the system creates proposals for suggested data
    Then there should be a proposal for product "B00EYZY6AC"
    And the suggested data for the subscription of product "B00EYZY6AC" should be empty

  Scenario: Create a proposal when the product is not categorized
    Given the product "B00EYZY6AC" of the family "router"
    And the product "606449099812" of the family "router"
    And there is suggested data for subscribed product "B00EYZY6AC"
    And there is suggested data for subscribed product "606449099812"
    And the product "606449099812" has category "hitech"
    When the system creates proposals for suggested data
    Then there should be a proposal for product "B00EYZY6AC"
    And there should be a proposal for product "606449099812"

  Scenario: Do not create a proposal if suggested data is empty
    Given the product "B00EYZY6AC" of the family "router"
    And the product "606449099812" of the family "router"
    And the product "B00EYZY6AC" has category "hitech"
    And the product "606449099812" has category "hitech"
    And there is suggested data for subscribed product "B00EYZY6AC"
    When the system creates proposals for suggested data
    Then there should be a proposal for product "B00EYZY6AC"
    And the suggested data for the subscription of product "B00EYZY6AC" should be empty
    But there should not be a proposal for product "606449099812"

