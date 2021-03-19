# Jobs Queue with Symfony Messenger

## Presentation

The Symfony Messenger component helps applications send and receive messages to/from other applications or via message queues. In our case 
we send the message that tells a job should be launched. A worker receives the message from the queue asynchonously and
executes the job.  

## Transports

Symfony Messenger works with transports that are defined in the configuration. A transport defines where the message will be sent (be careful
if no transport is defined the job is executed synchronously). Different transport can be defined per environment. We can use
doctrine (= a mysql table) or Google Pub/Sub for instance.

## Jobs priority

Some job types can slow down other jobs, either because of their duration or because too jobs are created too often. To improve that
we create 4 job queues: 
- 1 for jobs created through the UI (mass edit, mass delete), except imports/exports
- 1 for import/export
- 1 for data maintenance jobs

With this implementation we can imagine having a worker that handles only UI jobs and another worker for import jobs. A worker can
also handle messages from multiple queues, the first queue will be treated in priority, then the second and so on.  
