@acceptance-back
Feature: Fetch products from PIM.ai
  In order to automatically enrich my products
  As the System
  I want to fetch products I subscribed on from PIM.ai

  Scenario: Fail to fetch products if token is not configured
    Given the PIM.ai token is expired
    And last fetch of subscribed products has been done yesterday
    When the subscribed products are fetched from PIM.ai
    #Then no product subscriptions have been processed (APAI-156)

  Scenario: Successfully fetch products from PIM.ai
    Given PIM.ai is configured with a valid token
    And last fetch of subscribed products has been done yesterday
    When the subscribed products are fetched from PIM.ai
    #Then suggested data should be processed (APAI-156)

  Scenario: Successfully fetch no product from PIM.ai
    Given PIM.ai is configured with a valid token
    And last fetch of subscribed products has been done today
    When the subscribed products are fetched from PIM.ai
    #Then no product subscriptions have been processed (APAI-156)

  #Scenario: Token not configured or invalid

  #Scenario: Identifiers mapping not configured or invalid

  #Scenario: Mapping attributes empty

  #Scenario: Successfully fetch products from PIM.ai from a specific date

  #Scenario: Successfully fetch products from PIM.ai from last launched time

