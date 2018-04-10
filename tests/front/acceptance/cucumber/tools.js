const random = process.env.RANDOM_LATENCY || true;
const maxRandomLatency = undefined !== process.env.MAX_RANDOM_LATENCY_MS ? process.env.MAX_RANDOM_LATENCY_MS : 1000;

const answer = (methodToDelay, randomLatency = random, customMaxRandomLatency = maxRandomLatency) => {
    setTimeout(methodToDelay, (randomLatency ? Math.random() : 1) * parseInt(customMaxRandomLatency));
};

const answerJson = (request, response, randomLatency = random, customMaxRandomLatency = maxRandomLatency) => {
    answer(() => request.respond(json(response)), randomLatency, parseInt(customMaxRandomLatency));
};

const json = body => ({
    contentType: 'application/json',
    body: typeof body === 'string' ? body : JSON.stringify(body)
});

const csvToArray = (csv, separator = ',') => {
    return csv.split(separator).map(value => value.trim());
};

module.exports = {
    answer,
    answerJson,
    json,
    csvToArray
};
