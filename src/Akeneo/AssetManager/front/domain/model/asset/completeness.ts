import Value from 'akeneoassetmanager/domain/model/asset/value';

export type NormalizedCompleteness = {complete: number; required: number};

class Completeness {
  private constructor(private complete: number, private required: number) {
    Object.freeze(this);
  }

  public static createFromNormalized({complete, required}: NormalizedCompleteness) {
    return new Completeness(complete, required);
  }

  public static createFromValues(values: Value[]) {
    const normalizedCompleteness = values.reduce(
      (completeness: NormalizedCompleteness, currentValue: Value) => {
        const newCompleteness = {...completeness};
        if (currentValue.isComplete()) {
          newCompleteness.complete++;
        }

        if (currentValue.isRequired()) {
          newCompleteness.required++;
        }

        return newCompleteness;
      },
      {complete: 0, required: 0}
    );

    return Completeness.createFromNormalized(normalizedCompleteness);
  }

  public getCompleteAttributeCount() {
    return this.complete;
  }

  public getRequiredAttributeCount() {
    return this.required;
  }

  public hasRequiredAttribute() {
    return this.required > 0;
  }

  public hasCompleteAttribute() {
    return this.complete > 0;
  }

  public isComplete() {
    return this.complete === this.required;
  }

  public getRatio() {
    if (0 === this.required) {
      return 0;
    }

    return Math.round((100 * this.complete) / this.required);
  }
}

export default Completeness;
