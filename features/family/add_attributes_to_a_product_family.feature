@javascript @info https://akeneo.atlassian.net/browse/PIM-355
Feature: Add attribute to a product family
  In order to validate exported product attributes
  As a user
  I need to be able to define which attributes belong to a family

  Background:
    Given the following families:
      | code       |
      | Smartphone |
      | Bags       |
      | Jewels     |
    And the following attribute group:
      | name    |
      | General |

  Scenario: Successfully list available grouped attributes
    Given the following product attributes:
      | label            | group   |
      | Long Description | General |
      | Manufacturer     | General |
      | Size             |         |
    And I am logged in as "admin"
    When I am on the "Bags" family page
    And I visit the "Attributes" tab
    Then I should see available attributes Long Description and Manufacturer in group "General"
    And I should see available attribute Size in group "[other]"
    But I should not see available attribute SKU in group "[other]"

  Scenario: Successfully display all grouped family's attributes
    Given the following product attributes:
      | label            | group   | family     |
      | Long Description | General | Smartphone |
      | Manufacturer     | General |            |
      | Size             |         | Smartphone |
    And I am logged in as "admin"
    When I am on the "Smartphone" family page
    And I visit the "Attributes" tab
    Then I should see attribute "Long Description" in group "General"
    And I should see attribute "Size" in group "[other]"

  @info https://akeneo.atlassian.net/browse/PIM-244
  Scenario: Successfully add an attribute to a family
    Given the following product attributes:
      | label            | group   |
      | Long Description | General |
      | Manufacturer     | General |
      | Size             |         |
    And I am logged in as "admin"
    When I am on the "Bags" family page
    And I visit the "Attributes" tab
    And I add available attributes Long Description and Size
    Then I should see attribute "Long Description" in group "General"
    And I should see attribute "Size" in group "[other]"
