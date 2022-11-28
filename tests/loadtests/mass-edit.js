import {expect} from 'https://jslib.k6.io/k6chaijs/4.3.4.1/index.js';
import http from 'k6/http';
import encoding from 'k6/encoding';

const protocol = `${__ENV.SECURE}` == 0 ? `http` : `https`;
const url = `${protocol}://${__ENV.FQDN}`;
const credentials = `${__ENV.API_CLIENT_ID}:${__ENV.API_SECRET}`;
const encodedCredentials = encoding.b64encode(credentials);
const k6_duration = `${__ENV.K6_DURATION}` || '30s';
const k6_vus = `${__ENV.K6_VUS}` || 10;

export const options = {
    duration: `${k6_duration}`,
    vus: `${k6_vus}`,
    thresholds: {
        http_req_failed: ['rate<0.01'],
        http_req_duration: ['p(90)<5000'],
        http_req_waiting: ['p(90)<5000'],
    },
};

function getCredentials() {
    // Create authentication request and return access_token
    const headers = {
        headers: {
            "Content-Type": "application/json",
            "Authorization": `Basic ${encodedCredentials}`,
        },
    };
    const data = {
        "username": `${__ENV.API_USERNAME}`,
        "password": `${__ENV.API_PASSWORD}`,
        "grant_type": "password"
    };

    const response = http.post(`${url}/api/oauth/v1/token`, JSON.stringify(data), headers);

    expect(response.status, `API status code on authentication`).to.be.equal(200);

    return response.json();
}


function defaultHeaders(access_token) {
    return {
        "Content-Type": "application/vnd.akeneo.collection+json",
        "Authorization": `Bearer ${access_token}`,
    };
}

export default function(){
    // Create authentication request and return access_token
    const creds = getCredentials();
    const access_token = creds.access_token;

    let headers = {
        headers: defaultHeaders(access_token)
    };

    const listAllProducts = http.get(`${url}/api/rest/v1/products`, headers);
    let jsonData = JSON.parse(listAllProducts.body);
    let products_string = "";

    //loop on products list
    jsonData._embedded.items.forEach(function(product){
        delete product._links;
        delete product.updated;
        delete product.metadata;
        products_string += JSON.stringify(product) + "\n";
    });

    //update all products with new information
    const response = http.patch(`${url}/api/rest/v1/products`, products_string, headers);
}
