const {PubSub} = require('@google-cloud/pubsub')
const {Firestore} = require('@google-cloud/firestore')

const firestore = new Firestore({
    projectId: process.env.fireStoreProjectId,
    timestampsInSnapshots: true
})

const pubSubClient = new PubSub({projectId: process.env.projectId});

const testTopicPermissions = async (topicId) => {
    const permissionsToTest = [
        'pubsub.topics.publish',
    ];

    const [permissions] = await pubSubClient
        .topic(topicId)
        .iam.testPermissions(permissionsToTest);

    return permissions
}

const publishScheduledJob = async (topicId, tenantId, jobCode, jobOptions) => {
    const dataBuffer = Buffer.from(JSON.stringify(
        {
            code: jobCode,
            options: jobOptions,
        }
    ))

    const message = {
        data: dataBuffer,
        attributes: {
            tenant_id: tenantId,
        },
    }

    try {
        const messageId = await pubSubClient.topic(topicId).publishMessage(message)
        console.debug('Message ID = %d', messageId)
        console.info(
            'Published a scheduled job "%s" with options "%s" for tenant ID %s',
            jobCode,
            JSON.stringify(jobOptions),
            tenantId
        )
    } catch (err) {
        console.error(
            'Error while publishing scheduled job "%s" with options "%s" for tenant ID %s',
            jobCode,
            JSON.stringify(jobOptions),
            tenantId
        )
        console.error(JSON.stringify(err))
    }
}

exports.publishCommand = async (req, res) => {
    const requestBody = typeof req.body === 'object' ? req.body : JSON.parse(req.body)
    const jobCode = requestBody.job_code
    const jobOptions = requestBody.job_options
    const topicId = requestBody.topic_id
    const permissions = await testTopicPermissions(topicId).catch(console.error);
    console.debug(permissions)

    console.info(
        'Start publishing scheduled job "%s" with options "%s"',
        jobCode,
        JSON.stringify(jobOptions)
    )

    try {
        const querySnapshot = await firestore.collection(process.env.tenantContext).get()
        for (const document of querySnapshot.docs) {
            console.debug('Publishing for tenant ID %s', document.id);
            await publishScheduledJob(topicId, document.id, jobCode, jobOptions)
        }
    } catch (err) {
        console.error(err);
        return res.status(404).send({
            error: 'Unable to fetch tenants from context store',
            err
        });
    }

    return res.status(200).send(`Schedule for job ${jobCode} finish with success`);
};
