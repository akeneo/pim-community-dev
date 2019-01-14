# Decisions

_This document keeps track of all the discussions and decisions we took on the subject and should be updated each time we discuss about a subject worth remembering._

## 10/08/2018

### Validation of commands

Right now we are validating the command given by the action to give feedback to the user. In the case of the creation of an attribute, the user cannot edit all properties. The backend define default values for those properties during the creation of the command and then we validate it.

**This situation can lead to some problems**: if the backend is not well coded, we can be in a situation when the user get messages about fields that he cannot edit.

**Solution**: we think that maybe we should have different validation groups (one for the creation and one for the edition) and define the default values deeper in the stack (the command handler maybe). That way, there will be no chance that the user get errors on fields that he cannot manage.

### Strategy of tests for validation

The validation is an important subject for the testing strategy: it's involving both the backend and the frontend and the contract between them is key. Right now, we are testing the validation with integration tests with in memory repositories for the backend and acceptance tests in the frontend.

**Problem with this solution**: this is really verbose and we are testing all validation cases using http requests. Also the responses are not shared between the frontend and the backend which can lead to mis-alignment.

**Solution**: We think that we should use JSON files to share the responses expected by the backend and the frontend. To do so, we will have one file per response with to mandatory keys: code (string) and body (JSON). This work is potentially huge so we can say that for the next cards we will implement this technique and see how it goes.

## 12/08/2018

### Attribute model (REJECTED)

Currently we have a different model for each attribute type with different signature for all the additional properties. This situation is not really representative of the feature: additional properties are treated separatly in the form and are not shared between attribute types. Also, this implies to have dedicated services during the whole chaine: normalization/denormalization, commands, repositories, savers, reducers, hydrators, etc. Note that this part of the application could be extensible and this situation could be really complicated to manage.

**Problem**: We don't want to manage different model shape for each attribute type, therefore, we want to be sure that all attribute types have the properties they should have.

**Proposition**: I'm not sure if it's the best solution, but maybe we should have a collection of additional properties in the attribute. We will keep the attribute model responsible of their normalization and denormalization but at least, the interfact of the attributes will not change between types.

Simplyfied models to see the difference:

Before:

    ImageAttribute {
      setIdentifier()
      setLabel(locale: string)
      setRequired()
      setMaxFileSize();
      setAllowedExtensions();
      normalize();
    }

After:

    ImageAttribute {
      setIdentifier()
      setLabel(locale: string)
      setRequired()
      setAdditionalProperties(additionalProperties: TextAdditionalProperties)
      normalize();
    }

With the second solution, all attribute type would have the same signature or really close signatures.

### Attribute deletion

Right now in the PIM, it's really hard to manage attribute deletion. If we remove it, it's fine and everything works. But if we re-create an attribute with the same code but not the same scope/locale/type, it can leads to big problems. Dealing with a big clean job can be really complicated and weird for the user experience.

**Problem**: We need to find a solution to deal with attribute deletion that is not costly in term of performances and can avoid future problems.

**Solution**: The idea is to store the important attribute informations alongside with the value:

    {
      attribute: 'description',
      type: 'text',
      valuePerLocale: false,
      valuePerChannel: true,
      value: 'My awesome description
    }

This way, when we hydrate a value from the db, we are able to tell right away if it's in sync with the current attribute type/scope/locale and reject it if needed. This will clearly be a bit more costly to load but it's totally errorproof and should solve the problem we have on this topic. This solution should be benchmarked to be sure that it's not too costly.

This decision should be discussed with Alex

## 16/08/2018

### Attribute deletion

