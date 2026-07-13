"""
download_helper.py
Download satu object dari S3/moto ke path lokal.

Cara pakai:
    python3 download_helper.py <s3_key> <local_destination_path>
"""

import sys
from s3_helper import get_s3_client, BUCKET_NAME

if __name__ == "__main__":
    s3_key = sys.argv[1]
    local_path = sys.argv[2]

    s3 = get_s3_client()
    try:
        s3.download_file(BUCKET_NAME, s3_key, local_path)
    except Exception as e:
        print(f"Error: {e}", file=sys.stderr)
        sys.exit(1)
