import {validateAgainstSchema} from '@akeneo-pim-community/shared';
import {CampaignData} from './../models/campaignData';
import schema from './../models/campaignData.schema.json';

export const validateCampaignData = (data: any): CampaignData => validateAgainstSchema<CampaignData>(data, schema);
