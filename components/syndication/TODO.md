# Next

- Fix fetcher provider
- Cleanup walmart requirements
- Add main category to requirements
- fix unit tests
- Add filters to platform configuration
- Fix json export to iterate over every families

# Think about

- Platform rate limits
- Manage default values in configuration
- Trigger processing based on file dropped on storage
- Expose media to process
- What to do in case of requirement change
- To add to job configuration
  - configuration of frequency
  - revert filter and data mapping
  - filter family
- Product in multiple channels (duplicate products in filters)
- target multipls categories in amazon: how do you set multiple categories in amazon (merge products)
- Generated product data (stock, price, etc)
- To manage long running processes: we could ask the PIM for identifiers of products to export by calling only elastic search

# Long running proccesses

## Using search after and stateless

- We start by calling the middleware platform with the first search after.
- The middleware will process and store the current search after.
- Before the serverless function is timedout, we call itself with the new search after
- The process can restart

What happens in case of failure? As we store the current search after, we can start from when it stopped

## Using product id collection

- We start by calling a private API to fetch all the product ids to export (it need to take less than 9 minutes)
- We then creates batches of 1000 products id that we add to a pubsub list or something
- the list is consummed by the serverless function and called by the pubsub system
