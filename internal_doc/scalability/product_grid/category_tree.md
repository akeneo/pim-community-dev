# Category tree 

## Performance issue

When displaying the category tree in the product grid, the number of products in each category should be displayed.
Until the 2.3, with large catalogs, we can have performance issues when:

- the number of categories is high
- the number of products is high

We avoid to do it asynchronously, because the count is dependent of the permission of the user in EE.

## Objectives

Here the initial objectives to display the category tree in the product grid:

- should be displayed in less than 2 seconds
- support at least 5000 categories
- in 3 category trees (1666 categories/tree)
- support at least 130 000 products 
- should support 50 users concurrently

Note: 50 users on the PIM des not mean 50 concurrent requests. Users are not active at the same time.

## Axes

We can do compromise on axes.
The goal is to know what are the limit of our axes.

It is allowed to put a limit on an axis, if it's possible to reach this limit.

Here the list of axes:

- number of sub-categories in a category

## How it works in 2.3?

![Alt text](images/category_tree.png?raw=true "Category tree")

### Count in the category tree

When displaying the category tree "master" in the datagrid, it will:

- count the number of products for all the category tree: "master" and "minor" in this example
- count the number of products in category "A" and in category "B"

When the option "including sub-categories" is checked, a product is considered in a category if it belongs to at least one viewable category in the subtree of this category.

For example, products viewable in "master" are "1", "2", "3", "5", "7", for a total of 5 products.

Products viewable in "minor" are "2" and "6", for a total of 2 products.

Do note that a category can be visible but not its parent. This is important.

### Count in the product datagrid

When counting the products in the datagrid, the rule is not the same.
For the datagrid, a product is returned if it belongs to at least one viewable category (but not only in the subtree of the selected category).

For example, in the datagrid, products viewable in "master" are "1", "2", "3", "5", "7" = 5 products. Same as for the count.
Products viewable in "minor" are "2", "6", "7" = 3 products! Not same as for the count. 

So, the count in the category tree is different from the count displayed in the datagrid because it does not apply the same rule for the permission.

Product Owners are aware of this behavior.
The correct solution would be to only return products visible in the filtered category (like for the category tree count).

## Why it does not scale?

When counting on "master" tree, it counts on "A" and "B".

- first, it gets all categories viewable in "A" with an SQL request.
- then, it gets the count in ES for the category "A" with all viewable categories returned by previous request (potentially several thousands categories)
- then, it gets all categories viewable in "B" with an SQL request.
- then, it gets the count in ES for the category "B" with all viewable categories returned by previous request (potentially several thousands categories)

In this example, it's ok because you don't have a lot of categories in "master".
When you have a lot of categories in "master", it slows down the whole request.

For example, With 5000 categories, 10 000 products, 890 categories at the first level of "master", it takes 7 seconds on a Macbook.
Each SQL request is pretty fast, but there is 890 SQL requests. 
Each ES is fast. But there is 890 ES requests.

And there are also 890 requests for the traduction as well, due to the lazy loading.

So, 4470 SQL requests, and 890 ES requests.

## What are the solutions?

### First solution: less ES and SQL requests

#### Explanation

The solution is to get all the visible categories for each node in one single query. 
Then, we request ES with one query the count for all subchild, using the multi-search API.

Pros:
- less costly implementation
- same behavior as before

Drawback:
- dependent of the total number of categories
- still have big queries in ES, by requesting with all the viewable categories


#### SQL optimization

After optimization of the SQL request, I got pretty decent results.

```
    CREATE INDEX test_category_access ON pimee_security_product_category_access (view_items, user_group_id);
    CREATE INDEX test_category ON pim_catalog_category (lft, rgt);

    SELECT c.child_code, GROUP_CONCAT(c.subchild_code) FROM (
        SELECT child.code as child_code,  subchild.code as subchild_code
        FROM pim_catalog_category parent
        JOIN pim_catalog_category child on child.parent_id = parent.id
        JOIN pim_catalog_category subchild on subchild.lft BETWEEN child.lft AND child.rgt AND subchild.root = child.root
        JOIN pimee_security_product_category_access ca on ca.category_id = subchild.id 
        WHERE parent.code = 'master'
        AND ca.user_group_id IN (1, 4)
        AND ca.view_items = 1 ) as c
    GROUP BY c.child_code;
```

It takes 50 ms to return 890 categories, with all their viewable node associated (in a list separated by "," thanks to GROUP_CONCAT operator).

