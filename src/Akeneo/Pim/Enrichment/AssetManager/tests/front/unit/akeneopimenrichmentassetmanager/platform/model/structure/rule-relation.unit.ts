import {getRulesForAttribute} from 'akeneoassetmanager/platform/model/structure/rule-relation';

test('It should get the rule codes for the impacted attribute', () => {
  const attributeCode = 'packshot';
  const rulesNumberByAttribute = {packshot: 2};
  expect(getRulesForAttribute(attributeCode, rulesNumberByAttribute)).toEqual(2);
});
