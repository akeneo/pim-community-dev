import {validateAgainstSchema} from '@akeneo-pim-community/shared';
import {Card} from 'akeneocommunicationchannel/models/card';
import schema from 'akeneocommunicationchannel/models/card.schema.json';

export const validateCard = (data: any): Card => validateAgainstSchema<Card>(data, schema);
