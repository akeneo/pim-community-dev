import {Attribute} from "./index";

export default interface Family {
  attributes: Attribute[];
  code: string;
  attribute_as_label: string;
  labels: {
    [locale: string]: string;
  };
  meta: {
    id: number;
  }
}

