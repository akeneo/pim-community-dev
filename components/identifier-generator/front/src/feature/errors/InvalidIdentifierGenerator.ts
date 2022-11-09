import {Violation} from '../validators/Violation';

class InvalidIdentifierGenerator extends Error {
  violations: Violation[];

  constructor(violations: Violation[]) {
    super('Invalid identifier generator');
    this.violations = violations;
  }
}

export {InvalidIdentifierGenerator};
