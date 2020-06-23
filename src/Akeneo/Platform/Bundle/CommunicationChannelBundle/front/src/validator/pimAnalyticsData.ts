import {validateAgainstSchema} from '@akeneo-pim-community/shared';
import {PimAnalyticsData} from '../models/pimAnalyticsData';
import schema from '../models/pimAnalyticsData.schema.json';

export const validatePimAnalyticsData = (data: any): PimAnalyticsData => {
  return validateAgainstSchema<PimAnalyticsData>(data, schema);
};
