Feature: Be able to mass update status of children products if product models are selected
  In order change status on many product models at once
  As a product manager
  I need to be able to use the mass edit on product models status

  @javascript
  Scenario: Successfully mass update status of product models
    Given a "catalog_modeling" catalog configuration
    And I am logged in as "Julia"
    And I am on the products grid
    And I sort by "ID" value ascending
    And I select rows amor and aphrodite
    And I press the "Bulk actions" button
    And I choose the "Change status" operation
    And I disable the products
    And I wait for the "update_product_value" job to finish
    When I am on the products grid
    And I filter by "parent" with operator "in list" and value "amor, aphrodite"
    Then I should see products 1111111111, 1111111112, 1111111113, 1111111114, 1111111115, 1111111116, 1111111117, 1111111118
    And the row "1111111111" should contain:
      | column           | value      |
      | ID               | 1111111111 |
      | Status           | disabled   |
    And the row "1111111112" should contain:
      | column           | value      |
      | ID               | 1111111112 |
      | Status           | disabled   |
    And the row "1111111113" should contain:
      | column           | value      |
      | ID               | 1111111113 |
      | Status           | disabled   |
    And the row "1111111114" should contain:
      | column           | value      |
      | ID               | 1111111114 |
      | Status           | disabled   |
    And the row "1111111115" should contain:
      | column           | value      |
      | ID               | 1111111115 |
      | Status           | disabled   |
    And the row "1111111116" should contain:
      | column           | value      |
      | ID               | 1111111116 |
      | Status           | disabled   |
    And the row "1111111117" should contain:
      | column           | value      |
      | ID               | 1111111117 |
      | Status           | disabled   |
    And the row "1111111118" should contain:
      | column           | value      |
      | ID               | 1111111118 |
      | Status           | disabled   |
