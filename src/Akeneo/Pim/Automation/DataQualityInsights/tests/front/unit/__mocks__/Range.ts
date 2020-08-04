export default class Range {
  public startContainer: Node;
  public endContainer: Node;
  public endOffset: number;
  public startOffset: number;

  public collapsed: boolean;
  public commonAncestorContainer: Node;

  constructor() {
    this.collapsed = false;
    this.commonAncestorContainer = document.body;
  }

  public selectNodeContents(node: Node) {
    this.commonAncestorContainer = node.firstChild || node;
  }

  public setStart(node: Node, offset: number) {
    this.startContainer = node;
    this.startOffset = offset;
  }

  public setEnd(node: Node, offset: number) {
    this.endContainer = node;
    this.endOffset = offset;
  }
}
