type FormData = {
  code: string;
  priority: string;
  labels: {
    [key: string]: string;
  };
  content: { conditions: any[]; actions: any[] };
};

export { FormData };
