@acceptance-back
Feature: Generate Identifier from family

  Scenario: Cannot generate an identifier from family when product has no family
    Given An identifier generator with family property
    And A product without family
    When I generate an identifier for this product
    Then The identifier should not be generated

  Scenario: Cannot generate an identifier from family if family code is too short
    Given An identifier generator with truncate family property and operator = and value 3
    And A product with a family code AA
    When I generate an identifier for this product
    Then The identifier should not be generated
    And I should have an error message

  Scenario: Can generate an identifier from family
    Given An identifier generator with family property
    And A product with family
    When I generate an identifier for this product

