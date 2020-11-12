import {redirectToFilteredAttributeGrid} from "@akeneo-pim-community/data-quality-insights/src/infrastructure/AttributeGridRouter";

export const redirectToAttributeGridFilteredByKeyIndicator = (keyIndicator: string) => {
  redirectToFilteredAttributeGrid(`s[label]=-1&f[quality][value]=${keyIndicator}&t=attribute-grid`);
};
export const redirectToAttributeGridFilteredByFamilyAndKeyIndicator = (familyId: string, keyIndicator: string) => {
  redirectToFilteredAttributeGrid(`s[label]=-1&f[family][value][]=${familyId}&f[quality][value]=${keyIndicator}&t=attribute-grid`);

};
