# Courier Master Data API (Laravel 13)

Backend REST API sederhana untuk pengelolaan master data **Kurir (Courier)** menggunakan **Laravel 13** dengan pola **MVC + Repository Pattern**.

## Catatan Pengerjaan
- **Arsitektur & Standar Kode:** Menggunakan `CourierRepository` yang terpisah dari `CourierController`, mengikuti standar dan pola kode yang biasa saya terapkan saat magang (internship) sebelumnya agar alur kode sederhana, rapi, dan mudah di-debug.
- **Referensi & Pembelajaran:** Karena sebelumnya terbiasa dengan Laravel 8, saya melihat kembali struktur project lama saya sebagai gambaran penulisan kode. Selain itu, karena biasanya saya menguji fitur lewat tampilan frontend sederhana, penulisan **Feature Test menggunakan Model Factory** adalah hal baru bagi saya dan saya dibantu AI untuk menyusun bagian factory test tersebut.


## Endpoint API
- `GET /couriers` — List kurir (pagination, default sort `name`, `?sort_by=created_at`, `?search=budi+agung`, `?level=2,3`)
- `POST /couriers` — Tambah data kurir
- `GET /couriers/{id}` — Detail seluruh data kurir
- `PUT /couriers/{id}` — Update data kurir
- `DELETE /couriers/{id}` — Hapus data kurir

## Automated Test
Jalankan perintah berikut untuk menguji seluruh fitur dan validasi database:
```bash
php artisan test
```

### Hasil Test
```text
   PASS  Tests\Unit\ExampleTest
  ✓ that true is true

   PASS  Tests\Feature\CourierTest
  ✓ index returns paginated couriers sorted by name by default                   0.28s  
  ✓ index can override sort by registration date                                 0.01s  
  ✓ index can search multi word name                                             0.01s  
  ✓ index can filter by levels                                                   0.01s  
  ✓ show returns all courier data                                                0.01s  
  ✓ store validates and saves courier to database                                0.03s  
  ✓ update validates and updates courier in database                             0.01s  
  ✓ destroy deletes courier from database                                        0.01s  

   PASS  Tests\Feature\ExampleTest
  ✓ the application returns a successful response                                0.02s  

  Tests:    10 passed (32 assertions)
  Duration: 0.68s
```
