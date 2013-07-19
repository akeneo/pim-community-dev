Feature: Remove attribute from a product family
  In order to correct myself when I have wrongly added an attribute into a family
  As an user
  I need to be able to remove an attribute from a family

  @javascript
  Scenario: Successfully remove an attribute in a family
    Given the following family:
      | code |
      | Bags |
    And the following product attributes:
      | label            | family |
      | Long Description | Bags   |
      | Manufacturer     | Bags   |
    And I am logged in as "admin"
    And I am on the "Bags" family page
    And I visit the "Attributes" tab
    When I remove the "Manufacturer" attribute
    Then I should see "The family is successfully updated."
    And I should see attribute "Long Description" in group "Other"

  Scenario: Successfully display an attribute as removable on a product when it has been removed from the family
    Given the following family:
      | code |
      | Bags |
    And the following product attributes:
      | label            | family |
      | Long Description | Bags   |
      | Manufacturer     | Bags   |
    And the following products:
      | sku            | family |
      | bag-dolce-vita | Bags   |
    And the attribute "Manufacturer" has been removed from the "Bags" family
    And I am logged in as "admin"
    When I am on the "bag-dolce-vita" product page
    Then I should see a remove link next to the "Manufacturer" field
