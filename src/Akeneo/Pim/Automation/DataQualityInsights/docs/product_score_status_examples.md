# Example mapping for Data quality score status
1. [General](#General)
   1. [DQI feature is fully disabled](#DQI feature is fully disabled)
   2. [PIM is initialized there is no calculated evaluation or calculated score neither](#PIM is initialized there is no calculated evaluation or calculated score neither)
   3. [All products are evaluated](#All products are evaluated)
2. [Mass edition of product](#Mass edition of product)
   1. [Multiple products have been updated from the mass edition in product grid](#Multiple products have been updated from the mass edition in product grid)
   2. [Multiple products have been updated from the API](#Multiple products have been updated from the API)
   3. [Multiple products have been updated from an import job](#Multiple products have been updated from an import job)
3. [Product edition](#Product edition)
   1. [A product has been updated from the product edit form](#A product has been updated from the product edit form)
4. [Catalog structure edition](#Catalog structure edition)
   1. [DQI has been enabled/disabled on an attribute group](#DQI has been enabled/disabled on an attribute group)
   2. [An attribute label has been updated (EE)](#An attribute label has been updated (EE))
   3. [An attribute option has been updated (EE)](#An attribute option has been updated (EE))


# General 

## DQI feature is fully disabled

| Product grid                 | Product edit form               |
|------------------------------|---------------------------------|
| The Quality column is hidden | The quality score bar is hidden |

## PIM is initialized there is no calculated evaluation or calculated score neither
There is no data in DQI tables (product_evaluation|score, product_model_evaluation|score) in database

| Product grid | Product edit form |
|--------------|-------------------|
 | score hidden | score hidden      |

## All products are evaluated

| Product grid       | Product edit form   |
|--------------------|---------------------|
| score is displayed | score is displayed  |


# Mass edition of product

## Multiple products have been updated from the mass edition in product grid

|                           | Product grid | Product edit form |
|---------------------------|--------------|-------------------|
| DQI cronjobs not executed |              |                   |
| DQI evaluations prepared  |              |                   |
| evaluation calculated     |              |                   |

## Multiple products have been updated from the API

|                           | Product grid | Product edit form |
|---------------------------|--------------|-------------------|
| DQI cronjobs not executed |              |                   |
| DQI evaluations prepared  |              |                   |
| evaluation calculated     |              |                   |

## Multiple products have been updated from an import job

|                           | Product grid | Product edit form |
|---------------------------|--------------|-------------------|
| DQI cronjobs not executed |              |                   |
| DQI evaluations prepared  |              |                   |
| evaluation calculated     |              |                   |

# Product edition

## A product has been updated from the product edit form

|                                          | Product edit form | Product grid |
|------------------------------------------|-------------------|--------------|
| Page loaded without DQI info             |                   |              |
| DQI info loaded                          |                   |              |
|                                          |                   |              |
| Save in progress                         |                   |              |
| Product saved without refreshed DQI info |                   |              |
|                                          |                   |              |
| Quality evaluation in progress           |                   |              |
| Refreshed DQI info loaded                |                   |              |


# Catalog structure edition

## DQI has been enabled/disabled on an attribute group

|                           | Product grid | Product edit form |
|---------------------------|--------------|-------------------|
| DQI cronjobs not executed |              |                   |
| DQI evaluations prepared  |              |                   |
| evaluation calculated     |              |                   |

## An attribute label has been updated (EE)

|                           | Product grid | Product edit form |
|---------------------------|--------------|-------------------|
| DQI cronjobs not executed |              |                   |
| DQI evaluations prepared  |              |                   |
| evaluation calculated     |              |                   |


## An attribute option has been updated (EE)

|                           | Product grid | Product edit form |
|---------------------------|--------------|-------------------|
| DQI cronjobs not executed |              |                   |
| DQI evaluations prepared  |              |                   |
| evaluation calculated     |              |                   |


