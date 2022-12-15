import {LabelCollection} from './labelCollection';

type FamilyCode = string;

export type Family = {
  code: FamilyCode;
  labels: LabelCollection;
}
