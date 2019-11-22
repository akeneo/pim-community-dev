@acceptance-back
Feature: Validate identifier attribute of a product
  In order to keep my data consistent
  As a regular user
  I need to be able to see validation errors for identifier attribute

  Background:
    Given a catalog with the attribute "sku" as product identifier
    And the following locales "en_US"
    And the following "ecommerce" channel with locales "en_US"

  Scenario: Validate the unique constraint of identifier attribute
    Given a product with an identifier "foo"
    When another product is created with identifier "foo"
    Then the error "The same identifier is already set on another product" is raised

  Scenario: Fail to create a product with an identifier that contains a comma
    When a product is created with identifier "foo,bar"
    Then the error "This field should not contain any comma or semicolon or leading/trailing space" is raised

  Scenario: Fail to create a product with an identifier that contains a semicolon
    When a product is created with identifier "foo;bar"
    Then the error "This field should not contain any comma or semicolon or leading/trailing space" is raised

  Scenario: Fail to create a product with an identifier that ends with a space
    When a product is created with identifier "foo "
    Then the error "This field should not contain any comma or semicolon or leading/trailing space" is raised

  Scenario: Fail to create a product with an identifier that starts with a space
    When a product is created with identifier " foo"
    Then the error "This field should not contain any comma or semicolon or leading/trailing space" is raised

  Scenario: Fail to create a product with an identifier surrounded by spaces
    When a product is created with identifier " foo "
    Then the error "This field should not contain any comma or semicolon or leading/trailing space" is raised
