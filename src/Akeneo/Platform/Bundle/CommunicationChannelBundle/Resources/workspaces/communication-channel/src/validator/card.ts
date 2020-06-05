import {validateAgainstSchema} from '@akeneo-pim-community/shared';
import {Card} from './../models/card';
import schema from './../models/card.schema.json';

export const validateCard = (data: any): Card => validateAgainstSchema<Card>(data, schema);
