import {
    attributeOptionsReducer,
    initializeAttributeOptionsAction,
    updateAttributeOptionAction,
    createAttributeOptionAction,
    deleteAttributeOptionAction,
} from 'akeneopimstructure/js/attribute-option/reducers';
import {resetAttributeOptionsAction} from "akeneopimstructure/js/attribute-option/reducers";

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

    test('Initialize an empty list of attribute options with an array', () => {
        expect(
          attributeOptionsReducer([], initializeAttributeOptionsAction([]))
        ).toMatchObject([]);
    });

    test('Initialize an empty list of attribute options with an object', () => {
        expect(
          attributeOptionsReducer([], initializeAttributeOptionsAction({}))
        ).toMatchObject([]);
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

    test('Reset attribute options with an existing state', () => {
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
            resetAttributeOptionsAction())
        ).toBeNull();
    });

    test('Update an attribute option', () => {
        expect(
            attributeOptionsReducer(
                blackAndBlueOptions,
                updateAttributeOptionAction({
                    "id": 86,
                    "code": "blue",
                    "optionValues": {
                        "en_US": {"id":255,"locale":"en_US","value":"Blue 2"},
                        "fr_FR":{"id":256,"locale":"fr_FR","value":"Bleu 2"}
                    }
                })
            )
        ).toMatchObject([
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
                    "en_US": {"id":255,"locale":"en_US","value":"Blue 2"},
                    "fr_FR":{"id":256,"locale":"fr_FR","value":"Bleu 2"}
                }
            },
        ]);
    });

    test('Update an attribute option with an empty state', () => {
        expect(
          attributeOptionsReducer(
            null,
            updateAttributeOptionAction({})
          )
        ).toBeNull();
    });

    test('create an attribute option', () => {
        expect(
          attributeOptionsReducer(
            blackAndBlueOptions,
            createAttributeOptionAction({
                "id": 115,
                "code": "yellow",
                "optionValues": {
                    "en_US": {"id":350,"locale":"en_US","value":"Yellow"},
                    "fr_FR":{"id":351,"locale":"fr_FR","value":"Jaune"}
                }
            })
          )
        ).toMatchObject([
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
            {
                "id": 115,
                "code": "yellow",
                "optionValues": {
                    "en_US": {"id":350,"locale":"en_US","value":"Yellow"},
                    "fr_FR":{"id":351,"locale":"fr_FR","value":"Jaune"}
                }
            }
        ]);
    });

    test('Create an attribute option with an empty state', () => {
        expect(
          attributeOptionsReducer(
            null,
            createAttributeOptionAction({})
          )
        ).toBeNull();
    });

    test('delete an attribute option', () => {
        expect(
          attributeOptionsReducer(
            [
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
                {
                    "id": 115,
                    "code": "yellow",
                    "optionValues": {
                        "en_US": {"id":350,"locale":"en_US","value":"Yellow"},
                        "fr_FR":{"id":351,"locale":"fr_FR","value":"Jaune"}
                    }
                }
            ],
            deleteAttributeOptionAction(115)
          )
        ).toMatchObject([
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
        ]);
    });

    test('delete an attribute option with an empty state', () => {
        expect(
            attributeOptionsReducer(
                null,
                deleteAttributeOptionAction({})
            )
        ).toBeNull();
    });
});
