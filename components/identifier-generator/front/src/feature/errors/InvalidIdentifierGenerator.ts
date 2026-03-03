import {Violation} from '../validators';

class InvalidIdentifierGenerator extends Error {
  violations: Violation[];

  constructor(violations: Violation[]) {
    super('Invalid identifier generator');
    this.violations = violations;
  }
}

export {InvalidIdentifierGenerator};
