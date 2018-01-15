export const hidrate = (hidrator: any) => (element: any) => {
  return hidrator(element);
};

export const toArray = <Element>(elements: any): Element[] => {
  if (null === elements) {
    return [];
  }

  return Object.keys(elements).map((key: string): Element => elements[key]);
};

export default <Element>(hidrator: any) => (elements: any): Element[] => {
  return toArray(elements).map(hidrate(hidrator));
};
