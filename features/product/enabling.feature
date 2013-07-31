Feature: Enable and disable a product
  In order to avoid exportation of some products I'm still working on
  As a user
  I need to be able to enable or disable a product

  Scenario: Successfully disable a product
    Given an enabled "boat" product
    And I am logged in as "admin"
    When I am on the "boat" product page
    And I disable the product
    Then I should see "Product successfully saved"
    And product "boat" should be disabled

  Scenario: Successfully enable a product
    Given a disabled "boat" product
    And I am logged in as "admin"
    When I am on the "boat" product page
    And I enable the product
    Then I should see "Product successfully saved"
    And product "boat" should be enabled
