import {ValidationError} from '@akeneo-pim-community/shared';

export default interface Saver<Entity> {
  save: (entity: Entity) => Promise<ValidationError[] | null>;
  create: (entity: Entity) => Promise<ValidationError[] | null>;
}
