import Flag from 'akeneoreferenceentity/tools/component/flag';
import {denormalizeLocale} from 'akeneoreferenceentity/domain/model/locale';
import React from 'react';
import {mount} from 'enzyme';

describe('>>>COMPONENT --- flag', () => {
  test('It displays the right flag as icon', () => {
    const enUS = denormalizeLocale({
      code: 'en_US',
      label: 'English (United States)',
      region: 'United States',
      language: 'English',
    });
    const usFlag = mount(<Flag locale={enUS} displayLanguage={false} className={''} />);
    expect(
      usFlag
        .find('i')
        .first()
        .hasClass('flag-us')
    ).toBe(true);

    const serbianCyrlRS = denormalizeLocale({
      code: 'sr_Cyrl_RS',
      label: 'Serbian (Cyrillic, Serbia)',
      region: 'Serbia',
      language: 'Serbian',
    });
    const serbianFlag = mount(<Flag locale={serbianCyrlRS} displayLanguage={false} className={''} />);
    expect(
      serbianFlag
        .find('i')
        .first()
        .hasClass('flag-rs')
    ).toBe(true);
  });
  test('It displays nothing if the locale is not defined', () => {
    const blankFlag = mount(<Flag displayLanguage={false} className={''} />);
    expect(blankFlag.find('i').get(0)).toBeFalsy();
  });
  test('It displays a flag if the className is not defined', () => {
    const enUS = denormalizeLocale({
      code: 'en_US',
      label: 'English (United States)',
      region: 'United States',
      language: 'English',
    });
    const usFlag = mount(<Flag locale={enUS} displayLanguage={false} />);
    expect(
      usFlag
        .find('i')
        .first()
        .hasClass('flag-us')
    ).toBe(true);
  });
  test('It displays the language of the flag', () => {
    const enUS = denormalizeLocale({
      code: 'en_US',
      label: 'English (United States)',
      region: 'United States',
      language: 'English',
    });
    const usFlag = mount(<Flag locale={enUS} displayLanguage={true} />);
    expect(usFlag.exists('.language')).toBe(true);
  });
});
