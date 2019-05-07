import Flag from 'akeneoreferenceentity/tools/component/flag';
import {denormalizeLocale} from 'akeneoreferenceentity/domain/model/locale';
import * as React from 'react';
import {mount} from 'enzyme';

describe('>>>COMPONENT --- flag', () => {
    test('It displays the right flag as icon', () => {
        const enUS = denormalizeLocale({
            code: 'en_US',
            label: 'English (United States)',
            region: 'United States',
            language: 'English',
        });
        const usFlag = mount(
            <Flag
                locale ={enUS}
                displayLanguage={false}
                className={''}
            />
        );
        expect(usFlag.find('i').first().hasClass('flag-us')).toBe(true)

        const serbianCyrlRS = denormalizeLocale({
            code: 'sr_Cyrl_RS',
            label: 'Serbian (Cyrillic, Serbia)',
            region: 'Serbia',
            language: 'Serbian',
        });
        const serbianFlag = mount(
            <Flag
                locale ={serbianCyrlRS}
                displayLanguage={false}
                className={''}
            />
        );
        expect(serbianFlag.find('i').first().hasClass('flag-rs')).toBe(true)
    })
});
