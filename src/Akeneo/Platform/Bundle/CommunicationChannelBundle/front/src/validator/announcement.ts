import {validateAgainstSchema} from '@akeneo-pim-community/legacy-bridge';
import {Announcement} from '../models/announcement';
import schema from '../models/announcement.schema.json';

export const validateAnnouncement = (data: any): Announcement => validateAgainstSchema<Announcement>(data, schema);
