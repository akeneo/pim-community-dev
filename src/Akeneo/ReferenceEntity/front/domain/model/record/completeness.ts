import Value from 'akeneoreferenceentity/domain/model/record/value';

type NormalizedCompleteness = {complete: number; required: number};

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

  public hasNoRequiredAttribute() {
    return this.required === 0;
  }

  public hasNoCompleteAttribute() {
    return this.complete === 0;
  }

  public isComplete() {
    return this.complete === this.required;
  }

  public getRatio() {
    return Math.round((100 * this.complete) / this.required);
  }
}

export default Completeness;
