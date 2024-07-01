CREATE DATABASE IF NOT EXISTS erp_db;
USE erp_db;

CREATE TABLE IF NOT EXISTS musteriler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad_soyad VARCHAR(255) NOT NULL,
    email VARCHAR(255),
    telefon VARCHAR(20),
    adres VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS insan_kaynaklari (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ad_soyad VARCHAR(255) NOT NULL,
    pozisyon VARCHAR(255) NOT NULL,
    departman VARCHAR(255) NOT NULL,
    maas DECIMAL(10, 2) NOT NULL,
    ise_giris_tarihi DATE NOT NULL
);

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

CREATE TABLE IF NOT EXISTS performans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    calisan_id INT NOT NULL,
    tarih DATE NOT NULL,
    performans_puani INT NOT NULL,
    FOREIGN KEY (calisan_id) REFERENCES insan_kaynaklari(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS devamlilik (
    id INT AUTO_INCREMENT PRIMARY KEY,
    calisan_id INT NOT NULL,
    tarih DATE NOT NULL,
    durum ENUM('izin', 'devamsizlik', 'calisma') NOT NULL,
    FOREIGN KEY (calisan_id) REFERENCES insan_kaynaklari(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS urunler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    urun_adi VARCHAR(255) NOT NULL,
    urun_aciklamasi TEXT,
    fiyat DECIMAL(10, 2) NOT NULL,
    stok INT NOT NULL,
    urun_fotografi VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS satislar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    urun_id INT NOT NULL,
    miktar INT NOT NULL,
    toplam_fiyat DECIMAL(10, 2) NOT NULL,
    satis_tarihi DATE NOT NULL,
    FOREIGN KEY (urun_id) REFERENCES urunler(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS tedarikciler (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tedarikci_adi VARCHAR(255) NOT NULL,
    tedarikci_iletisim VARCHAR(255) NOT NULL,
    tedarikci_adresi VARCHAR(255) NOT NULL,
    tedarikci_telefonu VARCHAR(20) NOT NULL,
    tedarikci_eposta VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS satin_alma_siparisleri (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tedarikci_id INT NOT NULL,
    urun_adi VARCHAR(255) NOT NULL,
    miktar INT NOT NULL,
    birim_fiyat DECIMAL(10, 2) NOT NULL,
    toplam_fiyat DECIMAL(10, 2) NOT NULL,
    siparis_tarihi DATE NOT NULL,
    teslim_tarihi DATE,
    durum ENUM('Beklemede', 'Tamamlandı', 'İptal Edildi') NOT NULL,
    FOREIGN KEY (tedarikci_id) REFERENCES tedarikciler(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    customer_id INT NOT NULL,
    employee_id INT NOT NULL,
    description TEXT NOT NULL,
    status ENUM('Açık', 'Kapalı', 'Beklemede') NOT NULL DEFAULT 'Açık',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES musteriler(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES insan_kaynaklari(id) ON DELETE CASCADE
);





INSERT INTO transactions (tarih, tur, miktar, aciklama, vergi_orani, para_birimi) VALUES ('2024-06-01', 'gelir', 1000.00, 'Müşteri ödemesi', 18.00, 'TRY');
INSERT INTO transactions (tarih, tur, miktar, aciklama, vergi_orani, para_birimi) VALUES ('2024-06-02', 'gider', 200.00, 'Ofis malzemeleri', 18.00, 'TRY');

INSERT INTO finance_management (tarih, tur, miktar, aciklama, vergi_orani, para_birimi) VALUES ('2024-06-01', 'gelir', 1500.00, 'Satış geliri', 18.00, 'TRY');
INSERT INTO finance_management (tarih, tur, miktar, aciklama, vergi_orani, para_birimi) VALUES ('2024-06-02', 'gider', 500.00, 'Kira ödemesi', 18.00, 'TRY');

INSERT INTO muhasebe (tarih, tur, miktar, aciklama) VALUES ('2024-06-01', 'borc', 1000.00, 'Banka hesabi borcu');
INSERT INTO muhasebe (tarih, tur, miktar, aciklama) VALUES ('2024-06-02', 'alacak', 500.00, 'Müşteri ödemesi');

INSERT INTO insan_kaynaklari (ad_soyad, pozisyon, departman, maas, ise_giris_tarihi) VALUES ('Ali Veli', 'Yazılım Mühendisi', 'IT', 6000.00, '2024-01-15');
INSERT INTO insan_kaynaklari (ad_soyad, pozisyon, departman, maas, ise_giris_tarihi) VALUES ('Ayşe Yılmaz', 'Muhasebeci', 'Finance', 4000.00, '2024-03-10');

INSERT INTO performans (calisan_id, tarih, performans_puani) VALUES (1, '2024-06-01', 85);
INSERT INTO performans (calisan_id, tarih, performans_puani) VALUES (2, '2024-06-01', 90);

INSERT INTO devamlilik (calisan_id, tarih, durum) VALUES (1, '2024-06-01', 'calisma');
INSERT INTO devamlilik (calisan_id, tarih, durum) VALUES (2, '2024-06-01', 'izin');

INSERT INTO musteriler (ad_soyad, email, telefon, adres) VALUES ('ilyas', 'ilyas.com', '555-555-5555', 'izmir St');
INSERT INTO musteriler (ad_soyad, email, telefon, adres) VALUES ('doguhan', 'doguhan.com', '555-555-5556', 'buca St');

