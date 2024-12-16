import json
import requests
import os

# Membaca file JSON
with open('database.json', 'r') as f:
    data = json.load(f)

# Buat folder untuk menyimpan gambar dan video jika belum ada
if not os.path.exists('downloaded_images'):
    os.makedirs('downloaded_images')

if not os.path.exists('downloaded_videos'):
    os.makedirs('downloaded_videos')

# Fungsi untuk mengambil dan menyimpan gambar berdasarkan kata kunci
def download_image(keyword, file_name):
    url = f"https://labibweb.my.id/appsLb/db/foto/{data.foto}"  # URL untuk gambar
    response = requests.get(url)
    if response.status_code == 200:
        image_data = response.content
        file_path = os.path.join('downloaded_images', file_name)
        with open(file_path, 'wb') as handler:
            handler.write(image_data)
        print(f"{file_name} telah diunduh!!")  # Print text saat gambar berhasil diunduh
        return file_path  # Kembalikan path file lokal
    else:
        print(f"Gagal mengunduh gambar {file_name}. Status Code: {response.status_code}")
    return None

# Fungsi untuk mengambil dan menyimpan video berdasarkan kata kunci
def download_video(keyword, file_name):
    url = f"https://labibweb.my.id/appsLb/db/video/{data.video}"  # URL untuk video
    response = requests.get(url)
    if response.status_code == 200:
        video_data = response.content
        file_path = os.path.join('downloaded_videos', file_name)
        with open(file_path, 'wb') as handler:
            handler.write(video_data)
        print(f"{file_name} telah diunduh!!")  # Print text saat video berhasil diunduh
        return file_path  # Kembalikan path file lokal
    else:
        print(f"Gagal mengunduh video {file_name}. Status Code: {response.status_code}")
    return None

# Fungsi untuk mencari, mendownload gambar dan video, serta menambahkan path lokal ke data JSON
def add_local_media_paths(data):
    for category, items in data.items():
        for item, descriptions in items.items():
            for desc, content in descriptions.items():
                # Proses pengunduhan gambar
                if "foto" in content:
                    keyword = content["foto"]  # Menggunakan nilai dari key "foto" sebagai nama file
                    file_name = f"{keyword}"  # Nama file gambar yang diambil dari nilai "foto"
                    file_path = download_image(keyword, file_name)
                    if file_path:
                        content["local_image_path"] = file_path  # Menambahkan path file gambar ke JSON
                    else:
                        content["local_image_path"] = "Gambar tidak ditemukan"  # Jika gambar tidak ditemukan
                
                # Proses pengunduhan video
                if "video" in content:
                    keyword = content["video"]  # Menggunakan nilai dari key "video" sebagai nama file
                    file_name = f"{keyword}"  # Nama file video yang diambil dari nilai "video"
                    file_path = download_video(keyword, file_name)
                    if file_path:
                        content["local_video_path"] = file_path  # Menambahkan path file video ke JSON
                    else:
                        content["local_video_path"] = "Video tidak ditemukan"  # Jika video tidak ditemukan

    return data

# Proses pencarian, download gambar dan video, serta penyimpanan ke file JSON baru
data_with_media = add_local_media_paths(data)
with open('data_with_local_media.json', 'w') as f:
    json.dump(data_with_media, f, indent=4)