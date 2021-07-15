export type Extension = {
    id: string;
    name: string;
    logo: string;
    author: string;
    partner: string;
    description: string;
    url: string;
    categories: string[];
    certified: boolean;
};

export type Extensions = {
    total: number;
    extensions: Extension[];
};
