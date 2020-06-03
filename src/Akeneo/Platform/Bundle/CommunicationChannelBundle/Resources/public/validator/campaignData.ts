import {validateAgainstSchema} from '@akeneo-pim-community/shared';
import {CampaignData} from 'akeneocommunicationchannel/models/campaignData';
import schema from 'akeneocommunicationchannel/models/campaignData.schema.json';

export const validateCampaignData = (data: any): CampaignData => validateAgainstSchema<CampaignData>(data, schema);
