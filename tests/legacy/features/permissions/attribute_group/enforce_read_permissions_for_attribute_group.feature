@javascript
Feature: Enforce read-only permissions for an attribute group
  In order to be able to prevent some users from editing some product data
  As an administrator
  I need to be able to enforce read-only permissions for attribute groups

  Background:
    Given a "footwear" catalog configuration
    And the following product:
      | sku | family |
      | foo | boots  |
    And the following family variants:
      | code             | family | label-en_US    | variant-axes_1 | variant-attributes_1 |
      | boots_with_color | boots  | Boots by color | color          | color,sku           |
    And the following root product models:
      | code | family_variant   |
      | bar  | boots_with_color |

    And user group "IT support" has the permission to view the attribute group "info"
    And I am logged in as "Peter"

  Scenario: Successfully disable read-only fields for an attribute group in the product edit form
    Given I edit the "foo" product
    Then the fields SKU, Name and Manufacturer should be disabled

  Scenario: Successfully disable read-only fields for an attribute group in the product model edit form
    Given I edit the "bar" product model
    Then the fields Name and Manufacturer should be disabled
