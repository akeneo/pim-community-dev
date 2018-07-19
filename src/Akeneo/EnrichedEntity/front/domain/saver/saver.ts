import ValidationError from 'akeneoenrichedentity/domain/model/validation-error';

export default interface Saver<Entity> {
  save: (entity: Entity) => Promise<ValidationError[] | null>;
  create: (entity: Entity) => Promise<ValidationError[] | null>;
}
