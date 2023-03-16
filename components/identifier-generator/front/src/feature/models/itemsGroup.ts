import {AttributeType} from './attribute';

type ItemsGroup = {
  id: string;
  text: string;
  children: {
    id: string;
    text: string;
    type?: AttributeType;
  }[];
};

export type {ItemsGroup};
