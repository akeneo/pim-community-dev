import {
    initializeAttributeOptionsAction,
    attributeOptionsReducer
} from '../../../../../src/Akeneo/Pim/Structure/Bundle/Resources/public/js/attribute-option/reducers';

const blackAndBlueOptions = [
    {
        "id": 85,
        "code": "black",
        "optionValues": {
            "en_US": {"id":252,"locale":"en_US","value":"Black"},
            "fr_FR":{"id":253,"locale":"fr_FR","value":"Noir"}
        }
    },
    {
        "id": 86,
        "code": "blue",
        "optionValues": {
            "en_US": {"id":255,"locale":"en_US","value":"Blue"},
            "fr_FR":{"id":256,"locale":"fr_FR","value":"Bleu"}
        }
    },
];

describe('Attribute options reducer', () => {
    test('Default state is empty', () => {
        expect(attributeOptionsReducer([], {} as any)).toMatchObject([]);
    });

    test('Initialize attribute options action with an empty state', () => {
        expect(
          attributeOptionsReducer([], initializeAttributeOptionsAction(blackAndBlueOptions))
        ).toMatchObject(blackAndBlueOptions);
    });

    test('Initialize attribute options action with an existing state', () => {
        expect(
          attributeOptionsReducer([
                {
                    "id": 1,
                    "code": "red",
                    "optionValues": {
                        "en_US": {"id": 2,"locale":"en_US","value":"Red"},
                        "fr_FR":{"id": 3,"locale":"fr_FR","value":"Rouge"}
                    }
                },
            ],
            initializeAttributeOptionsAction(blackAndBlueOptions))
        ).toMatchObject(blackAndBlueOptions);
    });
});
