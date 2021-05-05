import {ValidationError} from '@akeneo-pim-community/shared';

export default interface Saver<CreateEntity, EditEntity> {
  save: (entity: EditEntity) => Promise<ValidationError[] | null>;
  create: (entity: CreateEntity) => Promise<ValidationError[] | null>;
}
