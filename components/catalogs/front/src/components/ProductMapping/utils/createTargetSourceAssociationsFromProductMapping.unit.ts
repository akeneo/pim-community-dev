import {createTargetSourceAssociationsFromProductMapping} from './createTargetSourceAssociationsFromProductMapping';

jest.unmock('./createTargetSourceAssociationsFromProductMapping');

test('it creates targets from the product mapping with UUID first', () => {
    expect(
        createTargetSourceAssociationsFromProductMapping({
            title: {
                source: 'title',
                locale: null,
                scope: null,
            },
            uuid: {
                source: 'uuid',
                locale: null,
                scope: null,
            },
            description: {
                source: 'description',
                locale: null,
                scope: null,
            },
        })
    ).toEqual([
        [
            'uuid',
            {
                source: 'uuid',
                locale: null,
                scope: null,
            },
        ],
        [
            'title',
            {
                source: 'title',
                locale: null,
                scope: null,
            },
        ],
        [
            'description',
            {
                source: 'description',
                locale: null,
                scope: null,
            },
        ],
    ]);
});
