API advanced search
====================

REST and SOAP APIs allow to create advanced search queries.

Parameters for APIs requests:

 - **query** - search string

REST API url: http://domail.com/api/rest/latest/search/advanced

SOAP function name: advancedSearch

REST API work with get request only.

Result
====================

Request return array with next data:

 - **records_count** - the total number of results (without offset and max_results) parameters
 - **count** - count of records in current request
 - **data**- array with data.

 Data consists from next values:

 - **entity_name** - class name of entity
 - **record_id** - id of record from this entity
 - **record_string** - the title of this record

 Query language
====================

Keywords
--------

### from

List of entity aliases to search from. It can be one alias or group. Examples:

    from one_alias
    from (first_alias, second_alias)

### where

Auxiliary keyword for visual separation 'from' block from search parameters

### and, or

Used to combine multiple clauses, allowing you to refine your search. Syntax: 

	and field_type field_name operator value
	or field_type field_name operator value

If field type not set, when text field type whill be used.

### offset

Allow to set offset of first result.

### max_results

Set results count for the query.

### order_by

Allow to set results order. Syntax:

	order_by field_type field_name direction

If field type was not set, then text field will be assigned. Direction - asc, desc. If direction is not assigned then whill be asc direction used.

Field types
-----------

Some keywords (and, or, order_by) contain field type parameter in syntax. By default, if type is not set, it whill be used text type. Supported field types:

**text**

**integer**

**decimal**

**datetime**


Operators
-----------

Different field types support different operators in 'where' block.

### ~ (CONTAINS)

**Work only with string fields**

Operator '~' is used for set text field value. If search value is string, it must be quoted. Examples:
	
	name ~ value
	name ~ "string value"


### !~ (NOT CONTAINS)

**Work only with string fields**

Operator '!~' is used for search strings without value. If search value is string, it must be quoted. Examples:
	
	name !~ value
	name !~ "string value"

### = (EQUALS)

**Does not work with string fields**

Operator '=' is used for search records where field matches the specified value. Examples:
	
	integer count = 100
	decimal price = 12.5
	datetime create_date = "2013-01-01 00:00:00"

### != (NOT EQUALS)

**Does not work with string fields**

Operator '!=' is used for search records where field does not matches the specified value. Examples:
	
	integer count != 5
	decimal price != 45
	datetime create_date != "2012-01-01 00:00:00"

### >, <, <=, >= 

**Does not work with string fields**

Operators is used to search for the records that have the specified field must be greater,
less,  than, less than equals, or greater than equals of the specified value. Examples:
	
	integer count >= 5
	decimal price < 45
	datetime create_date > "2012-01-01 00:00:00"

### in

**Does not work with string fields**

Operator 'in' is used for search records where field in the specified set of data. Examples:
	
	integer count in (5, 10, 15, 20)
	decimal price in (12.2, 55.25)


### !in

**Does not work with string fields**

Operator '!in' is used for search records where field not in the specified set of data. Examples:
	
	integer count !in (1, 3, 5)
	decimal price !in (2.1, 55, 45.4)


Query examples
--------------

	from demo_product where name ~ samsung and double price > 100

Search by demo products where name contains string 'samsung' and where price greater than 100.

	integer count != 10

Search by all entities where integer field count not equals 10.

	all_text !~ "test string"

Search by all entities where text field all_text not contains string 'test string'

	from (demo_products, demo_categories) where description ~ test order_by name offset 5 max_results 10

Select 10 results from demo_products, demo_categories entities where text field description contains 'test', order asc by text field name and offset first result to 5.
