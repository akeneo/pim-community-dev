const {Firestore} = require('@google-cloud/firestore');
const CryptoJS = require("crypto-js");

const firestore = new Firestore({
  projectId: process.env.fireStoreProjectId ,
  timestampsInSnapshots: true
});

exports.createDocument=(req, res) => {
// parse application/json
var  bodyjson=req.body;
   tenant_id  = bodyjson.tenant_id;
   tenant_name =  bodyjson.tenant_name;
   mysql_password = bodyjson.mysql_password;
   email_password = bodyjson.email_password;
   //check body params
   if (!tenant_id ||  !tenant_name  || !mysql_password || !email_password ){
    res.status(402).send(" params empty, null or not exist  !!!");
   }
   //check envar
    domain = process.env.domain;
    projectId = process.env.projectId;
    mailerBaseUrl = process.env.mailerBaseUrl;
    if (!domain || !projectId || !mailerBaseUrl){
      res.status(402).send(" env variable empty !!!");
     }

   tenantContextData = JSON.stringify({tenant_name :{
       "AKENEO_PIM_URL": "https://" + tenant_name +"."+ domain,
       "APP_DATABASE_HOST": "pim-mysql." + tenant_id + ".svc.cluster.local",
       "APP_INDEX_HOSTS": "elasticsearch-client." + tenant_id + ".svc.cluster.local",
       "APP_TENANT_ID":   tenant_id ,
       "MAILER_PASSWORD":  email_password ,
       "MAILER_URL": mailerBaseUrl+"?encryption=tls&auth_mode=login&username=" + tenant_name + "-"+ projectId+"@mg.cloud.akeneo.com&password=" + email_password + "&sender_address=no-reply%40" + tenant_name + "."+ domain,
       "MAILER_USER": tenant_name + "-"+projectId+"@mg.cloud.akeneo.com",
       "MEMCACHED_SVC": "memcached." + tenant_id + ".svc.cluster.local",
       "APP_DATABASE_PASSWORD": mysql_password ,
       "PFID":  tenant_id ,
       "SRNT_GOOGLE_BUCKET_NAME":  tenant_id
     }
    });


   const encryptKey = process.env.TENANT_CONTEXT_ENCRYPTION_KEY;
   async function  encryptAES (inputText, key){
    return CryptoJS.AES.encrypt(inputText, key).toString();
  }
  encryptAES(tenantContextData, encryptKey).then((response) =>{
       data= JSON.stringify(
        {
          "status" : "creation_in_progress",
          "status_date": new Date().toISOString(),
          "context" : response.toString()
       }
    );

      const docRef = firestore.collection(process.env.tenantContext).doc(tenant_name).set(JSON.parse(data),{merge: true});

  });

  async function  decryptAES (encryptedContext, encryptKey){
      return CryptoJS.AES.decrypt(encryptedContext, encryptKey);
};
//TODO : if we need to decrypt document
/*
let encryptedContext=" get my content from some where"
  decryptAES(encryptedContext, encryptKey).then((response) =>{
    const decrypted =response.toString()
  });
*/

   res.status(200).send(" the document create with success !!!");

};
