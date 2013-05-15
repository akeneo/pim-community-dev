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

  @info https://akeneo.atlassian.net/browse/PIM-244
  Scenario: Successfully add an attribute to a family

  Scenario: Sucessfully remove and attribute from a family
