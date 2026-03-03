import {FamilyCode} from './family';
import {AttributeCode} from './attribute';

type NomenclatureLineEditProps = {
  code: FamilyCode | AttributeCode;
  label: string;
  value: string;
};

export type {NomenclatureLineEditProps};
