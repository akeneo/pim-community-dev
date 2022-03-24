export type IntegerPercent = number;
type PositiveInteger = number;

/**
 * Calculate integer percentage corresponding to nbGood over (nbGood + nbToImprove)
 * By convention computePercent(0, 0) return 0
 * @param nbGood the number of product having some criterion satisfied (must be integer superior or equal to 0)
 * @param nbToImprove the number of product having some criterion not satisfied (must be integer superior or equal to 0)
 */
export function computePercent(nbGood: PositiveInteger, nbToImprove: PositiveInteger): IntegerPercent {
  const total = nbGood + nbToImprove;
  const percent = total === 0 ? 0 : Math.round((nbGood / total) * 100);

  // 100 is only possible when all are good
  if (percent === 100 && nbToImprove > 0) {
    return 99;
    // 0 is only possible when all are to improve
  } else if (percent === 0 && nbGood > 0) {
    return 1;
  }

  return percent;
}
