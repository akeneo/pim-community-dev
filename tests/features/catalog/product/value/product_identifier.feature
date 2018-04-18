Feature: Validate identifier attribute of a product
  In order to keep my data consistent
  As a regular user
  I need to be able to see validation errors for identifier attribute

  @acceptance
  Scenario: Validate the unique constraint of identifier attribute
    Given a product with an identifier "foo"
    When a product is created with identifier "foo"
    Then an error should be raised because of "The same identifier is already set on another product"
