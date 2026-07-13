"""
start_local_s3.py
Menjalankan moto server sebagai simulator S3 lokal di http://localhost:5001

Cara pakai:
    pip install -r requirements.txt
    python3 start_local_s3.py

Biarkan terminal ini tetap terbuka selama aplikasi web dipakai,
karena PHP akan memanggil s3_helper.py yang connect ke server ini.
"""

from moto.server import ThreadedMotoServer

if __name__ == "__main__":
    server = ThreadedMotoServer(ip_address="localhost", port=5001)
    server.start()
    print("Moto S3 simulator jalan di http://localhost:5001")
    print("Tekan CTRL+C untuk berhenti.")
    try:
        while True:
            pass
    except KeyboardInterrupt:
        server.stop()
        print("Server dihentikan.")
