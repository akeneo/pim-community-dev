const timeout = 10000;

describe('Pimenterpriseteamworkassistant > remover > project', () => {
  let page = global.__PAGE__;

  beforeEach(async () => {
    await page.reload();
  }, timeout);

  it('It removes a project', async () => {
    page.on('request', interceptedRequest => {
      if (interceptedRequest.url().includes('projects/new_collection') && 'DELETE' === interceptedRequest.method()) {
        interceptedRequest.respond({});
      }
    });

    const response = await page.evaluate(async () => {
      const remover = require('teamwork-assistant/remover/project');
      const project = {code: 'new_collection'};

      return await remover.remove(project);
    });

    expect(response).toEqual('');
  });
});
