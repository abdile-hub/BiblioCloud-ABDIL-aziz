import sys
import json
import boto3
from botocore.exceptions import ClientError

# ====== KONFIGURASI ======
LOCAL_MODE = True
BUCKET_NAME = "bibliocloud-books"
LOCAL_ENDPOINT_URL = "http://localhost:5001"  # endpoint moto server
AWS_REGION = "us-east-1"


def get_s3_client():
    if LOCAL_MODE:
        return boto3.client(
            "s3",
            endpoint_url=LOCAL_ENDPOINT_URL,
            aws_access_key_id="test",
            aws_secret_access_key="test",
            region_name=AWS_REGION,
        )
    else:
        return boto3.client("s3", region_name=AWS_REGION)


def ensure_bucket_exists(s3):
    try:
        s3.head_bucket(Bucket=BUCKET_NAME)
    except ClientError:
        s3.create_bucket(Bucket=BUCKET_NAME)


def list_objects():
    s3 = get_s3_client()
    ensure_bucket_exists(s3)
    response = s3.list_objects_v2(Bucket=BUCKET_NAME)
    result = []
    if "Contents" in response:
        for obj in response["Contents"]:
            result.append({
                "key": obj["Key"],
                "size": obj["Size"],
                "last_modified": obj["LastModified"].isoformat(),
            })
    return {"status": "success", "data": result}


def upload_object(local_path, s3_key):
    s3 = get_s3_client()
    ensure_bucket_exists(s3)
    try:
        s3.upload_file(local_path, BUCKET_NAME, s3_key)
        return {"status": "success", "message": f"Berhasil upload {s3_key}"}
    except Exception as e:
        return {"status": "error", "message": str(e)}


def delete_object(s3_key):
    s3 = get_s3_client()
    try:
        s3.delete_object(Bucket=BUCKET_NAME, Key=s3_key)
        return {"status": "success", "message": f"Berhasil hapus {s3_key}"}
    except Exception as e:
        return {"status": "error", "message": str(e)}


def detail_object(s3_key):
    s3 = get_s3_client()
    try:
        response = s3.head_object(Bucket=BUCKET_NAME, Key=s3_key)
        return {
            "status": "success",
            "data": {
                "key": s3_key,
                "size": response["ContentLength"],
                "last_modified": response["LastModified"].isoformat(),
                "content_type": response.get("ContentType", "unknown"),
            },
        }
    except Exception as e:
        return {"status": "error", "message": str(e)}


if __name__ == "__main__":
    action = sys.argv[1] if len(sys.argv) > 1 else None

    if action == "list":
        output = list_objects()
    elif action == "upload" and len(sys.argv) == 4:
        output = upload_object(sys.argv[2], sys.argv[3])
    elif action == "delete" and len(sys.argv) == 3:
        output = delete_object(sys.argv[2])
    elif action == "detail" and len(sys.argv) == 3:
        output = detail_object(sys.argv[2])
    else:
        output = {"status": "error", "message": "Action tidak dikenali atau parameter kurang"}

    print(json.dumps(output))
