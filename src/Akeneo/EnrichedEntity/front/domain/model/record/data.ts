export type NormalizedData = any;

export default abstract class Data {
  public abstract normalize(): any;
}
