# Franklin Insights
Franklin Insights is an intelligence layer whose mission is to guide Julia across the PIM to focus on compelling PX

## Ubiquitous language

### Configuration

Configuration and connection status between Akeneo PIM and Franklin Insights

***Token***

The token is a string coming from Franklin Insights

***Connection Status***

Define the status of the connection between Akeneo PIM and Franklin Insights
    
- isActive: define if Franklin has a token configured 
- isValid: define the current status of the connection validating the token
- isIdentifiersMappingValid
- productsCount

***Product Subscription Status***

The subscription status allows to know if a product can be subscribed or not to Franklin Insights

This object contains the connection status and different properties:
- connectionStatus
- isSubscribed
- hasFamily
- isMappingFilled
- isVariant (It would be better to use isSimpleProduct)

### Identifiers & Mapping

***Identifiers*** Franklin works with a set of identifiers (ASIN, UPC and the combination of Brand/MPN).
In order to ease the mapping, we considered Brand and MPN as two different attributes.

***Identifiers Mapping***
This is the collection of all the identifiers from Franklin that can be mapped to PIM attributes.

***Identifier Mapping*** 
    - *Franklin identifier*
        - Franklin identifier code
        - Franklin identifier label
    - *PIM identifier*

### Attributes Mapping 
This is a collection of all the attributes from Franklin that can be mapped to PIM attributes based on a PIM family code.

- ***Family code*** (PIM Family code)
- ***Attribute Mapping***
    - *Franklin attribute*
        - Franklin attribute id
        - Franklin attribute label
    - *PIM attribute*

### Attribute Options Mapping
This is a collection of options from Franklin mapped (or not) to PIM attribute options based on Franklin attribute id and PIM family code.

- ***Attribute Option Mapping***
    - *Franklin attribute id*
    - *Franklin option*
        - Franklin option id
        - Franklin option label
    - *PIM attribute option*

### Subscription

***Product Subscription Id***
When we subscribe a product to Franklin, a subscription id is returned.
It's a string that is calculated from the identifier values sent for subscription.
On Franklin side, it's called product subscription id. 

***Tracker Id***
The tracker id is an extra field that we send to Franklin.
It eases the recovering of the product on which a product subscription is based on.
We use the product id as tracker id in the current implementation.

***Mapped identifiers***
They are the product values mapped following the identifiers mapping.

***Mapped values***
They are the product values mapped following the attributes mapping.

***Requested identifiers***
The requested identifiers are the mapped values that we used to subscribe our product.

***Suggested Data***
The suggested data is the mapped data that comes from AskFranklin. It is used to create proposals.

***Product Subscription Request***

***Product Subscription Response***

### Fetch suggested data
Get back updated suggested data from AskFranklin.

### User intentions

- Get/Save identifiers mapping
- Get/Save attributes mapping
- Get/Save attribute options mapping
- Subscribe/Unsubscribe a product
- Bulk subscribe/unsubscribe products

### System automation

- Fetch subscribed products

## Technical implementation

4 layers:
- Application
- Domain
- Data Provider + Client
- Persistence 

Bounded context not extensible, BC Breaks allowed

### Specificities
- Install
- Decoupling (Application/Domain/Infra)
- Pattern Command/Handler
- Test stack (unit/acceptance/integration/e2e) and mock e2e

### Technical Debt
- aggregates (training happened a bit late so it's not correctly done)
- value objects (VO appeared during the project, we should create more)

### Plug to external components & bounded contexts

- Notifications
- Proposals
- Batch bundle / Job profiles
- Install
- Structure subscribers
- Product subscribers
