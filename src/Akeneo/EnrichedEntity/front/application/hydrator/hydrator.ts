const hydrate = (hydrator: any) => (element: any) => {
  return hydrator(element);
};

const toArray = <Element>(elements: any): Element[] => {
  if (null === elements) {
    return [];
  }

  return Object.keys(elements).map((key: string): Element => elements[key]);
};

export default <Element>(hydrator: any) => (elements: any): Element[] => {
  return toArray(elements).map(hydrate(hydrator));
};
