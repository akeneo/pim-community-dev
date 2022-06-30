// jest.unmock('./getCriterionByFieldType');
// jest.unmock('./StatusCriterion');
//
// import {AnyCriterionState} from '../models/Criterion';
// import {getCriterionByFieldType} from './getCriterionByFieldType';
// import {Operator} from '../models/Operator';
//
// test('test it throws on unknown criterion', () => {
//     expect(() => {
//         getCriterionByFieldType('unknow');
//     }).toThrow(Error);
// });
//
// const tests: {field: string; state: AnyCriterionState}[] = [
//     {
//         field: 'enabled',
//         state: {
//             field: 'enabled',
//             operator: Operator.EQUALS,
//             value: true,
//         },
//     },
// ];
//
// test.each(tests)('it maps the field to its criterion #%#', ({field, state}) => {
//     const result = getCriterionByFieldType(field);
//
//     expect(result).toMatchObject({
//         component: expect.any(Function),
//         factory: expect.any(Function),
//     });
//
//     expect(result.factory()).toEqual(state);
// });
