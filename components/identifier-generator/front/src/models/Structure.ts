import {AutoNumber} from "./Properties/AutoNumber";
import {FreeText} from "./Properties/FreeText";

type Property = AutoNumber | FreeText;

type Structure = {
  properties: Property[];
}

export type {Structure};
