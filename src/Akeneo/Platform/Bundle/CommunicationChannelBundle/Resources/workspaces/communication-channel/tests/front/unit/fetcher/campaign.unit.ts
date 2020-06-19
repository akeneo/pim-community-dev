import {CampaignFetcher} from '@akeneo-pim-community/communication-channel/src/fetcher/campaign';

const DataCollector = require('pim/data-collector');

jest.mock('pim/data-collector');
console.error = jest.fn();

afterEach(() => {
  DataCollector.collect.mockClear();
  CampaignFetcher.campaign = null;
});

test('It gets the campaign from the Data Collector', async () => {
  const expectedData = {pim_version: '4.0', pim_edition: 'CE'};
  const campaign = 'CE4.0';
  DataCollector.collect.mockReturnValueOnce(expectedData);

  const response = await CampaignFetcher.fetch();

  expect(response).toEqual(campaign);
  expect(DataCollector.collect).toHaveBeenCalledWith('pim_analytics_data_collect');
});

test('It gets the campaign for the Serenity version', async () => {
  const expectedData = {pim_version: '1384859', pim_edition: 'Serenity'};
  const campaign = 'Serenity';
  DataCollector.collect.mockReturnValueOnce(expectedData);

  const response = await CampaignFetcher.fetch();

  expect(response).toEqual(campaign);
  expect(DataCollector.collect).toHaveBeenCalledWith('pim_analytics_data_collect');
});

test('It does not call twice the DataCollector when it already fetch the campaign', async () => {
  const expectedData = {pim_version: '1384859', pim_edition: 'Serenity'};
  DataCollector.collect.mockReturnValueOnce(expectedData);

  await CampaignFetcher.fetch();

  await CampaignFetcher.fetch();

  expect(DataCollector.collect).toHaveBeenCalledTimes(1);
});

test('It can validate the campaign data needed from the data collector', async () => {
  const expectedData = {pim_version: '1384859', pim_edition: true};
  DataCollector.collect.mockReturnValueOnce(expectedData);

  await expect(CampaignFetcher.fetch()).rejects.toThrowError(Error);
  expect(console.error).toHaveBeenCalledTimes(1);
});
