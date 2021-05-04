export interface ValidationError {
  messageTemplate: string;
  parameters: {
    [key: string]: string | number;
  };
  message: string;
  propertyPath: string;
  invalidValue: any;
}
