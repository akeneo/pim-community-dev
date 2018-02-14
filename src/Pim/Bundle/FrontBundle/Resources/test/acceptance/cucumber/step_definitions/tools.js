const maxRandomLatency =
  undefined !== process.env.MAX_RANDOM_LATENCY_MS ? parseInt(process.env.MAX_RANDOM_LATENCY_MS) : 1000;

const answer = (methodToDelay, randomLatency = true, customMaxRandomLatency = maxRandomLatency) => {
  setTimeout(methodToDelay, (randomLatency ? Math.random() : 1) * customMaxRandomLatency);
};

const answerJson = (request, response, randomLatency = true, customMaxRandomLatency = maxRandomLatency) => {
  answer(() => request.respond(json(response)), randomLatency, customMaxRandomLatency);
};

const json = body => ({
  contentType: 'application/json',
  body: typeof body === 'string' ? body : JSON.stringify(body),
});

const spin = async function() {
  const [page, cb, ...rest] = arguments;

  return await page.waitFor(
    cb,
    {
      polling: 'mutation',
      timeout: 5000,
    },
    ...rest
  );
};

module.exports = {
  answer,
  answerJson,
  json,
  spin,
};
