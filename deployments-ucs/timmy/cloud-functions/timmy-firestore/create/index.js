const {Firestore} = require('@google-cloud/firestore');
const firestore = new Firestore({
  projectId: process.env.fireStoreProjectId ,
  timestampsInSnapshots: true
});

exports.createDocument=(req, res) => {
// parse application/json
var  bodyjson=req.body;
   pfid  = bodyjson.pfid;
   instance_name =  bodyjson.instance_name;
   mysql_password = bodyjson.mysql_password;
   email_password = bodyjson.email_password;
   //check body params
   if (!pfid ||  !instance_name  || !mysql_password || !email_password ){
    res.status(402).send(" params empty, null or not exist  !!!");
   }
   //check envar
    domain = process.env.domain;
    projectId = process.env.projectId;
    mailerBaseUrl = process.env.mailerBaseUrl;
    if (!domain || !projectId || !mailerBaseUrl){
      res.status(402).send(" env variable empty !!!");
     }
  
   data = JSON.stringify(
     { "values":
      {
       "AKENEO_PIM_URL": "https://" + instance_name +"."+ domain,
       "APP_DATABASE_HOST": "pim-mysql." + pfid + ".svc.cluster.local",
       "APP_INDEX_HOSTS": "elasticsearch-client." + pfid + ".svc.cluster.local",
       "APP_TENANT_ID":   pfid ,
       "MAILER_PASSWORD":  email_password ,
       "MAILER_URL": mailerBaseUrl+"?encryption=tls&auth_mode=login&username=" + instance_name + "-"+ projectId+"@mg.cloud.akeneo.com&password=" + email_password + "&sender_address=no-reply%40" + pfid + "."+ domain,
       "MAILER_USER": instance_name + "-"+projectId+"@mg.cloud.akeneo.com",
       "MEMCACHED_SVC": "memcached." + pfid + ".svc.cluster.local",
       "APP_DATABASE_PASSWORD": mysql_password ,
       "PFID":  pfid ,
       "SRNT_GOOGLE_BUCKET_NAME":  pfid 
     }
   }
   );
   const docRef = firestore.collection(process.env.tenantContext).doc(instance_name).set(JSON.parse(data));
   res.status(200).send(" the document create with success !!!");

};

