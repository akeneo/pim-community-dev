export class InvalidRawObjectError extends Error {
  constructor(message: string, expectedKeys: string[], invalidKeys: string[], malformedObject: any) {
    super(`${message}
Expected keys are: ${expectedKeys.join(', ')}
Invalid keys: ${invalidKeys.join(', ')}
Received object: ${JSON.stringify(malformedObject)}`);
  }
}

export const validateKeys = (object: any, keys: string[], message: string) => {
  const invalidKeys = keys.filter((key: string) => undefined === object[key]);

  if (0 !== invalidKeys.length) {
    throw new InvalidRawObjectError(message, keys, invalidKeys, object);
  }
};

const hydrate = (hydrator: any) => (element: any, context?: any) => {
  return hydrator(element, context);
};

const toArray = <Element>(elements: any): Element[] => {
  if (null === elements) {
    return [];
  }

  return Object.keys(elements).map((key: string): Element => elements[key]);
};

export default <Element>(hydrator: any) => (elements: any, context?: any): Element[] => {
  return toArray(elements).map((element: Element) => hydrate(hydrator)(element, context));
};
