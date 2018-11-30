@acceptance-back
Feature: Bulk subscribes products to Franklin
  In order to automatically enrich my products
  As Julia
  I want to bulk subscribe products to Franklin

  #Scenario: Successfully mass subscribe 3 valid products
  #Then 10 products should be subscribed

  #Scenario: Successfully mass subscribe 3 valid products and 2 invalid products
  #Then 3 products should be subcribed
  #And 2 errors thrown

  #Scenario: Successfully mass subscribe a product to Franklin that does not exist on Franklin
  # Tried with UPC 606449099813
  # Error 500 thrown by Franklin

  #Scenario: Fail to mass subscribe a product without family

  #Scenario: Fail to mass subscribe a product that does not exist

  #Scenario: Fail to mass subscribe a product with an invalid token
  # Should return a 403

  #Scenario: Fail to mass subscribe a product that has an incorrect UPC
  # wrong UPC format

  #Scenario: Fail to mass subscribe a product that is already subscribed to Franklin

  #Scenario: Fail to mass subscribe a product that does not have any values on mapped identifiers

  #Scenario: Fail to mass subscribe a product that does not have one value on mapped identifiers
  # Check with MPN + Brand with Brand not filled

  #Scenario: Fail to mass subscribe a product with an invalid identifiers mapping
  # No request to Franklin

  #Scenario: mass Subscribe a product without enough money on Franklin account
  # Should return a 402

  #Scenario: Handle a mass subscribe bad request to Franklin

  # TODO: Assert proposal creation
