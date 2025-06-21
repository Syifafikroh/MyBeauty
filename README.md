# 📚 Struktur Proyek Aplikasi Artikel – `230605110181`

## 📁 admin/
File dan folder untuk keperluan admin, termasuk manajemen artikel, kategori, dan penulis.

```
├── edit_article.php         # Form edit artikel oleh admin
├── edit_category.php        # Form edit kategori
├── edit_author.php          # Form edit penulis
├── login.php                # Form login untuk admin
├── logout.php               # Logout admin
├── dashboard.php            # Halaman Admin Utama
├── add_article.php          # Form untuk menambah artikel baru
├── add_category.php         # Form untuk menambah kategori baru
├── add_author.php           # Form untuk menambah penulis baru
├── manage_articles.php      # Form untuk manage artikel
├── manage_category.php      # Form untuk manage kategori
├── manage_author.php        # Form untuk manage penulis

```

## 📁 pengunjung/
File-file untuk tampilan pengunjung publik.

```
├── artikel.php              # Menampilkan detail artikel untuk pengunjung
├── category.php             # Menampilkan kategori artikel
├── config.php               # Pengatur koneksi ke database
├── index.php                # Halaman utama website
├── style.css                # Gaya utama untuk tampilan dashboard pengunjung
├── foto.jpg                 # gambar latar belakang situs
```

---

---

## ℹ️ Penjelasan Umum

Struktur proyek ini membagi fungsionalitas menjadi dua peran utama:

1. **Admin**: Dapat mengelola artikel, kategori, dan penulis melalui folder `admin/`.
2. **Pengunjung**: Dapat membaca artikel, menghubungi admin via kontak, dan melihat informasi tentang situs melalui folder `pengunjung/`.