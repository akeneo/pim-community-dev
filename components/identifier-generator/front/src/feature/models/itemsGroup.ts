type ItemsGroup = {
  id: string;
  text: string;
  children: {
    id: string;
    text: string;
  }[];
};

export type {ItemsGroup};
