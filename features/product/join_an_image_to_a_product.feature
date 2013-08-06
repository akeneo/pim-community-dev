Feature: Join an image to a product
  In order to join an image to a product
  As an user
  I need to be able to upload it and preview it

  Scenario: Succesfully leave the image empty
    Given a "Car" product
    And the following product attribute:
      | product | label  | type  |
      | Car     | Visual | image |
    And I am logged in as "admin"
    And I am on the "Car" product page
    And I save the product
    Then I should see "Product successfully saved"

  Scenario: Succesfully upload an image
    Given a "Car" product
    And the following product attribute:
      | product | label  | type  |
      | Car     | Visual | image |
    And I am logged in as "admin"
    And I am on the "Car" product page
    When I attach the file "akeneo.jpg" to "Visual"
    And I save the product
    Then I should see "akeneo.jpg"

  @javascript
  Scenario: Successfully remove an image
    Given a "Car" product
    And the following product attribute:
      | product | label  | type  |
      | Car     | Visual | image |
    And I am logged in as "admin"
    And I am on the "Car" product page
    When I attach the file "akeneo.jpg" to "Visual"
    And I save the product
    And I check "Remove media"
    And I save the product
    Then I should not see "akeneo.jpg"

  @javascript
  Scenario: Successfully replace an image
    Given a "Car" product
    And the following product attribute:
      | product | label  | type  |
      | Car     | Visual | image |
    And I am logged in as "admin"
    And I am on the "Car" product page
    When I attach the file "akeneo.jpg" to "Visual"
    And I save the product
    And I attach the file "akeneo2.jpg" to "Visual"
    And I save the product
    Then I should not see "akeneo.jpg"
    But I should see "akeneo2.jpg"

  @javascript
  Scenario: Successfully replace and remove an image
    Given a "Car" product
    And the following product attribute:
      | product | label  | type  |
      | Car     | Visual | image |
    And I am logged in as "admin"
    And I am on the "Car" product page
    When I attach the file "akeneo.jpg" to "Visual"
    And I save the product
    And I attach the file "akeneo2.jpg" to "Visual"
    And I check "Remove media"
    And I save the product
    Then I should not see "akeneo.jpg"
    But I should see "akeneo2.jpg"
