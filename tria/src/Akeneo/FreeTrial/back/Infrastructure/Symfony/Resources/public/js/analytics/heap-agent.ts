export interface HeapAgent {
  identify: (username: string) => void;
  addUserProperties: (properties: object) => void;
}

const getHeapAgent = async (): Promise<HeapAgent | null> => {
  // @ts-ignore
  return window.heap ? (window.heap as HeapAgent) : null;
};

export {getHeapAgent};
