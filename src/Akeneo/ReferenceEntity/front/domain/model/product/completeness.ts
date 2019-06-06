export type NormalizedCompleteness = {completeChildren: number; totalChildren: number; ratio: number};

class Completeness {
  private constructor(private completeChildren: number, private totalChildren: number, private ratio: number) {
    Object.freeze(this);
  }

  public static createFromNormalized({completeChildren, totalChildren, ratio}: NormalizedCompleteness) {
    return new Completeness(completeChildren, totalChildren, ratio);
  }

  public getCompleteChildren() {
    return this.completeChildren;
  }

  public getTotalChildren() {
    return this.totalChildren;
  }

  public isComplete() {
    if (this.completeChildren === 0 && this.totalChildren === 0) {
      return this.ratio === 100;
    }

    return this.completeChildren === this.totalChildren;
  }

  public hasCompleteItems() {
    return this.ratio > 0 || this.completeChildren > 0;
  }

  public getRatio() {
    return this.ratio;
  }

  public normalize(): NormalizedCompleteness {
    return {
      completeChildren: this.completeChildren,
      totalChildren: this.totalChildren,
      ratio: this.ratio,
    };
  }
}

export default Completeness;
export const denormalizeCompleteness = Completeness.createFromNormalized;
