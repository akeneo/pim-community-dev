import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';

export default interface Saver<CreateEntity, EditEntity> {
  save: (entity: EditEntity) => Promise<ValidationError[] | null>;
  create: (entity: CreateEntity) => Promise<ValidationError[] | null>;
}
