import {
  isSmartAttribute,
  getRulesForAttribute,
} from 'akeneopimenrichmentassetmanager/platform/model/structure/rule-relation';

test('It should check if the attribute can be updated by a product rule (SmartAttribute)', () => {
  const attributeCode = 'packshot';
  const ruleRelations = [
    {
      attribute: 'notices',
      rule: 'set_notices',
    },
    {
      attribute: 'packshot',
      rule: 'set_packshot_en_US',
    },
  ];
  expect(isSmartAttribute(attributeCode, ruleRelations)).toEqual(true);
  expect(isSmartAttribute('wrong_attribute', ruleRelations)).toEqual(false);
});

test('It should get the rule codes for the impacted attribute', () => {
  const attributeCode = 'packshot';
  const ruleRelations = [
    {
      attribute: 'notices',
      rule: 'set_notices',
    },
    {
      attribute: 'packshot',
      rule: 'set_packshot_en_US',
    },
  ];
  expect(getRulesForAttribute(attributeCode, ruleRelations)).toEqual(['set_packshot_en_US']);
});
