export type Payload = {
  labels: { [key: string]: string };
  code: string;
  priority: number;
  content: {
    conditions: {};
    actions: {};
  };
};
