import ValidationError from 'akeneoassetmanager/domain/model/validation-error';

export default interface Remover<Identifier> {
  remove: (identifier: Identifier) => Promise<ValidationError[] | null>;
}
