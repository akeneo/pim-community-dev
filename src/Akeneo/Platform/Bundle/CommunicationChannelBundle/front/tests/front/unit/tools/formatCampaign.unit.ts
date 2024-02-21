import {formatCampaign} from '@akeneo-pim-community/communication-channel/src/tools/formatCampaign';

test('It formats campaign for a non serenity version', () => {
  const expectedCampaign = 'CE4.0';

  const campaign = formatCampaign('CE', '4.0');

  expect(campaign).toEqual(expectedCampaign);
});

test('It formats campaign for a version', () => {
  const expectedCampaign = 'Serenity';

  const campaign = formatCampaign('Serenity', '12939394');

  expect(campaign).toEqual(expectedCampaign);
});
