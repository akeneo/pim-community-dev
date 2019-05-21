export type NormalizedCompleteness = {complete: number; required: number};

class Completeness {
  private constructor(private complete: number, private required: number) {
    Object.freeze(this);
  }

  public static createFromNormalized({complete, required}: NormalizedCompleteness) {
    return new Completeness(complete, required);
  }

  public getCompleteCount() {
    return this.complete;
  }

  public getRequiredCount() {
    return this.required;
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
