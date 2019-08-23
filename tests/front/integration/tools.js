const endsWith = (string, target) => {
  return string.substr(-target.length) === target;
}

const matchAnswer = (requestUrl, answers) => {
  return Object.entries(answers).filter(([url]) => {
    if (
      url === requestUrl ||
      endsWith(requestUrl, url) ||
      requestUrl.includes(url)
    ) return true;
  }).map(([url, answer]) => answer);
}

const mockResponses = (page, answers) => {
    page.on('request', req => {
      const url = req.url();
      const answer = matchAnswer(url, answers);

      if (answer.length === 0) {
        return req.continue();
      }

      const { body, contentType } = answer[0]
      return req.respond({ body: JSON.stringify(body), contentType, status: 200 })
    });
}

module.exports = {
  mockResponses
}
