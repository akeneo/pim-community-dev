function mockRequests(page, answers) {
  page.on('request', req => {
    const requestUrl = req.url();
    const answer = answers[requestUrl]
    if (!answer) return req.continue();
    const { body, contentType } = answer
    req.respond({ contentType, body });
  });
}


module.exports = { mockRequests };