When testing it concurrently on a server with 12 cores, it takes 84ms to execute a request with a concurrency of 12 simultaneous requests:

```
mysqlslap --create-schema akeneo_pim --concurrency=12 --iterations=20 -u akeneo_pim -pakeneo_pim --query=/var/tmp/query.sql
```

It takes 152ms to execute a request with a concurrency of 24 simultaneous requests:

```
mysqlslap --create-schema akeneo_pim --concurrency=24 --iterations=20 -u akeneo_pim -pakeneo_pim --query=/var/tmp/query.sql
```

Almost the double: it's normal. We have 12 cores. Mysql execute each request on a core. So, it does not really scale more than 12 concurrent requests without impacting the response time.

#### ES optimization

We do a multi-search:
```
    { "index" : "akeneo_pim_product"}
    {"size":0, "query" : {"constant_score": {"filter": {"terms": { "categories" : ["0010692373450"]}}}}}
    { "index" : "akeneo_pim_product"}
    {"size":0, "query" : {"constant_score": {"filter": {"terms": { "categories" : ["0028639452243"]}}}}}
    { "index" : "akeneo_pim_product"}
```


For the ES part, the response time is 200ms for one single request. 

Let's test the scalability with 10 concurrent requests:
```
ab -p /var/tmp/categories.sql -H 'cache-control: no-cache' -T application/x-ndjson -c 12 -n 240 "http://127.0.0.1:9200/akeneo_pim_product/pim_catalog_product/_msearch"
```

It takes 1s to get the response.

It's quite a lot. It's not a big deal though, as ES can scale as much as we want (not the case of Mysql without using Mysql cluster). If we have a lot of users, we should have more ES instances.

#### Go further: ES query response time

The main drawback of the solution is that we need to pass all the categories of the subtree when counting the number of products in a node.
So, we has to test the limit of ES when processing queries with a lot of `IN (category_1, category_2)`.

We tested queries in ES with big `IN (category_1, category_2)` until 1 million categories. There are 10 millions products (actually very small documents containing only categories).
Here are the results.

![Alt text](images/10_millions_products.png?raw=true "Number max categories in clause until 1 million categories")

We did the same but with more realistics numbers, until 50 000 categories.

![Alt text](images/10_millions_products_detailed.png?raw=true "Number max categories in clause until 50000 categories")

Also, we did a test to know the response time, given 5000, 20 000 and 30 000 categories.
Until 50 millions of products.

![](images/5000_categories.png?raw=true "Response time/number of products")

![Alt text](images/10000_categories.png?raw=true "Response time/number of products")

![Alt text](images/30000_categories.png?raw=true "Response time/number of products")

#### Conclusion

The results are conclusive. 
When adding the time taken by the SQL request to the time taken by ES request, we have most of the time the results in less than 500ms.

Also, do note that 890 direct children in one category is very probably an edge case. 
So, for most of the cases, we should have far better results. Meaning that we could have a response time in less than 500 ms very probably.

### Second solution: request all products in a node thanks to ES indexation feature

#### Explanation

With ES, it's possible to request all data in a node by configuring the indexation.
The idea is to index products with the full path of the category "master/A/B". 

Then, ES is able to automatically split it and index the product as being part of "master", "master/A", "master/A/B".
It is very easy to search how many product there are in each category:
```
{ "index" : "poc_categories"}
{"size":0, "query" : {"constant_score": {"filter": {"term": { "categories" : "node_2/node_10/node_1"}}}}}
{ "index" : "poc_categories"}
{"size":0, "query" : {"constant_score": {"filter": {"term": { "categories" : "node_2/node_9/node_4"}}}}}
```

You can filter with the permissions by excluding some paths as well.
But it can be very complex.

_Do note that a category can be visible but not its parent._

Applying different rights on children is useful when you want to give edit access to a subtree, and only view access to the parent. 

But in the PIM, it is also possible to give view access to a subtree, but not no view access to the parent.
There is no business need for it, but it works this way for now.

This functional behavior prevents us to to ignore a part of the tree in an easy way. 


Pros:
- no big requests in Mysql to get all children 
- no big requests with `IN (category_1, category_2)` in ES
- scale with several millions of products without any problem
- permission are easy to apply

Cons:
- you have to re-index products when moving a category
- you have to handle categories differently in Mysql and in ES
- it is costly to implement
- functional

We tested with very simple document, and the count is very fast with 10 millions of products (200ms with multi-search on 1000 categories). 
We could completely ignore a part of tree that is no authorized quite easily in the request.


