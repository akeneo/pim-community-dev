API simple search
====================

REST and SOAP APIs allow to search by all text fields in all entities.

Parameters for APIs requests:

 - **search** - search string
 - **offset** - integer value of offset
 - **max_results** - count  of result records in response

REST API url: http://domail.com/api/rest/latest/search

SOAP function name: search

REST API work with get request only. So, search request to the search must be like example:
http://domail.com/api/rest/latest/search?max_results=100&offset=0&search=search_string

Result
------

Request return array with next data:

 - **records_count** - the total number of results (without offset and max_results) parameters
 - **count** - count of records in current request
 - **data**- array with data.

 Data consists from next values:

 - **entity_name** - class name of entity
 - **record_id** - id of record from this entity
 - **record_string** - the title of this record