export type NormalizedData = any;

export default abstract class Data {
  public abstract normalize(): any;
  public abstract isEmpty(): boolean;
}
