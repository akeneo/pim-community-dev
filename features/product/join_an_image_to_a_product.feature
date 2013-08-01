Feature: Join an image to a product
  In order to join an image to a product
  As an user
  I need to be able to upload it and preview it

  @javascript
  Scenario: Succesfully leave the image empty
    Given a "Car" product available in english
    And the following product attribute:
      | product | label  | type  |
      | Car     | Visual | image |
    And I am logged in as "admin"
    And I am on the "Car" product page
    And I save the product
    Then I should see "Product successfully saved"

  @javascript
  Scenario: Succesfully upload an image
    Given a "Car" product available in english
    And the following product attribute:
      | product | label  | type  |
      | Car     | Visual | image |
    And I am logged in as "admin"
    And I am on the "Car" product page
    When I attach the file "./features/Context/fixtures/akeneo.jpg" to "Visual"
    And I save the product
    Then I should see "akeneo.jpg"
