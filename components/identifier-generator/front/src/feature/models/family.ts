import {LabelCollection} from './labelCollection';

export type FamilyCode = string;

export type Family = {
  code: FamilyCode;
  labels: LabelCollection;
};
