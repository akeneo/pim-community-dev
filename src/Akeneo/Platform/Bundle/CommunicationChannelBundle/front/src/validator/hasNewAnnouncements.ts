import {validateAgainstSchema} from '@akeneo-pim-community/legacy-bridge';
import {HasNewAnnouncements} from '../models/hasNewAnnouncements';
import schema from '../models/hasNewAnnouncements.schema.json';

export const validateHasNewAnnouncements = (data: any): HasNewAnnouncements =>
  validateAgainstSchema<HasNewAnnouncements>(data, schema);
