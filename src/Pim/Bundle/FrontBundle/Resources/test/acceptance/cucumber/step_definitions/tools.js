const maxRandomLatency = undefined !== process.env.MAX_RANDOM_LATENCY_MS ?
  parseInt(process.env.MAX_RANDOM_LATENCY_MS) :
  1000;

module.exports = {
  random: (methodToDelay, customMaxRandomLatency = maxRandomLatency) => {
    setTimeout(methodToDelay, Math.random() * maxRandomLatency);
  },
  json: (body) => ({
    contentType: 'application/json',
    body
  }),
  spin: async function () {
    const [page, cb, ...rest] = arguments;

    return await page.waitFor(cb, {
      polling: 'mutation',
      timeout: 5000
    }, ...rest);
  }
}
