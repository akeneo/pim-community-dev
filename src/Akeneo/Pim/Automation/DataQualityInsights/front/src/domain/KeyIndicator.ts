type KeyIndicator = {
  ratio: number;
  total: number;
}

type keyIndicatorMap = {
   [keyIndicator: string]: KeyIndicator;
};

type Tip = {
  message: string;
  link?: string;
};

type Tips = {
  [step: string]: Tip[];
};

export {KeyIndicator, keyIndicatorMap, Tip, Tips};
