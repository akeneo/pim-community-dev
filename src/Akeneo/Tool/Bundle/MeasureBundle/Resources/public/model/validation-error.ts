export interface ValidationError {
  messageTemplate: string;
  parameters: {
    [key: string]: string;
  };
  message: string;
  propertyPath: string;
  invalidValue: any;
}
