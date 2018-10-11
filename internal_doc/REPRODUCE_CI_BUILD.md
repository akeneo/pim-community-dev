# How to reproduce a build on the CI?

You have an error on the CI and you don't know why does it fails because it works on your computer?

You may want to reproduce the build!

## Kubernetes

First of all you have to set up the kubernetes client on your computer and gcloud on your computer

Install the cloud SDK: `https://cloud.google.com/sdk/docs/downloads-interactive`
Login with your google akeneo account: `$ gcloud auth login`
Configure gcloud with docker: `$ gcloud auth configure docker`
Install the kubernetes client: `$ gcloud components install kubectl`
Set the project (The one you want to reproduce the CI is named akecld-saas-training) : `$ gcloud config set project akecld-saas-training`
Get the kubernetes credentials : `$ gcloud container clusters get-credentials europe-west3-a --zone=europe-west3-a`

Here you should be able to do a `$ kubectl get all`

Create a kubernetes namespace for yourself: `$ kubectl create namespace nanou for example`

## Continuous Integration

Now that your are set up you can reproduce your build!

Let's take an example of this [Build](https://ci.akeneo.com/blue/organizations/jenkins/akeneo%2Fpim-enterprise-dev/detail/PR-4744/7/)

You have the tab "Artefacts" at the top right corner, click on it.

### Prepare the file

Download the file ending with `_job.yaml`  which contains the word selenium on it (because you will need behat most of the time) it should be the second file.

Then, you will have to edit this file (make sure it is the one containing selenium)

- First line, change the API from batch/v1 to v1
- Change the second line from kind: Job to kind: Pod
- Remove this block (usually line 9 to 13) and reindent the rest of the file to remove two indents:
```yaml
backoffLimit: 300
completions: 1358
parallelism: 136
template:
    spec:
```

- Remove the container "pubsub", usually this type of block (from `-name: pubsub` (included) to `volumes:` (excluded)) :
```yaml
  - name: pubsub
    image: eu.gcr.io/akeneo-ci/gcloud:1.1
    imagePullPolicy: Always
    resources:
      requests:
        cpu: 100m
        memory: 200Mi
      limits:
        memory: 300Mi
    command:
    - /bin/sh
    - -c
    args:
    - trap "touch /tmp/pod/main-terminated" EXIT; gcloud.phar pubsub:message:consume jenkins-slave-096rl-33qzj-subscription jenkins-slave-096rl-33qzj-results
    env:
    - name: REDIS_URI
      value: tcp://redis.jenkins:6379
    - name: POD_NAME
      valueFrom:
        fieldRef:
          fieldPath: metadata.name
    - name: NAMESPACE
      valueFrom:
        fieldRef:
          fieldPath: metadata.namespace
    volumeMounts:
    - name: tmp-pod
      mountPath: /tmp/pod
```
- Remove the `nodeSelector` and the `tolerations` blocks at the end of the file, usually this block:
```yaml
  nodeSelector:
    dedicated: consumer
  tolerations:
  - key: dedicated
    operator: Equal
    value: consumer
    effect: NoSchedule
```

### Deploy the cluster

Let's say your file is named `jenkins-slave-abcd.yaml`

Deploy it to the cluster: `$ kubectl apply -f jenkins-slave-abcd.yaml -n nanou` here the `-n nanou` is the namespace you previously created

To see if it is deployed you can type: `$ kubectl get pods -n nanou` to see if the containers are ready.

Once they are ready you can go on the container to launch what you want by doing this command `$ kubectl exec -it jenkins-slave-abcd -c main bash`

On it you can do the command that the CI launch they can be on the jenkinsfile or on the shared lib named "jenkins-k8s-utils" the user apache is `www-data` make sure you execute command with this one.

### Destroy the cluster

Now that you have finished you can destroy your instance (don't forget that instances can be expensive) `$ kubect delete -f jenkins-slave-abcd.yaml -n nanou`
