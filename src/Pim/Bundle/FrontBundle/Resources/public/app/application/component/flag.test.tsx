import Flag from './flag';
import * as React from 'react';
import {shallow} from 'enzyme';
import {createLocale} from 'pimfront/app/domain/model/locale';

describe('>>>COMPONENT --- flag', () => {
  test('Flag displays the good language', () => {
    const locale = createLocale({code: 'en_US'});
    const flag = shallow(<Flag locale={locale} displayLanguage />);

    expect(flag.text().trim()).toEqual('en');
    expect(flag.find('.flag').length).toBe(1);
  });

  test('Flag displays without language', () => {
    const locale = createLocale({code: 'en_US'});
    const flag = shallow(<Flag locale={locale} displayLanguage={false} />);

    expect(flag.text().trim()).toBe('');
    expect(flag.find('.flag').length).toBe(1);
  });
});
