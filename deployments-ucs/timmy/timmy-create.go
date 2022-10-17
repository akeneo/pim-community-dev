package main

import (
    "context"
    "log"
    "os"
    "cloud.google.com/go/firestore"
)

func createClientFirestore(ctx context.Context, firestoreProjectID string) *firestore.Client {
    client, err := firestore.NewClient(ctx, firestoreProjectID)
    if err != nil {
            log.Fatalf("Failed to create client: %v", err)
    }

    return client
}

func setDocument(ctx context.Context, client *firestore.Client, collection string, document string, data map[string]interface{}) {
    result, err := client.Collection(collection).Doc(document).Set(ctx, data)
    if err != nil {
      log.Fatalln(err)
    }
    log.Print(result)
}

func deleteDocument(ctx context.Context, client *firestore.Client, collection string, document string) {
    result, err := client.Collection(collection).Doc(document).Delete(ctx)
    if err != nil {
      log.Fatalln(err)
    }
    log.Print(result)
}

func readDocument(ctx context.Context, client *firestore.Client, collection string, document string) map[string]interface{} {
    doc, err := client.Collection(collection).Doc(document).Get(ctx)
    if err != nil {
        log.Fatalf("Failed to iterate: %v", err)
    }
    return doc.Data()
}

func main() {
    ctx := context.Background()
    firestoreProjectID := "akecld-prd-pim-fire-eur-dev"

    // Firestore
    clientFirestore := createClientFirestore(ctx, firestoreProjectID)
    collection := os.Args[6]
    pfid := os.Args[1]
    instance_name := os.Args[2]
    mysql_password := os.Args[3]
    email_password := os.Args[4]
    pim_secret := os.Args[5]
    data := map[string]interface{}{
        "values": `{
            "AKENEO_PIM_URL": "https://` + instance_name + `.pim-saas-dev.dev.cloud.akeneo.com",
            "APP_DATABASE_HOST": "pim-mysql.` + pfid + `.svc.cluster.local",
            "APP_INDEX_HOSTS": "elasticsearch-client.` + pfid + `.svc.cluster.local",
            "APP_TENANT_ID": "` + pfid + `",
            "MAILER_PASSWORD": "` + email_password + `",
            "MAILER_URL": "smtp://smtp.mailgun.org:2525?encryption=tls&auth_mode=login&username=` + instance_name + `-akecld-prd-pim-saas-dev@mg.cloud.akeneo.com&password=` + email_password + `&sender_address=no-reply-` + pfid + `.pim-saas-dev.dev.cloud.akeneo.com",
            "MAILER_USER": "` + instance_name + `-akecld-prd-pim-saas-dev@mg.cloud.akeneo.com",
            "MEMCACHED_SVC": "memcached.` + pfid + `.svc.cluster.local",
            "APP_DATABASE_PASSWORD": "` + mysql_password + `",
            "APP_SECRET": "` + pim_secret + `",
            "PFID": "` + pfid + `",
            "SRNT_GOOGLE_BUCKET_NAME": "` + pfid + `"
          }`,
    }
    setDocument(ctx, clientFirestore, collection, pfid, data)
    defer clientFirestore.Close()
}
