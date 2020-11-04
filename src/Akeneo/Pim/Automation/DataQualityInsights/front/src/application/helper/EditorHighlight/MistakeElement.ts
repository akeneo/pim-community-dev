export default interface MistakeElement {
  text: string;
  type: string;
  globalOffset: number;
  offset: number;
  line: number;
  suggestions: string[];
}
