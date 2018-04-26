@javascript
Feature: Product creation
  In order to add a non-imported product
  As a product manager
  I need to be able to manually create a product

  Background:
    Given a "footwear" catalog configuration
    And the following products:
    | sku     | family  |
    | sandals | sandals |
    | boots   |         |
    And I am logged in as "Julia"
    And I am on the products grid

  Scenario: Successfully create a product without family
    Given I create a product
    Then I should see the SKU and Family fields
    And I fill in the following information in the popin:
      | SKU | caterpillar_1 |
    And I press the "Save" button in the popin
    Then I should be on the product "caterpillar_1" edit page
    And I should see the text "caterpillar_1"

  Scenario: Successfully create a product with family
    Given I create a product
    Then I should see the SKU and Family fields
    And I fill in the following information in the popin:
      | SKU    | caterpillar_1 |
      | Family | Sandals       |
    And I press the "Save" button in the popin
    Then I should be on the product "caterpillar_1" edit page
    And I should see the text "caterpillar_1"
    And I should see the text "Family sandals"

  Scenario: Fail to create a product with an already used code
    Given I create a product
    And I fill in the following information in the popin:
      | SKU | sandals |
    And I press the "Save" button in the popin
    Then I should see validation error "The same identifier is already set on another product"

  @jira https://akeneo.atlassian.net/browse/PIM-4706
  Scenario: Fail to create a product with a comma in the identifier
    Given I create a product
    And I fill in the following information in the popin:
      | SKU | to,to |
    And I press the "Save" button in the popin
    Then I should see validation error "This field should not contain any comma or semicolon."

  @jira https://akeneo.atlassian.net/browse/PIM-4706
  Scenario: Fail to create a product with a semicolon in the identifier
    Given I create a product
    And I fill in the following information in the popin:
      | SKU | to;to |
    And I press the "Save" button in the popin
    Then I should see validation error "This field should not contain any comma or semicolon."
