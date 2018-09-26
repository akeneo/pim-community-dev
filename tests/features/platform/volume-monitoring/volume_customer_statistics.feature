Feature: Volume statistics of the customers
  In order to better know the usage of the PIM
  As Akeneo Company
  I want to monitor anonymously volumes of the customers

  @acceptance-back
  Scenario: Gather customers statistics about the number of channels
    Given a catalog with 3 channels
    When statistics of the customer's catalog are collected
    Then Akeneo statistics engine stores a number of 3 channels for this customer

  @acceptance-back
  Scenario: Gather customers statistics about the number of locales
    Given a catalog with 6 locales
    When statistics of the customer's catalog are collected
    Then Akeneo statistics engine stores a number of 6 locales for this customer

  @acceptance-back
  Scenario: Gather customers statistics about the number of products
    Given a catalog with 10 products
    When statistics of the customer's catalog are collected
    Then Akeneo statistics engine stores a number of 10 products for this customer

  @acceptance-back
  Scenario: Gather customers statistics about the number of product models
    Given a catalog with 8 product models
    When statistics of the customer's catalog are collected
    Then Akeneo statistics engine stores a number of 8 product models for this customer

  @acceptance-back
  Scenario: Gather customers statistics about the number of variant products
    Given a catalog with 5 variant products
    When statistics of the customer's catalog are collected
    Then Akeneo statistics engine stores a number of 5 variant products for this customer

  @acceptance-back
  Scenario: Gather customers statistics about the number of families
    Given a catalog with 7 families
    When statistics of the customer's catalog are collected
    Then Akeneo statistics engine stores a number of 7 families for this customer

  @acceptance-back
  Scenario: Gather customers statistics about the number of users
    Given a catalog with 22 users
    When statistics of the customer's catalog are collected
    Then Akeneo statistics engine stores a number of 22 users for this customer

  @acceptance-back
  Scenario: Gather customers statistics about the number of categories
    Given a catalog with 5 categories
    When statistics of the customer's catalog are collected
    Then Akeneo statistics engine stores a number of 5 categories for this customer

  @acceptance-back
  Scenario: Gather customers statistics about the number of category trees
    Given a catalog with 7 category trees
    When statistics of the customer's catalog are collected
    Then Akeneo statistics engine stores a number of 7 category trees for this customer

  @acceptance-back
  Scenario: Gather customers statistics about the maximum of categories in one category
    Given a catalog with 8 categories in one category
    When statistics of the customer's catalog are collected
    Then Akeneo statistics engine stores a maximum number of 8 categories in one category for this customer

  @acceptance-back
  Scenario: Gather customers statistics about the maximum of category levels
    Given a catalog with 12 category levels
    When statistics of the customer's catalog are collected
    Then Akeneo statistics engine stores a maximum number of 12 category levels for this customer

  @acceptance-back
  Scenario: Gather customers statistics about the size of the catalog
    The size of the catalog is defined by the number of product values`
    Given a catalog with 487520 product values
    When statistics of the customer's catalog are collected
    Then Akeneo statistics engine stores a number of 487520 product values for this customer

  @acceptance-back
  Scenario: Gather customers statistics about the average size of the products
    The average size of the products is defined by the average number of product values that it contains
    Given a product with 587 product values
    And a product model with 565 product values
    When statistics of the customer's catalog are collected
    Then Akeneo statistics engine stores an average number of 576 product values for this customer

  @acceptance-back
  Scenario: Gather customers statistics about the average potential size of the products
    The potential size of a product is defined by the number of product values that it could contain
    and the potential number of product values is defined by the attributes in its family
    Given a channel defined with 2 activated locales
    And a family with 7 attributes, 8 localizable attributes, 4 scopable attributes and 6 scopable and localizable attributes
    And a family with 2 attributes, 3 localizable attributes, 1 scopable attributes and 5 scopable and localizable attributes
    When statistics of the customer's catalog are collected
    Then Akeneo statistics engine stores an average number of 29 product values per family for this customer

  @acceptance-back
  Scenario: Gather customers statistics about the maximum potential potential size of the products
    The potential size of a product is defined by the number of product values that it could contain
    and the potential number of product values is defined by the attributes in its family
    Given a channel defined with 2 activated locales
    And a channel defined with 3 activated locales
    And a family with 2 attributes, 3 localizable attributes, 4 scopable attributes and 6 scopable and localizable attributes
    And a family with 4 attributes, 3 localizable attributes, 2 scopable attributes and 5 scopable and localizable attributes
    When statistics of the customer's catalog are collected
    Then Akeneo statistics engine stores a maximum number of 85 product values per family for this customer

  @acceptance-back
  Scenario: Gather customers statistics about the number of attributes useable as grid filter
    Given a catalog with 10 useable as grid filter attributes
    When attribute statistics of the customer's catalog are collected
    Then Akeneo statistics engine stores a number of 10 useable as grid filter for this customer

  @acceptance-back
  Scenario: Gather customers statistics about the average number of localizable attributes per family
    Given a family with 7 localizable attributes, 8 scopable attributes, 4 localizable and scopable attributes and 6 attributes
    And a family with 0 localizable attributes, 2 scopable attributes, 2 localizable and scopable attributes and 2 attributes
    And a family with 3 localizable attributes, 0 scopable attributes, 0 localizable and scopable attributes and 2 attributes
    When attribute statistics of the customer's catalog are collected
    Then Akeneo statistics engine stores an average percentage of 29 localizable attributes per family for this customer

  @acceptance-back
  Scenario: Gather customers statistics about the average number of scopable attributes per family
    Given a family with 7 scopable attributes, 8 localizable attributes, 4 localizable and scopable attributes and 6 attributes
    And a family with 0 scopable attributes, 2 localizable attributes, 2 localizable and scopable attributes and 2 attributes
    When attribute statistics of the customer's catalog are collected
    Then Akeneo statistics engine stores an average percentage of 14 scopable attributes per family for this customer

  @acceptance-back
  Scenario: Gather customers statistics about the average number of localizable and scopable attributes per family
    Given a family with 2 localizable and scopable attributes, 4 localizable attributes, 4 scopable attributes and 4 attributes
    And a family with 0 localizable and scopable attributes, 0 localizable attributes, 5 scopable attributes and 5 attributes
    When attribute statistics of the customer's catalog are collected
    Then Akeneo statistics engine stores an average percentage of 7 localizable and scopable attributes per family for this customer
