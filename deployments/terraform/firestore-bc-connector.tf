// 
// FIRESTORE COLLECTION
//
//

resource "google_firestore_document" "placeholder" {
  count = 1

  collection  = local.pfid
  document_id = "placeholder"
  fields      = "{\"placeholder\":{\"mapValue\":{\"fields\":{\"placeholder\":{\"stringValue\":\"fakevalue\"}}}}}"

  provisioner "local-exec" {
    when    = destroy
    command = "firebase firestore:delete --project ${self.project} -ry /${self.collection}"
  }
}

//
// PERMISSIONS
//
//

resource "google_project_iam_member" "firestore_user_iam_member" {
  count = 1
  role = "roles/datastore.user"
  member = "serviceAccount:${google_service_account.pim_service_account.email}"

  depends_on = [
    google_service_account.pim_service_account,
  ]
}

