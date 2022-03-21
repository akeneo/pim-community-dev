import {computePercent} from '../../../../front/src/domain/IntegerPercent';

describe('IntegerPercent', () => {
  test.each`
    nbGood  | nbToImprove | expectedPercent
    ${0}    | ${0}        | ${0}
    ${0}    | ${1}        | ${0}
    ${1}    | ${2000}     | ${1}
    ${1000} | ${1}        | ${99}
    ${1000} | ${0}        | ${100}
    ${495}  | ${505}      | ${50}
    ${494}  | ${505}      | ${49}
    ${510}  | ${490}      | ${51}
    ${499}  | ${490}      | ${50}
    ${500}  | ${490}      | ${51}
    ${1}    | ${1}        | ${50}
  `('computePercent($nbGood, $nbToImprove) === $expectedPercent', ({nbGood, nbToImprove, expectedPercent}) => {
    expect(computePercent(nbGood, nbToImprove)).toBe(expectedPercent);
  });
});
