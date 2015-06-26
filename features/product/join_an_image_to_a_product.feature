Feature: Join an image to a product
  In order to join an image to a product
  As a regular user
  I need to be able to upload it and preview it

  Background:
    Given the "default" catalog configuration
    And a "Car" product
    And the following attribute:
      | label  | type  | allowed extensions |
      | Visual | image | jpg                |
    And the "Car" product has the "visual" attribute
    And I am logged in as "Mary"
    And I am on the "Car" product page

  # need proposal
  @skip-pef @javascript
  Scenario: Succesfully leave the image empty
    # Flash message is different from CE
    When I save the product
    Then I should see flash message "Product working copy has been updated"
