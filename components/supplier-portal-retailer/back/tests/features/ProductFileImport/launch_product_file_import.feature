Feature: Supplier Portal - Product File Import - launch a product file import

  Scenario:
    Given a product file
    When I import the product file "file.xlsx"
    Then I should be redirected to "http://www.google.fr"

  Scenario:
    Given a product file
    When I import the product file "unknown_file.xlsx"
    Then I should have a product file does not exist error

