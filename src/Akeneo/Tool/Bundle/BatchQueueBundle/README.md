# Jobs Queue with Symfony Messenger

## Presentation

The Symfony Messenger component helps applications send and receive messages to/from other applications or via message queues. In our case 
we send the message that tells a job should be launched. A worker receives the message from the queue asynchronously and
executes the job.  

## Transports

Symfony Messenger works with transports that are defined in the configuration. A transport defines where the message will be sent (be careful
if no transport is defined then the job is executed synchronously). Various transports can be defined per environment. We can use
doctrine (= a mysql table) or Google Pub/Sub for instance.

## Jobs priority

Some job types can slow down other jobs, either because of their duration or because too many jobs are created too often. To improve that
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
bash command        Symfony Messenger          / Doctrine / ...)    JobExecutionMessageHandler     Job Process
|                         |                           |                      |                          |
| Consume 1 message       |                           |                      |                          |
| ----------------------> |                           |                      |                          |
|                         |                           |                      |                          |
|                         | Is there a new message?   |                      |                          |
|                         | ------------------------> |                      |                          |
|                         |                           |                      |                          |
|                         |           Here is message |                      |                          |
|                         | <------------------------ |                      |                          |
|                         |                           |                      |                          |
|                         | Acknowledge message       |                      |                          |
|                         | (GooglePubSub only)       |                      |                          |
|                         | ------------------------> |                      |                          |
|                         |                           |                      |                          |
|                         | Spread message to handler |                      |                          |
|                         | --------------------------+--------------------> |                          |
|                         |                           |                      |                          |
|                         |                           |                      | Run job (in sub-process) |
|                         |                           |                      | -----------------------> |
|                         |                           |                      |                          |
|                         |                           |                      |                          |
|                         |                           |                      |   Update health check    |
|                         |                           |                      |  every x seconds while   |
|                         |                           |                      |      job is running      |
|                         |                           |                      |                          |
|                         |                           |                      |                          |
|                         |                           |                      |           Job terminated |
|                         |                           |                      | <----------------------- |
|                         |                           |                      |                          |
|                         |                   Message handling is terminated |                          |
|                         | <-------------------------+--------------------- |                          |
|                         |                           |                      |                          |
|                         | Acknowledge message       |                      |                          |
|                         | (except GooglePubSub)     |                      |                          |
|                         | ------------------------> |                      |                          |
|                         |                           |                      |                          |
|  The message is handled |                           |                      |                          |
| <---------------------- |                           |                      |                          |
|                         |                           |                      |                          |
|                         |                           |                      |                          |
| Consume 1 message       |                           |                      |                          |
| ----------------------> |                           |                      |                          |
|                         |                           |                      |                          |
| ...                     |                           |                      |                          |
```
### Why do we ackowledge the message differently with Google PubSub?

Google PubSub has a maximum time to acknowledge a message. By default it's 10 seconds and it can be configured
with the `ackDeadlineSeconds` option (https://cloud.google.com/pubsub/docs/reference/rest/v1/projects.subscriptions/create).
The maximum that can be configured is 10 minutes. If the the message is not acknowledged in that interval, it can be delivered again.  
But it's not rare a job execution exceeds 10 minutes, and we don't want to receive the same message twice. We thought about 2 solutions:
- When the job is too long we can extend the acknowledge time by a doing a request (https://cloud.google.com/pubsub/docs/reference/rest/v1/projects.subscriptions/modifyAckDeadline)
- As soon the message is received, we acknowledge the message without waiting the handler

The first solution is hard to implement, the only advantage is we can retry to execute a failing job. We are not sure we
want this feature, at least for the moment, so we took the second solution.

### Why do we have an healthcheck date updated regularly?

#### History

Initially, the queue was a homemade queue implemented with Mysql.  
A daemon (never-ending PHP command) consumed one message at a time. A message corresponds to an Akeneo job to launch. This job is executed in a sub-process. It increase the stability of the daemon because all it pushed all the complexity of a job (ORM, ES, potential memory leaks, etc) inside a sub-process.

- If the daemon fails, it's not an issue: the job updates its status by itself (error, stopped, etc).
- If the job fails, it's not an issue: the daemon can detect that and changes the status of the job. This way, it does not stay as started, even if there was an unexpected and unrecoverable issue (such as memory leak).

But there is one missing case. Imagine the daemon is failing for any reason, and after that the job too. There is no safeguard to change the job state.

And here come the healthcheck datetime. Updated every X seconds by the daemon, the health check a job allows to say "the job is still running, everything is fine".

If a job has a status at STARTED and the healthcheck is not updated, it means that:
- the daemon failed and can't change the healthcheck
- the job itself can't change its own status: it probably failed also

So, in this case, despite the status "STARTED" in database, it's considered as failed in the UI for the final user.

#### Why is it useless when running with docker?

Without docker, if the daemon is killed or fails, the jobs (in sub-processes) is **not** killed.

With docker, if the daemon is killed or fails, as it's the main process, it will stop the contained (and so the sub-process).
In that case (pretty common case), the healthcheck is useless.
