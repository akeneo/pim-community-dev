import React from 'react';
import {act, renderHook} from '@testing-library/react-hooks';
import {useSortedAttributeOptions} from "akeneopimstructure/js/attribute-option/hooks";
import {AttributeOption} from "akeneopimstructure/js/attribute-option/model";

const renderUseSortedAttributeOptions = (
    attributeOptions: AttributeOption[]|null,
    autoSortOptions: boolean,
    manuallySortAttributeOptions: (attributeOptions: AttributeOption[]) => void
) => {
    return renderHook(() => useSortedAttributeOptions(
        attributeOptions,
        autoSortOptions,
        manuallySortAttributeOptions
    ));
};

const givenAttributeOptions = () => {
    return [
        {
            id: 86,
            code: 'yellow',
            optionValues: {
                'en_US': {id:256, locale:'en_US', value:'Yellow'},
                'fr_FR':{id:257, locale:'fr_FR', value:'Jaune'}
            }
        },
        {
            id: 85,
            code: 'black',
            optionValues: {
                'en_US': {id:254, locale:'en_US', value:'Black'},
                'fr_FR':{id:255, locale:'fr_FR', value:'Noir'}
            }
        },
        {
            id: 83,
            code: 'red',
            optionValues: {
                'en_US': {id:250, locale:'en_US', value:'Red'},
                'fr_FR':{id:251, locale:'fr_FR', value:'Rouge'}
            }
        },
        {
            id: 84,
            code: 'blue',
            optionValues: {
                'en_US': {id:252, locale:'en_US', value:'Blue'},
                'fr_FR':{id:253, locale:'fr_FR', value:'Bleu'}
            }
        },
    ];
};

const expectAlphabeticSortedAttributeOptions = () => {
    return [
        {
            id: 85,
            code: 'black',
            optionValues: {
                'en_US': {id:254, locale:'en_US', value:'Black'},
                'fr_FR':{id:255, locale:'fr_FR', value:'Noir'}
            }
        },
        {
            id: 84,
            code: 'blue',
            optionValues: {
                'en_US': {id:252, locale:'en_US', value:'Blue'},
                'fr_FR':{id:253, locale:'fr_FR', value:'Bleu'}
            }
        },
        {
            id: 83,
            code: 'red',
            optionValues: {
                'en_US': {id:250, locale:'en_US', value:'Red'},
                'fr_FR':{id:251, locale:'fr_FR', value:'Rouge'}
            }
        },
        {
            id: 86,
            code: 'yellow',
            optionValues: {
                'en_US': {id:256, locale:'en_US', value:'Yellow'},
                'fr_FR':{id:257, locale:'fr_FR', value:'Jaune'}
            }
        },
    ];
};

const expectManuallySortedAttributeOptions = () => {
    return [
        {
            id: 83,
            code: 'red',
            optionValues: {
                'en_US': {id:250, locale:'en_US', value:'Red'},
                'fr_FR':{id:251, locale:'fr_FR', value:'Rouge'}
            }
        },
        {
            id: 85,
            code: 'black',
            optionValues: {
                'en_US': {id:254, locale:'en_US', value:'Black'},
                'fr_FR':{id:255, locale:'fr_FR', value:'Noir'}
            }
        },
        {
            id: 84,
            code: 'blue',
            optionValues: {
                'en_US': {id:252, locale:'en_US', value:'Blue'},
                'fr_FR':{id:253, locale:'fr_FR', value:'Bleu'}
            }
        },
        {
            id: 86,
            code: 'yellow',
            optionValues: {
                'en_US': {id:256, locale:'en_US', value:'Yellow'},
                'fr_FR':{id:257, locale:'fr_FR', value:'Jaune'}
            }
        },
    ];
};


const expectValidSortedAttributeOptions = () => {
    return [
        {
            id: 86,
            code: 'yellow',
            optionValues: {
                'en_US': {id:256, locale:'en_US', value:'Yellow'},
                'fr_FR':{id:257, locale:'fr_FR', value:'Jaune'}
            }
        },
        {
            id: 83,
            code: 'red',
            optionValues: {
                'en_US': {id:250, locale:'en_US', value:'Red'},
                'fr_FR':{id:251, locale:'fr_FR', value:'Rouge'}
            }
        },
        {
            id: 85,
            code: 'black',
            optionValues: {
                'en_US': {id:254, locale:'en_US', value:'Black'},
                'fr_FR':{id:255, locale:'fr_FR', value:'Noir'}
            }
        },
        {
            id: 84,
            code: 'blue',
            optionValues: {
                'en_US': {id:252, locale:'en_US', value:'Blue'},
                'fr_FR':{id:253, locale:'fr_FR', value:'Bleu'}
            }
        },
    ];
};

