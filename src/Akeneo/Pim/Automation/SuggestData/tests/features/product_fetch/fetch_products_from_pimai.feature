@acceptance-back
Feature: Fetch products from PIM.ai
  In order to automatically enrich my products
  As the System
  I want to fetch products I subscribed on from PIM.ai

  Scenario: Fail to fetch products if token is not configured
    Given PIM.ai has not been configured
    When the subscribed products are fetched from PIM.ai
    Then new suggested data should be processed

  #Scenario: Successfully fetch products from PIM.ai

  #Scenario: Token not configured or invalid

  #Scenario: Identifiers mapping not configured or invalid

  #Scenario: Mapping attributes empty

  #Scenario: Successfully fetch products from PIM.ai from a specific date

  #Scenario: Successfully fetch products from PIM.ai from last launched time



