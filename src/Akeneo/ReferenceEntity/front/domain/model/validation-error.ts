export default interface ValidationError {
  messageTemplate: string;
  parameters: {
    [key: string]: string;
  };
  message: string;
  propertyPath: string;
  invalidValue: any;

  normalize(): NormalizedValidationError;
}

export type NormalizedValidationError = {
  messageTemplate: string;
  parameters: {
    [key: string]: string;
  };
  message: string;
  propertyPath: string;
  invalidValue: any;
}

class ConcreteValidationError implements ValidationError {
  readonly messageTemplate: string;
  readonly parameters: {
    [key: string]: string;
  };
  readonly message: string;
  readonly propertyPath: string;
  readonly invalidValue: any;

  private constructor(error: ValidationError) {
    this.messageTemplate = error.messageTemplate;
    this.parameters = error.parameters;
    this.message = error.message;
    this.propertyPath = error.propertyPath;
    this.invalidValue = error.invalidValue;
  }

  public normalize() {
    return {
      messageTemplate: this.messageTemplate,
      parameters: this.parameters,
      message: this.message,
      propertyPath: this.propertyPath,
      invalidValue: this.invalidValue,
    }
  }

  static fromError(error: ValidationError) {
    return new ConcreteValidationError(error);
  }
}

export const createValidationError = ConcreteValidationError.fromError;
