export const hidrate = (hidrator: any) => (element: any) => {
  return hidrator(element);
};

export const toArray = <Element>(elements: any): Element[] => {
  if (null === elements) {
    return [];
  }

  return Object.keys(elements).map((key: string): Element => elements[key]);
};

export default (hidrator: any) => (elements: any) => {
  return toArray(elements).map(hidrate(hidrator));
};
