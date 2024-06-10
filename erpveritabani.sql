CREATE DATABASE IF NOT EXISTS erp_db;

USE erp_db;

CREATE TABLE IF NOT EXISTS transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tarih DATE NOT NULL,
    tur ENUM('gelir', 'gider') NOT NULL,
    miktar DECIMAL(10, 2) NOT NULL,
    aciklama VARCHAR(255),
    vergi_orani DECIMAL(5, 2) NOT NULL,
    para_birimi VARCHAR(3) NOT NULL
);

CREATE TABLE IF NOT EXISTS finance_management (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tarih DATE NOT NULL,
    tur ENUM('gelir', 'gider') NOT NULL,
    miktar DECIMAL(10, 2) NOT NULL,
    aciklama VARCHAR(255),
    vergi_orani DECIMAL(5, 2) NOT NULL,
    para_birimi VARCHAR(3) NOT NULL
);

CREATE TABLE IF NOT EXISTS muhasebe (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tarih DATE NOT NULL,
    tur ENUM('borc', 'alacak') NOT NULL,
    miktar DECIMAL(10, 2) NOT NULL,
    aciklama VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS insan_kaynaklari (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad_soyad VARCHAR(255) NOT NULL,
    pozisyon VARCHAR(255) NOT NULL,
    maas DECIMAL(10, 2) NOT NULL,
    ise_giris_tarihi DATE NOT NULL
);

CREATE TABLE IF NOT EXISTS satis_yonetimi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tarih DATE NOT NULL,
    musteri_adi VARCHAR(255) NOT NULL,
    miktar DECIMAL(10, 2) NOT NULL,
    urun VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS satin_alma_yonetimi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tarih DATE NOT NULL,
    tedarikci_adi VARCHAR(255) NOT NULL,
    miktar DECIMAL(10, 2) NOT NULL,
    urun VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS stok_yonetimi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    urun_adi VARCHAR(255) NOT NULL,
    miktar INT NOT NULL,
    konum VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS uretim_planlama (
    id INT AUTO_INCREMENT PRIMARY KEY,
    urun_adi VARCHAR(255) NOT NULL,
    planlanan_miktar INT NOT NULL,
    baslangic_tarihi DATE NOT NULL,
    bitis_tarihi DATE NOT NULL
);

CREATE TABLE IF NOT EXISTS musteri_iliskileri_yonetimi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    musteri_adi VARCHAR(255) NOT NULL,
    iletisim_tarihi DATE NOT NULL,
    iletisim_yontemi VARCHAR(255) NOT NULL,
    notlar TEXT
);

CREATE TABLE IF NOT EXISTS proje_yonetimi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    proje_adi VARCHAR(255) NOT NULL,
    baslangic_tarihi DATE NOT NULL,
    bitis_tarihi DATE NOT NULL,
    butce DECIMAL(10, 2) NOT NULL
);

INSERT INTO transactions (tarih, tur, miktar, aciklama, vergi_orani, para_birimi) VALUES ('2024-06-01', 'gelir', 1000.00, 'Müşteri ödemesi', 18.00, 'TRY');
INSERT INTO transactions (tarih, tur, miktar, aciklama, vergi_orani, para_birimi) VALUES ('2024-06-02', 'gider', 200.00, 'Ofis malzemeleri', 18.00, 'TRY');

INSERT INTO finance_management (tarih, tur, miktar, aciklama, vergi_orani, para_birimi) VALUES ('2024-06-01', 'gelir', 1500.00, 'Satış geliri', 18.00, 'TRY');
INSERT INTO finance_management (tarih, tur, miktar, aciklama, vergi_orani, para_birimi) VALUES ('2024-06-02', 'gider', 500.00, 'Kira ödemesi', 18.00, 'TRY');

INSERT INTO muhasebe (tarih, tur, miktar, aciklama) VALUES ('2024-06-01', 'borc', 1000.00, 'Banka hesabi borcu');
INSERT INTO muhasebe (tarih, tur, miktar, aciklama) VALUES ('2024-06-02', 'alacak', 500.00, 'Müşteri ödemesi');

INSERT INTO insan_kaynaklari (ad_soyad, pozisyon, maas, ise_giris_tarihi) VALUES ('Ali Veli', 'Yazılım Mühendisi', 6000.00, '2024-01-15');
INSERT INTO insan_kaynaklari (ad_soyad, pozisyon, maas, ise_giris_tarihi) VALUES ('Ayşe Yılmaz', 'Muhasebeci', 4000.00, '2024-03-10');

INSERT INTO satis_yonetimi (tarih, musteri_adi, miktar, urun) VALUES ('2024-06-01', 'Müşteri A', 1500.00, 'Ürün X');
INSERT INTO satis_yonetimi (tarih, musteri_adi, miktar, urun) VALUES ('2024-06-02', 'Müşteri B', 2000.00, 'Ürün Y');

INSERT INTO satin_alma_yonetimi (tarih, tedarikci_adi, miktar, urun) VALUES ('2024-06-01', 'Tedarikçi A', 1000.00, 'Hammadde X');
INSERT INTO satin_alma_yonetimi (tarih, tedarikci_adi, miktar, urun) VALUES ('2024-06-02', 'Tedarikçi B', 1500.00, 'Hammadde Y');

INSERT INTO stok_yonetimi (urun_adi, miktar, konum) VALUES ('Ürün X', 100, 'Depo A');
INSERT INTO stok_yonetimi (urun_adi, miktar, konum) VALUES ('Ürün Y', 200, 'Depo B');

INSERT INTO uretim_planlama (urun_adi, planlanan_miktar, baslangic_tarihi, bitis_tarihi) VALUES ('Ürün X', 500, '2024-06-01', '2024-06-10');
INSERT INTO uretim_planlama (urun_adi, planlanan_miktar, baslangic_tarihi, bitis_tarihi) VALUES ('Ürün Y', 300, '2024-06-05', '2024-06-15');

INSERT INTO musteri_iliskileri_yonetimi (musteri_adi, iletisim_tarihi, iletisim_yontemi, notlar) VALUES ('Müşteri A', '2024-06-01', 'Telefon', 'Yeni ürün hakkında bilgi verildi.');
INSERT INTO musteri_iliskileri_yonetimi (musteri_adi, iletisim_tarihi, iletisim_yontemi, notlar) VALUES ('Müşteri B', '2024-06-02', 'E-posta', 'Teklif gönderildi.');

INSERT INTO proje_yonetimi (proje_adi, baslangic_tarihi, bitis_tarihi, butce) VALUES ('Proje A', '2024-06-01', '2024-12-31', 100000.00);
INSERT INTO proje_yonetimi (proje_adi, baslangic_tarihi, bitis_tarihi, butce) VALUES ('Proje B', '2024-07-01', '2025-01-31', 200000.00);
