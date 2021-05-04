import {ValidationError} from '@akeneo-pim-community/shared';

export default interface Remover<Identifier> {
  remove: (identifier: Identifier) => Promise<ValidationError[] | null>;
}
