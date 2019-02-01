#Franklin Insights
Franklin Insights is an intelligence layer whose mission is to guide Julia across the PIM to focus on compelling PX

##Ubiquitous language

###Configuration

Configuration and connection status between Akeneo PIM and Franklin Insights

***Token***

The token is a string coming from Franklin Insights

***Connection Status***

Define the status of the connection between Akeneo PIM and Franklin Insights
    
- isActive: define if Franklin has a token configured 
- isValid: define the current status of the connection validating the token
- isIdentifiersMappingValid
- productsCount

***Subscription Status***

The subscription status allows to know if a product can be subscribed or not to Franklin Insights

This object contains the connection status and different properties:
- connectionStatus
- isSubscribed
- hasFamily
- isMappingFilled
- isVariant (@camille: Should we replace this by isSimpleProduct?)


###Identifiers & Mapping

***Identifiers*** Franklin works with a set of identifiers (ASIN, UPC and the combination of Brand/MPN).
In order to ease the mapping, we considered Brand and MPN as two different attributes.

***Identifiers Mapping***
This is the collection of all the identifiers from Franklin mapped (or not) to catalog attributes.

***Identifier Mapping*** 

***Franklin identifier***
    Franklin identifier code
    Franklin identifier label
    
***Catalog identifier*** (@camille: Do you prefer PIM identifier? More commonly, should we talk about catalog or PIM?)

###Attributes Mapping 

***Attributes Mapping***

***Attribute Mapping***

***Franklin attribute***
    Franklin attribute id
    Franklin attribute label

***Catalog attribute***

###Attribute Options Mapping

***Attribute Options Mapping***

***Attribute Option Mapping***

***Franklin option***
    Franklin option id
    Franklin option label

***Catalog option*** (@camille: Should we use options or attribute options?)

###Subscription

***Subscription Id***

***Tracker Id***

***Requested identifiers***

***Mapped values***

***Suggested Data***

***Subscription Request***

***Subscription Response***


###Fetch suggested data


### User intentions

- Get/Save identifiers mapping
- Get/Save attributes mapping
- Get/Save attribute options mapping
- Subscribe/Unsubscribe a product
- Bulk subscribe/unsubscribe products

### System automation

- Fetch subscribed products



### Misc



## Functional scope


## Technical implementation

4 layers:
- Application
- Domain
- Data Provider + Client
- Persistence 

Bounded context not extensible, BC Breaks allowed

### Specificities
- Install
- 

### Technical Debt
- aggregates
- read models
- value objects

### Plug to external components & bounded contexts

- Notifications
- Proposals
- Batch bundle / Job profiles
- Install
- Structure subscribers
- Product subscribers

