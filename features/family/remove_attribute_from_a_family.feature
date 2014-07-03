@javascript
Feature: Remove attribute from a family
  In order to correct myself when I have wrongly added an attribute into a family
  As an administrator
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
    And the following product:
      | sku            | family | longDescription | manufacturer |
      | bag-dolce-vita | Bags   | my description  | dolce        |
    And I am logged in as "Peter"

  Scenario: Successfully remove an attribute from a family and display it as removable from product
    Given I am on the "Bags" family page
    And I visit the "Attributes" tab
    When I remove the "Manufacturer" attribute
    Then I should see flash message "Attribute successfully removed from the family"
    And I should see attribute "Long Description" in group "Other"
    When I am on the "bag-dolce-vita" product page
    Then I should see a remove link next to the "Manufacturer" field
