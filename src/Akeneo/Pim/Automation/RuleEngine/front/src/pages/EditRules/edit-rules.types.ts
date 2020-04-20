type FormData = {
  code: string;
  priority: string;
  labels: {
    [key: string]: string;
  };
};

export { FormData };
