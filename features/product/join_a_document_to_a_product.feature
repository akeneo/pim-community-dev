Feature: Join a document to a product
  In order to join a document to a product
  As an user
  I need to be able to upload it and preview it

  Scenario: Succesfully leave the documet empty
    Given a "Car" product available in english
    And the following product attribute:
      | product | label       | type |
      | Car     | Description | file |
    And I am logged in as "admin"
    And I am on the "Car" product page
    And I save the product
    Then I should see "Product successfully saved"

  Scenario: Succesfully upload a document
    Given a "Car" product available in english
    And the following product attribute:
      | product | label       | type |
      | Car     | Description | file |
    And I am logged in as "admin"
    And I am on the "Car" product page
    When I attach the file "akeneo.txt" to "Description"
    And I save the product
    Then I should see "akeneo.txt"

  @javascript
  Scenario: Succesfully display the document
    Given a "Car" product available in english
    And the following product attribute:
      | product | label       | type |
      | Car     | Description | file |
    And I am logged in as "admin"
    And I am on the "Car" product page
    When I attach the file "akeneo.txt" to "Description"
    And I save the product
    And I follow "akeneo.txt"
    Then I should see the "akeneo.txt" content

  @javascript
  Scenario: Successfully remove a document
    Given a "Car" product available in english
    And the following product attribute:
      | product | label       | type |
      | Car     | Description | file |
    And I am logged in as "admin"
    And I am on the "Car" product page
    When I attach the file "akeneo.txt" to "Description"
    And I save the product
    And I check "Remove media"
    And I save the product
    Then I should not see "akeneo.txt"

  @javascript
  Scenario: Successfully replace a document
    Given a "Car" product available in english
    And the following product attribute:
      | product | label       | type |
      | Car     | Description | file |
    And I am logged in as "admin"
    And I am on the "Car" product page
    When I attach the file "akeneo.txt" to "Description"
    And I save the product
    When I attach the file "akeneo2.txt" to "Description"
    And I save the product
    Then I should not see "akeneo.txt"
    But I should see "akeneo2.txt"

  @javascript
  Scenario: Successfully replace and remove a document
    Given a "Car" product available in english
    And the following product attribute:
      | product | label       | type |
      | Car     | Description | file |
    And I am logged in as "admin"
    And I am on the "Car" product page
    When I attach the file "akeneo.txt" to "Description"
    And I save the product
    And I attach the file "akeneo2.txt" to "Description"
    And I check "Remove media"
    And I save the product
    Then I should not see "akeneo.txt"
    But I should see "akeneo2.txt"
