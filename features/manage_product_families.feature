@info https://akeneo.atlassian.net/browse/PIM-355
Feature: Manage product families
  In order to validate exported product attributes
  As an user
  I need to be able to define which attributes belong to a family

  Scenario: Successfully display all the families
    Given the following families:
      | name       |
      | Smartphone |
      | Bags       |
      | Jewels     |
    And the current language is english
    And I am logged in as "admin"
    When I am on family page
    Then I should see the families Bags, Jewels and Smartphone

  Scenario: Successfully edit a family name
    Given the following families:
      | name       |
      | Smartphone |
      | Bags       |
      | Jewels     |
    And the current language is english
    And I am logged in as "admin"
    When I am on family page
    And I edit the "Bags" family
    And I change the Name to "Purse"
    And I save the family
    Then I should see the families Jewels, Purse and Smartphone

  Scenario: Successfully list available grouped attributes
    Given the following families:
      | name       |
      | Smartphone |
      | Bags       |
      | Jewels     |
    And the following attribute group:
      | name    |
      | General |
    And the following attributes:
      | name             | group   |
      | Long Description | General |
      | Manufacturer     | General |
      | Size             |         |
    And the current language is english
    And I am logged in as "admin"
    And I am on the "Bags" family page
    Then I should see available attributes Long Description and Manufacturer in group "General"
    And I should see available attribute Size in group "Other"

  @info https://akeneo.atlassian.net/browse/PIM-244
  Scenario: Successfully add an attribute to a family

  Scenario: Sucessfully remove and attribute from a family