Follow up of [attribute deletion](#attribute-deletion): after some benchmarks, the read operations (mysql, hydration, normalization) are almost free compared to symfony bootup. On a macbook air, it takes 100ms to load 25 attributes while it takes 110ms to load 250 attributes.

We also discussed about a way to invalidate more easily the values of a record after an attribute deletion:

#### First solution: unique hash generated at attribute creation

We store this hash on the values. Each time we load a value, we compare this hash to the actual attribute and if it's different, it means that it has been deleted and re-created.
Pros: Simple solution to implmement, the same for all attribute type and efficient in term of performances.
Cons: If the user delete the attribute and re-create it with the same code, same type and same scopability/localizability, the values will be invalidated whereas they could have been restored.

#### Second solution: sha1 of the attribute type, localizable and scopable

At attribute creation, we calculate a hash of the "structure property" of the attribute into a hash and use it exaclty like the previous solution (store in value+xompare at hydration).
The problem with this solution is that all attribute type does not have the same "structural properties". For example, if you delete a metric attribute and recreate it with the same code, same type, same scope but another metric family, the hydration of the values will fail. So for each attribute type, the `getHash` method could be different.

Pros: Efficient in term of performance, the user can recover data if he deleted the attribute by error.
Cons: require more code and different for each attribute types.

## 23/08/2018

### Attribute deletion

Follow up of [attribute deletion](#attribute-deletion-1): We discussed about the attribute deletion again.

#### Problems:
Having to keep the "timestamp" with the value all the time could be complicated and not wanted: we change the format (which is central in the PIM) of the data to get arround a technical problem. Also, we think that the fact that our current identifier (reference_entity_identifier, identifier) is not time proof.

Alongside those discussions, we rediscussed about UUID and still think that the DX can be much worst with this solution

#### Proposed solution

Merge the two! The idea is to have a composite key composed of: the attribute code, the reference entity code and a ("timestamp"|"fingerprint"|"footprint"|"uniqly generated id").

With that, we could identify our attribute related to it's environment (the reference entity), from the outside (import, export and api uses the attribute code) and through time (with the "timestamp"|"footprint"|"uniqly generated id").

To solve the problem of the DX, the identifier could be generated by concatenating the attribute code, the reference entity code and a generated unique ID.

#### Impacts

##### Database

The database will have a new column of the concatenation of attribute_code, reference_entity_code and a une identifier

    identifier                                      | code        | reference_entity | required | value_per_locale | value_per_channel | ...
    description_designer_23525246353513532523525252 | description | designer         | false    | true             | false             |
    description_brand_7874587587358658265286538653  | description | brand            | false    | true             | false             |

The values will contain only the identifiers alongside the other properties:

    {
      description_designer_23525246353513532523525252: [{
        locale: en_US,
        channel: null,
        data: {...}
      }],
      description_brand_7874587587358658265286538653: [{
        locale: en_US,
        channel: null,
        data: {...}
      }]
    }

Optionnaly, the identifier could be duplicated in the value itself or the values could not be indexed by identifier

##### Boundaries

For the external API, import and exports we will need to map the code and reference entity code because it's not used by the end users. Indeed the identifier will not be exposed

## 03/09/2018

### Record identifiers:

#### Problem:

In almost all cases, we identify the record with it's code and reference entity (import/export, api, urls, rules, product values, etc). We also did a refactor to use a unique identifier (not the composite key) on the record.

So we have a situation where, we almost always have to do a mapping step to convert this composite key to the unique identifier.

#### Proposed solution:

We don't touch anything in database and in models. But we use the composite key (code and reference entity identifier) to fetch the record instead of doing a pre-mapping.

##### In database and in models:

We keep the technical unique identifier

##### for import/export, api, product value, record screen

We use the couple record code and reference entity identifier to fetch the record.

##### for indexation:

We use the unique identifier to fetch the result of the elastic search query

## 19/09/2018

### Set values on Record:

#### Problem:

To set values on the record, we need to validate and update the value regarding the attribute type of this value.

#### Proposed solution:

##### Command Definition :

Create a generic editRecordCommand which will have those properties :
- referenceEntityIdentifier
- code
- labels
- editRecordValueCommands (array of editRecordValueCommand typed by the attribute)

Each editRecordValueCommand will have those properties :
- attribute (the attribute object)
- channel
- label
- data
(Each of those commands will be created via a dedicated factory, registered as such in a registry).

To create the editRecordValueCommand typed by the attribute, we will have to :
 - create an associative array of attribute indexed by identifier for each reference entity (QueryFunction)
 - get the attribute identifier of each raw value to be able to retrieve the attribute (thanks to the array create previously)
 - create the specific editRecordValueCommand of this attribute type

##### Validation :

As we have different validation by type of record value, we have a dedicated validator by type of editRecordValueCommand.
For instance, we will have :
 - EditFileValueCommandValidator to validate the EditFileValueCommand
 - EditTextValueCommandValidator to validate the EditTextValueCommand

#### Set value on the record :

As we could have a different edit of a record value, we have a dedicated updater by type of value.
For instance, we will have :
 -  FileUpdater to update a record value from the EditFileValueCommand
 -  TextUpdater to update a record value from the EditTextValueCommand

## 05/10/2018

### Indexing records with events

#### Problem:

When a record is updated:
 - From where do we send the event ?
 - what does it contain ?
 - What does the listener do (normalization or fetch a record or both) ?

#### Proposed solution:

For sure, we didn't wanted to directly index the record from the repository so the SQL repository stays focus on interacting with the database and so it's easier to test.

4 steps process:
- An event is dispatched from the repository indicating the record needs to be reindexed, from our point of view, it's a technical event: `RecordUpdatedEvent`
- This event contains only the identifier of the record to be reindexed
- The listener (`IndexRecordListener`) uses this Id, to fetch the record from the repository and passes it to an indexer `RecordIdexer` as an array
- The `RecordIndexer` gathers normalizes the Records, and calls the ES client for indexing

##### Indexing completeness

Later on, we can imagine that when calculating the completeness of products, we might be able to reindex only this property in the record's document using a `CompletenessIndexer`.

##### Challenges

When the reindexation of a lot of records is needed (like 1 million), how do we handle such a task ? The use of background jobs seems appropriate yet it raises questions like:
- How long does it take to reindex such a volume of record ?
- It it going to block the job queue ?

This case can happen when an attribute text is removed from the enriched entity and this property is used for the search in ES, so it needs to be totally recalculated.

## 06/10/2018

### API port and hexagon

#### Problem:

Given we will need to expose the data about the reference entities and records through the API, as well as be able update those data,

how will we handle those usecases regarding:
- The transformation of json array into commands ?
- Are those commands the same than the one we already have ?
- Are they validated differently ? or do they reuse the validators we have ?
- Are the messages and property path inside those violations the same between the UI (internal api) and the external Api ?

#### Proposed solution:

##### Edit usecases:

- We will have a different controller for each endpoint of the external API.
- We think the format the UI (internal API) and the external API will be similar, hence **we can reuse the command factories already present in src/Application**.
- The intention of the user is the same when using the UI and the API, hence **we will reuse those commands to call the handlers**.
- The constraints on those commands are the same, as well as the messages of the violations and the property paths, hence **we will reuse the validators** (the violations will be normalized differently in the external API, but that's not a problem since it will be done in a different controller).
- The API will check the format of request bodies to give a nice feedback to the user using jsonSchema, this check will be performed in the controllers. (in the UI port, 500 error occurs when the request body does not permit to create a command with it).

##### Read usecases:

- We will use a different read model for each read usecases like `Read a record details` as those models may differ a lot between the UI and the external API.
- There will be different query functions used to generate those read models.

## 12/10/2018

### Flat indexing of records in search engine

#### Problem:

##### Today:

The search is possible thanks to the following indexing format:

    [
        "identifier": "starck_designer_23525246353513532523525252"
        "reference_entity_identifier": "designer",
        "code": "starck",
        "labels": [
            "fr_FR": "Philippe Starck"
        ],
        "record_list_search": [
            "ecommerce": [
                "fr_FR": "designer Philippe Starck Né à Paris, Philippe Starck ...",
                "en_US": "designer Philippe Starck Born in Paris, Philippe Starck ...",
            ],
            "mobile": [
                "fr_FR": "designer Philippe Starck Célèbre designer.",
                "en_US": "designer Philippe Starck Famous designer.",
            ]
        ]
    ]

In the matrix: record_list_search (which is indexed by channel, locale) the value have the following pattern: "{record_code} {label_for_locale} {all values separated by a space for this locale and channel}".

The search would be performed using the following query:

    // Search for "Bordeaux Nantes" in ecommerce/fr_FR.
    [
        "_source": "_id",
        "from": 0,
        "size": 100,
        "sort": ["identifier": "asc"],
        "query"  : [
            "constant_score": [
                "filter": [
                    "bool": [
                        "filter": [
                            [
                                "term": [
                                    "reference_entity_code": "designer",
                                ],
                            ],
                            [
                                'query_string': [
                                    "default_field": "record_list_search.ecommerce.fr_FR"
                                    "query": "*Bordeaux* AND *Nantes*"
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]

##### The issue:
The issue with this indexing format is that whenever the structure of a reference entity changes (ie, an attribute is removed), every records belonging to this reference entity *needs to be reindexed* to remove the values linked to the removed attribute.

This task can take some time and requires us to launch a background job responsible for the reindexing of the records.

#### Proposed solution:

##### The "flat keys" indexing format

The search would be possible thanks to the following indexing format:

    [
        "identifier": "starck_designer_23525246353513532523525252"
        "reference_entity_identifier": "designer",
        "code": "starck",
        "labels": [
            "fr_FR": "Philippe Starck"
        ],
        "description_ecommerce_fr_FR_88b980b0-d05e-11e8-a8d5-f2801f1b9fd1": "Né à Paris, Philippe Starck ...",
        "description_ecommerce_en_US_88b980b0-d05e-11e8-a8d5-f2801f1b9fd1": "Born in Paris, Philippe Starck ...",
        "description_mobile_en_US_88b980b0-d05e-11e8-a8d5-f2801f1b9fd1": "Famous designer.",
        "description_mobile_fr_FR_88b980b0-d05e-11e8-a8d5-f2801f1b9fd1": "Célèbre designer.",
    ]

The keys of the flat format, corresponds the the "Value keys" (see Akeneo\ReferenceEntity\Domain\Query\Attribute\ValueKey).

The search becomes:

    // Search for "Bordeaux Nantes" in ecommerce/fr_FR.
    [
        "_source": "_id",
        "from": 0,
        "size": 100,
        "sort": ["identifier": "asc"],
        "query"  : [
            "constant_score": [
                "filter": [
                    "bool": [
                        "filter": [
                            [
                                "term": [
                                    "reference_entity_code": "designer",
                                ],
                            ],
                            [
                                'query_string': [
                                    "fields": ["description_ecommerce_fr_FR", "description_ecommerce_en_US", "description_mobile_en_US", "description_mobile_en_US"] // List generated after the reference entity structure
                                    "query": "*Bordeaux* AND *Nantes*"
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ]

Whenever the "description" attribute is removed, we do not need to reindex the document as there  will be no way that a user will filter on it since it does not belong to the reference entity's structure anymore (even if this attribute is recreated with the same code thanks to the uuid).

The next time this record is saved, the description value keys will be removed and the ES document will be clean.

The issue with this search model is that the size of the request is now dependent of the structure size, the more the attributes of the reference entity, the bigger the request and the more fields ES has to search on.

#### Benchmarking

We've run some benchmark trying to see how munch longer would it take for ES to perform the search comparing to the first search model. Here are the resuts:

##### Protocol

- Index 10 000 records in the target format
- Each record has 100 attributes (non scopable non localizable which is the worst case scenario) (PO said it would never go higher than this 100 limit).
- Perform the search by changing some axis as shown below:
    - Number of words
    - Is the search full text "*Borde*" or is it just a start with ("Bordea*")
    - The number of attributes we search on

##### Mappings

In the first search model (with the "record_list_search" field), all the fields are indexed using the ES "keyword" type.

In the second model ("flat keys"), the fields are tokenized using the ES standard tokenizer.
What this means is that whenever we send a value which should be tokenized, ES will break the words into smaller words and index them internally so that when we search for the beginning of a word ES is faster at giving results (see https://www.elastic.co/guide/en/elasticsearch/reference/5.6/analysis-tokenizers.html).

##### Results

|                                              | Search Model 1 "Keyword" | Search Model 2 "Flat keys" | Ratio |
|----------------------------------------------|--------------------------|----------------------------|-------|
| 10 words full text ("*bor*")                 | 45 ms                    | 3200 ms                    | x71   |
| 3 words full text ("*bor*")                  | 15 ms                    | 650 ms                     | x43   |
| 3 words start with ("bor*")                  | 6 ms                     | 150 ms                     | x37   |
| 3 words equals ("bordeaux")                  | 3 ms                     | 130 ms                     |       |
| 3 words full text (search on 20 attributes)  |                          | 120 ms                     |       |
| 3 words start with (search on 20 attributes) |                          | 40 ms                      |       |

The search model 1 performs better than the search model 2 (x71 times better).

When degrading the functionnal need (search on start with instead of full text), the search model 2 is still x37 times slower.

To reach a satifying response time with the search model 2, we need to significantly degrate the functionnal need (search on 3 words and 20 attributes max).

#### Status: Rejected

*Decision*: *We will implement the search model 1 "keyword" and try to improve the indexing time as munch as we can so it does not become a bottleneck on the PIM*.

## 29/10/2018

### Operators in filter

#### Problem:

On the PIM today, the operators use a mix of algebric symbols (`<`, `=`, `!=`), SQL style operators (`NOT IN`, `IN`, etc) and homemade operators (`AT_LEAST_COMPLETE_ON_ONE_LOCALE`, etc). Problem is that to keep track of all of them and to be consistent, we implemented a [Operators.php](https://github.com/akeneo/pim-community-dev/blob/cf809f1895afe7a0e916aa2b84fccf9f1f208632/src/Akeneo/Pim/Enrichment/Component/Product/Query/Filter/Operators.php) class.

#### Solution

We think that we should still use a class to contains all the operators. But we should also normalize those operators to avoid mapping as much as possible.

To sum up, the operator to check equality should be named 'EQUALS' and be used by the front and the backend.

## 10/12/2018

### Right management discussion

Right now, the EE permissions only applies to products and product informations. So it has been implicit that those rights were products permissions for the categories, attribute groups and locales.
With the reference entity subject we needed to add new permission controls over localizable values of records.

### Propositions:

So we asked our product owner what was the best solution among those two:
- The first one was to add a new dedicated permission "Allowed to edit reference entity information". This solution will imply to split permission by object type (product, reference entity, etc).
- The second one, consist of generalize the existing permission "Allowed to edit product information" to become "Allowed to edit information" (wording may change of course).

### Solution

The response from the product owner is as follow: It doesn't make sense to differenciate rights on locales for products and reference entities.
For example, a translator that could view the English and edit the German product attributes will not need to have rights to edit English attributes and view Spanish on records.
If the need is to restrict rights on a particular type of entity, it's more of an ACL concern or a permission by reference entity (which will also be provided).

## 11/12/2018

### Bulletproof reference entities

#### Problem:

Today the reference entities suffer the same drawbacks of the product concerning entities linked to it.
For instance:
- When an attribute option is removed, the completeness results will be impacted for records who had only this attribute option.
- When a record is removed, the completeness results will be impacted for records only linked to the removed record.
- When all records are removed, what happens to other records referencing the removed records ?
- The same goes for the assets (maybe in the future).

Basically, an entity deletion that is linked in the values of the records imply that those record's values are refreshed (or cleaned) and reindexed to keep the search on completeness and read models synchronized with the structure and the other entities (Eventual consistency).

#### Solutions:

We identified 3 usecases we need to fix. In all of those usecases the solution is to select the impacted records and to refresh them (or clean their values). The way we select those records defers for each usecase.

**Elasticsearch index update**

To support our new search usecases, we need to update the index with the "links" the record is linked to (attribute options or other records):

    [
        'reference_entity_code'   => 'designer',
        'identifier'              => 'designer_stark',
        'code'                    => 'stark',
        'updated_at'              => date_create('2018-01-01')->getTimestamp(),
        'links'       => [
            'record' => [
                'brand' => ['brand_kartell'], // Link to a specific reference entity and a specific record
            ],
            'option' => [
                'color_brand_fingerprint' => ['red', 'blue'] // link to a specific attribute and a specific attribute option
            ]
        ],
    ]

**When a record is removed**, we need to refresh all the records refencing the removed record.

    // 'brand_kartell' record has been removed, let's find all the record linked to it to refresh them
    [
        '_source' => '_id',
        'query'   => [
            'constant_score' => [
                'filter' => [
                    'bool' => [
                        'filter' => [
                            [
                                'term' => [
                                    'links.record.brand' => 'brand_kartell',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ]


**When all records of a reference entity are removed**, we need to refresh all the records refencing the removed records.

    // All the records of 'brand' have been removed, let's find all the records linked to them to refresh them
    [
        '_source' => '_id',
        'sort'    => ['updated_at' => 'desc'],
        'query'   => [
            'constant_score' => [
                'filter' => [
                    'bool' => [
                        'filter' => [
                            [
                                'exists' => [
                                    'field' => 'links.record.brand'
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ]

**When an option of an attribute is removed**, we need to refresh all the records refencing the removed option.

    // Options 'blue' and 'yellow' have been removed from attribute 'color_brand_fingerprint'
    [
        '_source' => '_id',
        'query'   => [
            'constant_score' => [
                'filter' => [
                    'bool' => [
                        'filter' => [
                            [
                                'terms' => [ // <-- See the usage of 'terms' operator here as it is likely that multiple options might be removed at once
                                    'links.option.color_brand_fingerprint' => ['blue', 'yellow']
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ]


## 07/01/19

### Use of constructor for commands

#### Problem

Let's start by defining what is a command on our (bounded context) BC and how we use them:

- They represent the user intention
- They are plain php object
- They don't have any logic nor constructor
- Their properties are public
- We create them with the new operator in the infrastructure layer

They are really convenient, simple to create and manipulate.

Here is how we initalize one today:

    $createImageAttributeCommand = new CreateImageAttributeCommand();
    $createImageAttributeCommand->referenceEntityIdentifier = $referenceEntityIdentifier->normalize();
    $createImageAttributeCommand->code = 'image';
    $createImageAttributeCommand->labels = [];
    $createImageAttributeCommand->isRequired = false;
    $createImageAttributeCommand->valuePerChannel = false;
    $createImageAttributeCommand->valuePerLocale = false;
    $createImageAttributeCommand->maxFileSize = '8192';
    $createImageAttributeCommand->allowedExtensions = [];

We see different isssues here:

- There is no type checks on the properties of the command (so if we do a mistake here, it will fail later in the process and it will be difficult to debug it)
- If we add a new property to this command, it's not easy to know where to add it

#### Proposed solution

Use proper constructor on our commands.

#### How to?

We will dedicate someone on the next bloom to list all comamnds and create subtasks.
Then we will be able to create a PR to fix them one by one (use of a feature branch could be possible).
Then take cards one by one and fix them. It should not create any conflicts
