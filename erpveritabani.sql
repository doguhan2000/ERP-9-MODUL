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
    departman VARCHAR(255) NOT NULL,
    maas DECIMAL(10, 2) NOT NULL,
    ise_giris_tarihi DATE NOT NULL
);

CREATE TABLE IF NOT EXISTS performans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    calisan_id INT NOT NULL,
    tarih DATE NOT NULL,
    performans_puani INT NOT NULL,
    FOREIGN KEY (calisan_id) REFERENCES insan_kaynaklari(id)
);

CREATE TABLE IF NOT EXISTS devamlilik (
    id INT AUTO_INCREMENT PRIMARY KEY,
    calisan_id INT NOT NULL,
    tarih DATE NOT NULL,
    durum ENUM('izin', 'devamsizlik', 'calisma') NOT NULL,
    FOREIGN KEY (calisan_id) REFERENCES insan_kaynaklari(id)
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
