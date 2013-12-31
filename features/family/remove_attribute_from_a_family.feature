@javascript
Feature: Remove attribute from a family
  In order to correct myself when I have wrongly added an attribute into a family
  As a user
  I need to be able to remove an attribute from a family

  Background:
    Given the "default" catalog configuration
    And the following family:
      | code |
      | Bags |
    And the following attributes:
      | label            | families |
      | Long Description | Bags     |
      | Manufacturer     | Bags     |
    And I am logged in as "admin"

  Scenario: Successfully remove an attribute in a family
    Given I am on the "Bags" family page
    And I visit the "Attributes" tab
    When I remove the "Manufacturer" attribute
    Then I should see flash message "Attribute successfully removed from the family"
    And I should see attribute "Long Description" in group "Other"

  Scenario: Successfully display an attribute as removable on a product when it has been removed from the family
    Given the following products:
      | sku            | family | longDescription | manufacturer |
      | bag-dolce-vita | Bags   | my description  | dolce        |
    And the attribute "Manufacturer" has been removed from the "Bags" family
    When I am on the "bag-dolce-vita" product page
    Then I should see a remove link next to the "Manufacturer" field
