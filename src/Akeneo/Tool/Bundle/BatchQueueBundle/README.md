# Jobs Queue with Symfony Messenger

## Presentation

The Symfony Messenger component helps applications send and receive messages to/from other applications or via message queues. In our case 
we send the message that tells a job should be launched. A worker receives the message from the queue asynchronously and
executes the job.  

## Transports

Symfony Messenger works with transports that are defined in the configuration. A transport defines where the message will be sent (be careful
if no transport is defined the job is executed synchronously). Different transport can be defined per environment. We can use
doctrine (= a mysql table) or Google Pub/Sub for instance.

## Jobs priority

Some job types can slow down other jobs, either because of their duration or because too jobs are created too often. To improve that
we create 3 job queues:
- 1 for jobs created through the UI (mass edit, mass delete), except imports/exports
- 1 for import/export
- 1 for data maintenance jobs

With this implementation we can imagine having a worker that handles only UI jobs and another worker for import jobs. A worker can
also handle messages from multiple queues, the first queue will be treated in priority, then the second and so on.

## Workflow

```text
                                                  Queue system
User                                             (GooglePubSub
bash command         SymfonyMessenger          / Doctrine / ...)    JobExecutionMessageHandler    Job Process
|                         |                           |                      |                        |
| Consume 1 message       |                           |                      |                        |
| ----------------------> |                           |                      |                        |
|                         |                           |                      |                        |
|                         | Is there a new message?   |                      |                        |
|                         | ------------------------> |                      |                        |
|                         |                           |                      |                        |
|                         |           Here is message |                      |                        |
|                         | <------------------------ |                      |                        |
|                         |                           |                      |                        |
|                         | Acknowledge message       |                      |                        |
|                         | (GooglePubSub only)       |                      |                        |
|                         | ------------------------> |                      |                        |
|                         |                           |                      |                        |
|                         | Spread message to handler |                      |                        |
|                         | --------------------------+--------------------> |                        |
|                         |                           |                      |                        |
|                         |                           |                      | Run job (new process)  |
|                         |                           |                      | ---------------------> |
|                         |                           |                      |                        |
|                         |                           |                      |                        |
|                         |                           |                      |  Update health check   |
|                         |                           |                      | every x seconds while  |
|                         |                           |                      |     job is running     |
|                         |                           |                      |                        |
|                         |                           |                      |                        |
|                         |                           |                      |         Job terminated |
|                         |                           |                      | <--------------------- |
|                         |                           |                      |                        |
|                         |                   Message handling is terminated |                        |
|                         | <-------------------------+--------------------- |                        |
|                         |                           |                      |                        |
|                         | Acknowledge message       |                      |                        |
|                         | (except GooglePubSub)     |                      |                        |
|                         | ------------------------> |                      |                        |
|                         |                           |                      |                        |
|  The message is handled |                           |                      |                        |
| <---------------------- |                           |                      |                        |
|                         |                           |                      |                        |
|                         |                           |                      |                        |
| Consume 1 message       |                           |                      |                        |
| ----------------------> |                           |                      |                        |
|                         |                           |                      |                        |
| ...                     |                           |                      |                        |
```
### Why do we ackowledge the message differently with Google PubSub?

Google PubSub has a maximum time to acknowledge a message. By default it's 10 seconds and it can be configured
with the `ackDeadlineSeconds` option (https://cloud.google.com/pubsub/docs/reference/rest/v1/projects.subscriptions/create).
The maximum we can configured is 10 minutes. If the the message is not acknowledged in time, the message can be delivered again.  
But it's not rare a job execution exceeds 10 minutes, and we don't want to receipt twice the same message. We thought about 2 solutions:
- When the job is too long we can extend the acknowledge time by a doing a request (https://cloud.google.com/pubsub/docs/reference/rest/v1/projects.subscriptions/modifyAckDeadline)
- As soon the message is receipted, we acknowledge the message without waiting the handler

The first solution is hard to implement, the only advantage is we can retry to execute a failing job. We are not sure we
want this feature, at least for the moment, so we took the second solution.
