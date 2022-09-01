import { sleep } from 'k6';
import http from 'k6/http';
import encoding from 'k6/encoding';

const url = `https://${__ENV.FQDN}`;
const credentials = `${__ENV.API_CLIENT_ID}:${__ENV.API_SECRET}`;
const encodedCredentials = encoding.b64encode(credentials);
const k6_duration = `${__ENV.K6_DURATION}` || '10m';
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

export function setup() {
    // Create authentication request and return access_token
    const headers = {
        headers: {
            "Content-Type": "application/json",
            "Authorization": `Basic ${encodedCredentials}`,
        },
    };
    const data = {
        "username" : `${__ENV.API_USERNAME}`,
        "password" : `${__ENV.API_PASSWORD}`,
        "grant_type": "password"
    };

    const res = http.post(`${url}/api/oauth/v1/token`, JSON.stringify(data), headers);
    sleep(1);
    console.log(`${res.status} ${res.body}`);
    console.log(`${credentials} : ${encodedCredentials}`);

    const access_token = res.json().access_token;
    return access_token;
}

export default function (access_token) {
    // Call API produtcts
    const headers = {
        headers: {
            "Content-Type": "application/vnd.akeneo.collection+json",
            "Authorization": `Bearer ${access_token}`,
        },
    };
    var r = (Math.random() + 1).toString(36).substring(7);

    // console.log(`${JSON.stringify(body)}`);
    const list = http.get(`${url}/api/rest/v1/products`, headers);
    var jsonData = JSON.parse(list.body);
    // console.log(jsonData._embedded.items);

    // jsonData._embedded.items.forEach(function(item) {
    // console.log(item.values);
    // console.log(item);
    // var body = {
    //   "identifier" : identifier,
    //   "values" : {identifier:[{"scope": "can_canais", "locale":null, "data":r}]}
    // };
    // const res = http.patch(`${url}/api/rest/v1/products`, JSON.stringify(jsonData._embedded.items), headers);
    // var products = [];
    // jsonData._embedded.items.forEach(function(product){
    //   products.push({
    //     "identifier": product.identifier,
    //     "enabled": product.enabled
    //     // "family": product.family,
    //     // "categories": product.categories,
    //     // "groups": product.groups,
    //     // "values": product.values
    //   });
    // });
    var products_str = ""
    var sep = ""
    jsonData._embedded.items.forEach(function(product){
        delete product._links
        product.values.data == [(Math.random() + 1).toString(36).substring(7)]

        if (product.family != null) {
            products_str += JSON.stringify(product) + "\n";
        }
    });
    // console.log(products_str);
    const res = http.patch(`${url}/api/rest/v1/products`, products_str, headers);
    // console.log(products)
    // const res = http.patch(`${url}/api/rest/v1/products`, JSON.stringify(products), headers);
    // console.log(`${res.status} ${res.body}`);
    //   });
    // // var jsonData = JSON.parse(list.body._embedded.items);
    // JSON.parse(jsonData._embedded.items, (key, value) => {
    //   console.log(key);            // on affiche le nom de la propriété dans la console
    //   return value;                // et on renvoie la valeur inchangée.
    // });
    // console.log(jsonData);
    // console.log(list);
    sleep(1);
    console.log(`${res.status} ${res.body}`);
}