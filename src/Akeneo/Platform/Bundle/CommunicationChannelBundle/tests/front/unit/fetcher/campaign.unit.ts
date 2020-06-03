import Campaign from 'akeneocommunicationchannel/fetcher/campaign';

const DataCollector = require('pim/data-collector');

jest.mock('pim/data-collector');

afterEach(() => {
  DataCollector.collect.mockClear();
  Campaign.campaign = null;
});

test('It gets the campaign from the Data Collector', async () => {
  const expectedData = {pim_version: '4.0', pim_edition: 'CE'};
  const campaign = 'CE4.0';
  DataCollector.collect.mockReturnValueOnce(expectedData);

  const response = await Campaign.fetch();

  expect(response).toEqual(campaign);
  expect(DataCollector.collect).toHaveBeenCalledWith('pim_analytics_data_collect');
});

test('It gets the campaign for the Serenity version', async () => {
  const expectedData = {pim_version: '1384859', pim_edition: 'Serenity'};
  const campaign = 'Serenity';
  DataCollector.collect.mockReturnValueOnce(expectedData);

  const response = await Campaign.fetch();

  expect(response).toEqual(campaign);
  expect(DataCollector.collect).toHaveBeenCalledWith('pim_analytics_data_collect');
});

test('It does not call twice the DataCollector when it already fetch the campaign', async () => {
  const expectedData = {pim_version: '1384859', pim_edition: 'Serenity'};
  DataCollector.collect.mockReturnValueOnce(expectedData);

  await Campaign.fetch();

  await Campaign.fetch();

  expect(DataCollector.collect).toHaveBeenCalledTimes(1);
});
