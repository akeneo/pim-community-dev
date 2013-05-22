Feature: Remove an attribute from a product
  In order to reduce undesired amount of attributes on a product
  As an user
  I need to be able to remove an attribute

  Scenario: Fail to remove an attribute belonging to the family of the product
    Given the following family:
      | code    |
      | vehicle |
    And the following product attribute:
      | name  | Group   | family  |
      | Model | General | vehicle |
    And the following product:
      | sku    | family  |
      | kangoo | vehicle |
    And the current language is english
    And I am logged in as "admin"
    And I am on the "kangoo" product page
    Then I should not see a remove link next to the "Model" field

  @javascript
  Scenario: Successfully remove an attribute from a product
    Given the following family:
      | code    |
      | vehicle |
    And the following product:
      | sku    | family  |
      | kangoo | vehicle |
    And the following product attribute:
      | product | name  | group   | family  |
      |         | Model | Other | vehicle |
      | kangoo  | Color | Other |         |
    And the current language is english
    And I am logged in as "admin"
    And I am on the "kangoo" product page
    When I remove the "Color" attribute
    Then I should see "Attribute was successfully removed."
    And attribute in group "Other" should be Model
