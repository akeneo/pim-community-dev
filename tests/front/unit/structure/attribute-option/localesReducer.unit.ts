import {
    initializeLocalesAction,
    localesReducer
} from '../../../../../src/Akeneo/Pim/Structure/Bundle/Resources/public/js/attribute-option/reducers';

describe('Locales reducer', () => {
    test('Default state is empty', () => {
        expect(localesReducer([], {} as any)).toMatchObject([]);
    });

    test('Initialize locales action with an empty state', () => {
        expect(localesReducer([], initializeLocalesAction([
            {'code':'de_DE', 'label':'German (Germany)'},
            {'code':'en_US', 'label':'English (United States)'},
            {'code':'fr_FR', 'label':'French (France)'},
        ]
        ))).toMatchObject([
            {code: 'de_DE', label: 'German (Germany)'},
            {code: 'en_US', label: 'English (United States)'},
            {code: 'fr_FR', label: 'French (France)'},
        ]);
    });

    test('Initialize locales action with an existing state', () => {
        expect(
          localesReducer([
                {'code':'it_IT', 'label':'Italian (Italy)'},
                {'code':'es_ES', 'label':'Spanish (Spain)'},
            ],
            initializeLocalesAction([
                {'code':'de_DE', 'label':'German (Germany)'},
                {'code':'en_US', 'label':'English (United States)'},
                {'code':'fr_FR', 'label':'French (France)'},
            ]
        ))).toMatchObject([
            {code: 'de_DE', label: 'German (Germany)'},
            {code: 'en_US', label: 'English (United States)'},
            {code: 'fr_FR', label: 'French (France)'},
        ]);
    });
});
