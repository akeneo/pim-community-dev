Feature: Edit a product family
  In order to provide accurate information about a product family
  As an user
  I need to be able to edit its code and the translations of its name

  Background:
    Given the following families:
      | code       |
      | smartphone |
      | bags       |
      | jewels     |
    And the following family translations:
      | family     | language | label      |
      | jewels     | english  | Jewels     |
      | jewels     | french   | Bijoux     |
      | smartphone | english  | Smartphone |
      | bags       | english  | Bags       |
    And I am logged in as "admin"
    When I am on the families page

  Scenario: Successfully edit a product family
    Given I edit the "Bags" family
    When I change the Code to "purse"
    And I save the family
    Then I should see "Product family successfully updated"

  Scenario: Fail to set an already used code
    Given I edit the "Bags" family
    When I change the Code to "smartphone"
    And I save the family
    Then I should see "This code is already taken."

  Scenario: Fail to set a non-valid code
    Given I edit the "Bags" family
    When I change the Code to an invalid value
    And I save the family
    Then I should see "The code must only contain alphanumeric characters."

  Scenario: Successfully set the translations of the name
    Given I am on the "Jewels" family page
    And I change the english Label to "Jewelery"
    And I save the family
    Then I should see the families Bags, Jewelery and Smartphone