describe('useSortedAttributeOptions', () => {
    beforeEach(() => {
        jest.clearAllMocks()
    });

    afterAll(() => {
        jest.resetAllMocks();
    });

    const sort = jest.fn();

    it('should initialize sorted attribute options with null when given attributeOptions is null', () => {
        const {result} = renderUseSortedAttributeOptions(null, false, sort);
        let sortedAttributeOptions;

        act(() => {
            sortedAttributeOptions = result.current.sortedAttributeOptions;
        });

        expect(sortedAttributeOptions).toBeNull();
    });

    it('should initialize sorted attribute options with given attribute options', () => {
        const attributeOptions = givenAttributeOptions();
        const {result} = renderUseSortedAttributeOptions(attributeOptions, false, sort);
        let sortedAttributeOptions;

        act(() => {
            sortedAttributeOptions = result.current.sortedAttributeOptions;
        });

        expect(sortedAttributeOptions).toEqual(attributeOptions);
    });

    it('should sort by alphabetic order the attribute options when given attribute options change and autoSort is active', () => {
        const attributeOptions = givenAttributeOptions();
        const expectedSortedAttributeOptions = expectAlphabeticSortedAttributeOptions();
        const {result} = renderUseSortedAttributeOptions(attributeOptions, true, sort);
        let sortedAttributeOptions;

        act(() => {
            sortedAttributeOptions = result.current.sortedAttributeOptions;
        });

        expect(sortedAttributeOptions).toEqual(expectedSortedAttributeOptions);
    });

    describe('move', () => {
        it('should not move options when the list is null', () => {
            const {result} = renderUseSortedAttributeOptions(null, false, sort);
            let sortedAttributeOptions;

            act(() => {
                result.current.move('red', 'yellow');
            });

            act(() => {
                sortedAttributeOptions = result.current.sortedAttributeOptions;
            });

            expect(sortedAttributeOptions).toBeNull();
        });

        it('should not move options when they are equal', () => {
            const attributeOptions = givenAttributeOptions();
            const {result} = renderUseSortedAttributeOptions(attributeOptions, false, sort);
            let sortedAttributeOptions;

            act(() => {
                result.current.move('red', 'red');
            });

            act(() => {
                sortedAttributeOptions = result.current.sortedAttributeOptions;
            });

            expect(sortedAttributeOptions).toEqual(attributeOptions);
        });

        it('should move options', () => {
            const attributeOptions = givenAttributeOptions();
            const expectedSortedOptions = expectManuallySortedAttributeOptions();
            const {result} = renderUseSortedAttributeOptions(attributeOptions, false, sort);
            let sortedAttributeOptions;

            act(() => {
                result.current.move('red', 'yellow');
            });

            act(() => {
                result.current.move('yellow', 'blue');
            });

            act(() => {
                sortedAttributeOptions = result.current.sortedAttributeOptions;
            });

            expect(sortedAttributeOptions).toEqual(expectedSortedOptions);
        });
    });

    describe('validate', () => {
        it('should validate the order of the attribute options', () => {
            const attributeOptions = givenAttributeOptions();
            const expectSortedAttributeOptions = expectValidSortedAttributeOptions();
            const {result} = renderUseSortedAttributeOptions(attributeOptions, false, sort);

            act(() => {
                result.current.validate();
            });

            expect(sort).not.toHaveBeenCalled();

            act(() => {
                result.current.move('red', 'black');
            });

            act(() => {
                result.current.validate();
            });

            expect(sort).toHaveBeenCalledWith(expectSortedAttributeOptions);
        });

        it('should not validate the order of the attribute options when the list is null', () => {
            const {result} = renderUseSortedAttributeOptions(null, false, sort);

            act(() => {
                result.current.validate();
            });

            expect(sort).not.toHaveBeenCalled();
        });
    });
});
