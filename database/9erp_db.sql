CREATE DATABASE IF NOT EXISTS 9erp_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE 9erp_db;

CREATE TABLE IF NOT EXISTS users (
id INT PRIMARY KEY AUTO_INCREMENT,
username VARCHAR(50) NOT NULL UNIQUE,
password VARCHAR(255) NOT NULL,
email VARCHAR(100) NOT NULL UNIQUE,
role ENUM('admin', 'accountant', 'hr', 'manager') NOT NULL,
status ENUM('active', 'inactive') DEFAULT 'active',
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO users (username, password, email, role) VALUES
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@9erp.com', 'admin');

CREATE TABLE IF NOT EXISTS departments (
id INT AUTO_INCREMENT PRIMARY KEY,
name VARCHAR(100) NOT NULL,
description TEXT,
parent_id INT,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (parent_id) REFERENCES departments(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS positions (
id INT AUTO_INCREMENT PRIMARY KEY,
title VARCHAR(100) NOT NULL,
department_id INT,
level INT,
description TEXT,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS employees (
id INT AUTO_INCREMENT PRIMARY KEY,
employee_no VARCHAR(20) UNIQUE NOT NULL,
first_name VARCHAR(50) NOT NULL,
last_name VARCHAR(50) NOT NULL,
email VARCHAR(100) UNIQUE NOT NULL,
phone VARCHAR(20),
photo VARCHAR(255),
department_id INT,
position_id INT,
manager_id INT,
hire_date DATE,
salary DECIMAL(10,2),
status ENUM('active', 'passive', 'on_leave') DEFAULT 'active',
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
FOREIGN KEY (position_id) REFERENCES positions(id) ON DELETE SET NULL,
FOREIGN KEY (manager_id) REFERENCES employees(id) ON DELETE SET NULL
);

-- Zaman yönetimi için yeni tablo
CREATE TABLE IF NOT EXISTS time_logs (
id INT AUTO_INCREMENT PRIMARY KEY,
employee_id INT NOT NULL,
check_in DATETIME,
check_out DATETIME,
status ENUM('normal', 'late', 'early_leave', 'absent') DEFAULT 'normal',
total_hours DECIMAL(5,2),
note TEXT,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
);

-- İşe alım için yeni tablolar
CREATE TABLE IF NOT EXISTS job_postings (
id INT AUTO_INCREMENT PRIMARY KEY,
department_id INT,
position_id INT,
title VARCHAR(100) NOT NULL,
description TEXT,
requirements TEXT,
min_experience INT,
min_salary DECIMAL(10,2),
max_salary DECIMAL(10,2),
english_level ENUM('beginner', 'intermediate', 'advanced', 'native') NOT NULL,
status ENUM('active', 'closed') DEFAULT 'active',
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL,
FOREIGN KEY (position_id) REFERENCES positions(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS candidates (
id INT AUTO_INCREMENT PRIMARY KEY,
job_posting_id INT,
first_name VARCHAR(50) NOT NULL,
last_name VARCHAR(50) NOT NULL,
email VARCHAR(100) NOT NULL,
phone VARCHAR(20),
experience_years INT,
english_level ENUM('beginner', 'intermediate', 'advanced', 'native') NOT NULL,
expected_salary DECIMAL(10,2),
cv_file VARCHAR(255),
status ENUM('new', 'reviewing', 'interviewed', 'offered', 'rejected', 'hired') DEFAULT 'new',
notes TEXT,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (job_posting_id) REFERENCES job_postings(id) ON DELETE SET NULL
);

-- CV Havuzu için tablolar
CREATE TABLE IF NOT EXISTS cv_pool (
id INT AUTO_INCREMENT PRIMARY KEY,
first_name VARCHAR(50) NOT NULL,
last_name VARCHAR(50) NOT NULL,
email VARCHAR(100) NOT NULL,
phone VARCHAR(20),
department_id INT,
experience_years INT,
english_level ENUM('beginner', 'intermediate', 'advanced', 'native') NOT NULL,
education_level ENUM('high_school', 'associate', 'bachelor', 'master', 'phd') NOT NULL,
skills TEXT,
cv_file VARCHAR(255) NOT NULL,
status ENUM('active', 'archived', 'hired') DEFAULT 'active',
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
);

-- CV Etiketleri için tablo
CREATE TABLE IF NOT EXISTS cv_tags (
id INT AUTO_INCREMENT PRIMARY KEY,
cv_id INT,
tag_name VARCHAR(50) NOT NULL,
FOREIGN KEY (cv_id) REFERENCES cv_pool(id) ON DELETE CASCADE
);

-- CV Notları için tablo
CREATE TABLE IF NOT EXISTS cv_notes (
id INT AUTO_INCREMENT PRIMARY KEY,
cv_id INT,
note_text TEXT NOT NULL,
created_by INT,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (cv_id) REFERENCES cv_pool(id) ON DELETE CASCADE,
FOREIGN KEY (created_by) REFERENCES employees(id) ON DELETE SET NULL
);

-- Performans değerlendirme tablosu
CREATE TABLE performance_reviews (
id INT AUTO_INCREMENT PRIMARY KEY,
employee_id INT NOT NULL,
score DECIMAL(4,2) NOT NULL CHECK (score >= 0 AND score <= 10),
review_text TEXT NOT NULL,
goals TEXT NOT NULL,
review_date DATE NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (employee_id) REFERENCES employees(id)
);

-- Bordro tablosu
CREATE TABLE IF NOT EXISTS payrolls (
id INT AUTO_INCREMENT PRIMARY KEY,
employee_id INT NOT NULL,
period_month INT NOT NULL,
period_year INT NOT NULL,
base_salary DECIMAL(10,2) NOT NULL,
overtime_hours DECIMAL(5,2) DEFAULT 0,
overtime_payment DECIMAL(10,2) DEFAULT 0,
bonus DECIMAL(10,2) DEFAULT 0,
meal_allowance DECIMAL(10,2) DEFAULT 0,
transport_allowance DECIMAL(10,2) DEFAULT 0,
gross_salary DECIMAL(10,2) NOT NULL,
income_tax DECIMAL(10,2) NOT NULL,
stamp_tax DECIMAL(10,2) NOT NULL,
insurance_deduction DECIMAL(10,2) NOT NULL,
unemployment_insurance DECIMAL(10,2) NOT NULL,
net_salary DECIMAL(10,2) NOT NULL,
payment_status ENUM('pending', 'paid') DEFAULT 'pending',
payment_date DATE,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
);

-- Avans tablosu
CREATE TABLE IF NOT EXISTS salary_advances (
id INT AUTO_INCREMENT PRIMARY KEY,
employee_id INT NOT NULL,
amount DECIMAL(10,2) NOT NULL,
request_date DATE NOT NULL,
status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
approved_by INT,
approval_date DATE,
payment_date DATE,
description TEXT,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
FOREIGN KEY (approved_by) REFERENCES employees(id) ON DELETE SET NULL
);

-- Özlük belgeleri için tablo
CREATE TABLE IF NOT EXISTS employee_documents (
id INT AUTO_INCREMENT PRIMARY KEY,
employee_id INT NOT NULL,
document_type ENUM('is_sozlesmesi', 'saglik_raporu', 'diploma', 'sertifika', 'kimlik', 'adli_sicil', 'ikametgah', 'diger') NOT NULL,
file_path VARCHAR(255) NOT NULL,
notes TEXT,
upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
);

INSERT INTO departments (name, description) VALUES
('Yönetim', 'Üst yönetim departmanı'),
('İnsan Kaynakları', 'İK departmanı'),
('Yazılım', 'Yazılım geliştirme departmanı'),
('Muhasebe', 'Muhasebe departmanı'),
('Satış', 'Satış departmanı');

INSERT INTO positions (title, department_id, level) VALUES
('Genel Müdür', 1, 1),
('İK Müdürü', 2, 2),
('Yazılım Müdürü', 3, 2),
('Kıdemli Yazılımcı', 3, 3),
('İK Uzmanı', 2, 3),
('Muhasebe Müdürü', 4, 2),
('Satış Müdürü', 5, 2);

INSERT INTO employees (employee_no, first_name, last_name, email, department_id, position_id, salary) VALUES
('EMP001', 'Ahmet', 'Yılmaz', 'ahmet.yilmaz@9erp.com', 1, 1, 10000.00),
('EMP002', 'Ayşe', 'Demir', 'ayse.demir@9erp.com', 2, 2, 8000.00),
('EMP003', 'Mehmet', 'Kaya', 'mehmet.kaya@9erp.com', 3, 3, 9000.00),
('EMP004', 'Fatma', 'Şahin', 'fatma.sahin@9erp.com', 2, 5, 7000.00),
('EMP005', 'Ali', 'Öztürk', 'ali.ozturk@9erp.com', 3, 4, 6000.00);

-- Örnek zaman kayıtları
INSERT INTO time_logs (employee_id, check_in, check_out, status, total_hours) VALUES
(1, '2025-01-10 08:00:00', '2025-01-10 17:00:00', 'normal', 9.00),
(2, '2025-01-10 08:30:00', '2025-01-10 17:00:00', 'late', 8.50),
(3, '2025-01-10 08:00:00', '2025-01-10 16:30:00', 'early_leave', 8.50),
(4, '2025-01-10 08:00:00', '2025-01-10 17:00:00', 'normal', 9.00),
(5, '2025-01-10 08:15:00', '2025-01-10 17:00:00', 'normal', 8.75);

-- Örnek iş ilanları
INSERT INTO job_postings (department_id, position_id, title, description, requirements, min_experience, min_salary, max_salary, english_level) VALUES
(3, 4, 'Kıdemli Yazılım Geliştirici', 'Web uygulamaları geliştirme konusunda deneyimli yazılımcı arıyoruz.', 'PHP, MySQL, JavaScript konularında deneyim\nFramework bilgisi (Laravel, Symfony)\nVersiyon kontrol sistemleri (Git)', 3, 15000.00, 25000.00, 'intermediate'),
(2, 5, 'İK Uzmanı', 'İnsan kaynakları süreçlerini yönetecek uzman arıyoruz.', 'İK süreçlerine hakim\nİş hukuku bilgisi\nMS Office kullanımı', 2, 12000.00, 18000.00, 'intermediate'),
(5, 7, 'Satış Müdürü', 'Satış ekibini yönetecek deneyimli yönetici arıyoruz.', 'Satış ve pazarlama deneyimi\nEkip yönetimi tecrübesi\nMüşteri ilişkileri yönetimi', 5, 20000.00, 35000.00, 'advanced');

-- Örnek adaylar
INSERT INTO candidates (job_posting_id, first_name, last_name, email, phone, experience_years, english_level, expected_salary, status) VALUES
(1, 'Mehmet', 'Yıldız', 'mehmet.yildiz@email.com', '5551234567', 4, 'advanced', 22000.00, 'new'),
(1, 'Ayşe', 'Kara', 'ayse.kara@email.com', '5559876543', 3, 'intermediate', 20000.00, 'reviewing'),
(2, 'Ali', 'Demir', 'ali.demir@email.com', '5553334444', 2, 'intermediate', 15000.00, 'interviewed');

-- ... (mevcut tablolar devam ediyor)

-- Muhasebe modülü için tablolar
CREATE TABLE IF NOT EXISTS account_chart (
id INT PRIMARY KEY AUTO_INCREMENT,
code VARCHAR(20) NOT NULL UNIQUE,
name VARCHAR(255) NOT NULL,
type ENUM('asset', 'liability', 'equity', 'income', 'expense') NOT NULL,
parent_id INT,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (parent_id) REFERENCES account_chart(id)
);

CREATE TABLE IF NOT EXISTS accounting_vouchers (
id INT PRIMARY KEY AUTO_INCREMENT,
voucher_no VARCHAR(50) NOT NULL UNIQUE,
voucher_date DATE NOT NULL,
description TEXT,
type ENUM('rent', 'tax', 'insurance', 'utility', 'salary', 'other') NOT NULL,
status ENUM('draft', 'posted', 'cancelled') DEFAULT 'draft',
total_amount DECIMAL(15,2) NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
created_by INT,
FOREIGN KEY (created_by) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS voucher_details (
id INT PRIMARY KEY AUTO_INCREMENT,
voucher_id INT NOT NULL,
account_id INT NOT NULL,
description TEXT,
debit DECIMAL(15,2) DEFAULT 0,
credit DECIMAL(15,2) DEFAULT 0,
FOREIGN KEY (voucher_id) REFERENCES accounting_vouchers(id),
FOREIGN KEY (account_id) REFERENCES account_chart(id)
);

-- Temel hesap planı kayıtları
INSERT IGNORE INTO account_chart (code, name, type) VALUES
('100', 'KASA', 'asset'),
('102', 'BANKALAR', 'asset'),
('120', 'ALICILAR', 'asset'),
('320', 'SATICILAR', 'liability'),
('335', 'PERSONELE BORÇLAR', 'liability'),
('600', 'YURTİÇİ SATIŞLAR', 'income'),
('770', 'GENEL YÖNETİM GİDERLERİ', 'expense'),
('740', 'HİZMET ÜRETİM MALİYETİ', 'expense');

-- Örnek bordro kayıtları
INSERT INTO payrolls (
employee_id, period_month, period_year,
base_salary, overtime_hours, overtime_payment,
bonus, meal_allowance, transport_allowance,
gross_salary, income_tax, stamp_tax,
insurance_deduction, unemployment_insurance, net_salary,
payment_status, payment_date
) VALUES
(1, MONTH(CURRENT_DATE), YEAR(CURRENT_DATE),
10000.00, 10, 500.00,
1000.00, 500.00, 300.00,
12300.00, 1845.00, 93.35,
1722.00, 123.00, 8516.65,
'paid', CURRENT_DATE),

(2, MONTH(CURRENT_DATE), YEAR(CURRENT_DATE),
8000.00, 5, 200.00,
800.00, 500.00, 300.00,
9800.00, 1470.00, 74.48,
1372.00, 98.00, 6785.52,
'paid', CURRENT_DATE),

(3, MONTH(CURRENT_DATE), YEAR(CURRENT_DATE),
9000.00, 8, 360.00,
900.00, 500.00, 300.00,
11060.00, 1659.00, 83.96,
1548.40, 110.60, 7658.04,
'paid', CURRENT_DATE),

(4, MONTH(CURRENT_DATE), YEAR(CURRENT_DATE),
7000.00, 0, 0.00,
700.00, 500.00, 300.00,
8500.00, 1275.00, 64.60,
1190.00, 85.00, 5885.40,
'paid', CURRENT_DATE),

(5, MONTH(CURRENT_DATE), YEAR(CURRENT_DATE),
6000.00, 15, 450.00,
600.00, 500.00, 300.00,
7850.00, 1177.50, 59.66,
1099.00, 78.50, 5435.34,
'paid', CURRENT_DATE);

ALTER TABLE accounting_vouchers
MODIFY COLUMN type ENUM('rent', 'tax', 'insurance', 'utility', 'salary', 'other') NOT NULL;

CREATE TABLE IF NOT EXISTS monthly_expenses (
id INT PRIMARY KEY AUTO_INCREMENT,
expense_type ENUM('rent', 'tax', 'insurance', 'utility', 'salary', 'other') NOT NULL,
period_month INT NOT NULL,
period_year INT NOT NULL,
total_amount DECIMAL(15,2) DEFAULT 0,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
UNIQUE KEY expense_period (expense_type, period_month, period_year)
);

-- Finans modülü için tablolar
CREATE TABLE IF NOT EXISTS bank_accounts (
id INT PRIMARY KEY AUTO_INCREMENT,
account_name VARCHAR(255) NOT NULL,
bank_name VARCHAR(255) NOT NULL,
branch_name VARCHAR(255),
account_no VARCHAR(50),
iban VARCHAR(50) UNIQUE,
currency ENUM('TRY', 'USD', 'EUR') DEFAULT 'TRY',
current_balance DECIMAL(15,2) DEFAULT 0,
status ENUM('active', 'passive') DEFAULT 'active',
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS bank_transactions (
id INT PRIMARY KEY AUTO_INCREMENT,
bank_account_id INT NOT NULL,
transaction_type ENUM('deposit', 'withdrawal', 'transfer') NOT NULL,
amount DECIMAL(15,2) NOT NULL,
description TEXT,
transaction_date DATE NOT NULL,
related_voucher_id INT,
status ENUM('pending', 'completed', 'cancelled') DEFAULT 'completed',
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (bank_account_id) REFERENCES bank_accounts(id),
FOREIGN KEY (related_voucher_id) REFERENCES accounting_vouchers(id)
);

CREATE TABLE IF NOT EXISTS payments (
id INT PRIMARY KEY AUTO_INCREMENT,
payment_type ENUM('depo', 'tedarik', 'nakliye', 'bakim', 'diger') NOT NULL,
amount DECIMAL(15,2) NOT NULL,
due_date DATE NOT NULL,
payment_date DATE,
description TEXT,
status ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending',
bank_account_id INT,
related_voucher_id INT,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (bank_account_id) REFERENCES bank_accounts(id),
FOREIGN KEY (related_voucher_id) REFERENCES accounting_vouchers(id)
);

CREATE TABLE IF NOT EXISTS collections (
id INT PRIMARY KEY AUTO_INCREMENT,
customer_name VARCHAR(255) NOT NULL,
amount DECIMAL(15,2) NOT NULL,
due_date DATE NOT NULL,
collection_date DATE,
description TEXT,
status ENUM('pending', 'collected', 'cancelled') DEFAULT 'pending',
bank_account_id INT,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (bank_account_id) REFERENCES bank_accounts(id)
);

-- Örnek banka hesapları
INSERT INTO bank_accounts (account_name, bank_name, branch_name, account_no, iban, currency) VALUES
('Ana Hesap TL', 'Ziraat Bankası', 'Merkez', '12345678', 'TR330006100519786457841326', 'TRY'),
('Dolar Hesabı', 'İş Bankası', 'Merkez', '98765432', 'TR770006200519786457841327', 'USD'),
('Euro Hesabı', 'Garanti', 'Merkez', '56789012', 'TR660006300519786457841328', 'EUR');

-- Gelirler tablosu
CREATE TABLE IF NOT EXISTS incomes (
id INT PRIMARY KEY AUTO_INCREMENT,
income_type ENUM('satis', 'hizmet', 'diger') NOT NULL,
amount DECIMAL(15,2) NOT NULL,
customer_name VARCHAR(255) NOT NULL,
due_date DATE NOT NULL,
description TEXT,
status ENUM('pending', 'collected', 'cancelled') DEFAULT 'pending',
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Giderler/Ödemeler tablosu
DROP TABLE IF EXISTS payments;
CREATE TABLE IF NOT EXISTS payments (
id INT PRIMARY KEY AUTO_INCREMENT,
payment_type ENUM('kira', 'fatura', 'personel', 'malzeme', 'diger') NOT NULL,
amount DECIMAL(15,2) NOT NULL,
supplier_name VARCHAR(255) NOT NULL,
due_date DATE NOT NULL,
description TEXT,
status ENUM('pending', 'paid', 'cancelled') DEFAULT 'pending',
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Banka hesapları tablosu (eğer yoksa)
CREATE TABLE IF NOT EXISTS bank_accounts (
id INT PRIMARY KEY AUTO_INCREMENT,
bank_name VARCHAR(255) NOT NULL,
account_name VARCHAR(255) NOT NULL,
account_number VARCHAR(50),
iban VARCHAR(50) UNIQUE,
currency ENUM('TRY', 'USD', 'EUR') NOT NULL,
current_balance DECIMAL(15,2) DEFAULT 0,
status ENUM('active', 'passive') DEFAULT 'active',
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Örnek banka hesapları (eğer yoksa)
INSERT IGNORE INTO bank_accounts (bank_name, account_name, iban, currency, current_balance) VALUES
('Ziraat Bankası', 'Ana Hesap TL', 'TR330006100519786457841326', 'TRY', 0),
('İş Bankası', 'Dolar Hesabı', 'TR770006200519786457841327', 'USD', 0),
('Garanti', 'Euro Hesabı', 'TR660006300519786457841328', 'EUR', 0);

-- Üretim Planlama tabloları
CREATE TABLE IF NOT EXISTS workstations (
id INT PRIMARY KEY AUTO_INCREMENT,
name VARCHAR(100) NOT NULL,
description TEXT,
capacity INT NOT NULL,
status ENUM('active', 'maintenance', 'inactive') DEFAULT 'active',
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS production_orders (
id INT PRIMARY KEY AUTO_INCREMENT,
order_code VARCHAR(50) UNIQUE NOT NULL,
customer_name VARCHAR(100) NOT NULL,
company_name VARCHAR(100),
contact_email VARCHAR(100),
contact_phone VARCHAR(20),
project_type ENUM('e-commerce', 'corporate', 'blog', 'custom') NOT NULL,
features TEXT,
tech_stack TEXT,
start_date DATE NOT NULL,
due_date DATE NOT NULL,
priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
workstation_id INT,
notes TEXT,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (workstation_id) REFERENCES workstations(id)
);

-- Proje-Personel ilişki tablosu
CREATE TABLE IF NOT EXISTS project_assignments (
id INT PRIMARY KEY AUTO_INCREMENT,
project_id INT NOT NULL,
employee_id INT NOT NULL,
estimated_days INT NOT NULL,
start_date DATE NOT NULL,
end_date DATE NOT NULL,
status ENUM('active', 'completed', 'delayed') DEFAULT 'active',
notes TEXT,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (project_id) REFERENCES production_orders(id) ON DELETE CASCADE,
FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
);

-- İş İstasyonlarını web geliştirme departmanlarına güncelle
INSERT INTO workstations (name, description, capacity) VALUES
('Frontend Geliştirme', 'Frontend geliştirme süreçleri', 160),
('Backend Geliştirme', 'Backend geliştirme süreçleri', 160),
('UI/UX Tasarım', 'Arayüz tasarımı ve kullanıcı deneyimi', 120),
('Test ve Kalite Kontrol', 'Test süreçleri ve kalite kontrol', 100);

-- production_orders tablosuna progress ve updated_at kolonları ekle
ALTER TABLE production_orders
ADD COLUMN progress INT DEFAULT 0,
ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Proje durum logları tablosu
CREATE TABLE IF NOT EXISTS project_status_logs (
id INT PRIMARY KEY AUTO_INCREMENT,
project_id INT NOT NULL,
status VARCHAR(20) NOT NULL,
progress INT DEFAULT 0,
notes TEXT,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (project_id) REFERENCES production_orders(id) ON DELETE CASCADE
);

-- Proje ilerleme logları tablosu
CREATE TABLE IF NOT EXISTS project_progress_logs (
id INT PRIMARY KEY AUTO_INCREMENT,
project_id INT NOT NULL,
progress INT NOT NULL,
notes TEXT,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (project_id) REFERENCES production_orders(id) ON DELETE CASCADE
);

-- Departments tablosuna status kolonu ekle
ALTER TABLE departments
ADD COLUMN status ENUM('active', 'passive') DEFAULT 'active';

-- Mevcut departmanları aktif olarak işaretle
UPDATE departments SET status = 'active';

-- Proje raporları için tablo
CREATE TABLE IF NOT EXISTS project_reports (
id INT PRIMARY KEY AUTO_INCREMENT,
project_id INT NOT NULL,
report_type ENUM('daily', 'weekly', 'monthly', 'completion') NOT NULL,
start_date DATE NOT NULL,
end_date DATE NOT NULL,
total_hours INT NOT NULL,
completed_tasks INT DEFAULT 0,
pending_tasks INT DEFAULT 0,
challenges TEXT,
solutions TEXT,
next_steps TEXT,
created_by INT NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (project_id) REFERENCES production_orders(id),
FOREIGN KEY (created_by) REFERENCES employees(id)
);

-- Proje maliyet takibi için tablo
CREATE TABLE IF NOT EXISTS project_costs (
id INT PRIMARY KEY AUTO_INCREMENT,
project_id INT NOT NULL,
cost_type ENUM('labor', 'software', 'hosting', 'other') NOT NULL,
amount DECIMAL(10,2) NOT NULL,
description TEXT,
date DATE NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (project_id) REFERENCES production_orders(id)
);

-- Stok kategorileri tablosu
CREATE TABLE IF NOT EXISTS inventory_categories (
id INT PRIMARY KEY AUTO_INCREMENT,
name VARCHAR(100) NOT NULL,
description TEXT,
status ENUM('active', 'passive') DEFAULT 'active',
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Stok ürünleri tablosu
CREATE TABLE IF NOT EXISTS inventory_items (
id INT PRIMARY KEY AUTO_INCREMENT,
category_id INT NOT NULL,
item_code VARCHAR(50) UNIQUE NOT NULL,
name VARCHAR(255) NOT NULL,
description TEXT,
version VARCHAR(50),
license_type ENUM('perpetual', 'subscription', 'opensource') DEFAULT 'perpetual',
purchase_price DECIMAL(10,2),
sale_price DECIMAL(10,2),
quantity INT DEFAULT 0,
min_quantity INT DEFAULT 1,
supplier_info TEXT,
status ENUM('active', 'passive') DEFAULT 'active',
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (category_id) REFERENCES inventory_categories(id)
);

-- Örnek kategoriler
INSERT INTO inventory_categories (name, description) VALUES
('Hazır Yazılım Paketleri', 'E-ticaret, CRM, ERP gibi hazır yazılım çözümleri'),
('Lisanslar', 'Yazılım lisansları ve abonelikler'),
('Hosting/Domain Paketleri', 'Hosting ve domain hizmetleri'),
('Yazılım Bileşenleri', 'Eklentiler, modüller ve API hizmetleri'),
('Tema/Şablonlar', 'Hazır temalar ve tasarım şablonları');

-- Tedarikçiler tablosu
CREATE TABLE IF NOT EXISTS suppliers (
id INT PRIMARY KEY AUTO_INCREMENT,
name VARCHAR(255) NOT NULL,
contact_person VARCHAR(100),
phone VARCHAR(20),
email VARCHAR(100),
address TEXT,
tax_number VARCHAR(50),
status ENUM('active', 'passive') DEFAULT 'active',
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tedarikçi ürünleri tablosu
CREATE TABLE IF NOT EXISTS supplier_products (
id INT PRIMARY KEY AUTO_INCREMENT,
supplier_id INT,
inventory_item_id INT,
price DECIMAL(10,2) NOT NULL,
currency ENUM('TRY', 'USD', 'EUR') DEFAULT 'TRY',
delivery_time INT DEFAULT 1, -- Gün cinsinden teslimat süresi
min_order_quantity INT DEFAULT 1,
status ENUM('active', 'passive') DEFAULT 'active',
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (supplier_id) REFERENCES suppliers(id),
FOREIGN KEY (inventory_item_id) REFERENCES inventory_items(id)
);

-- Örnek tedarikçiler
INSERT INTO suppliers (name, contact_person, phone, email) VALUES
('Yazılım A.Ş.', 'Ahmet Yılmaz', '0212 555 11 11', 'info@yazilim.com'),
('Dijital Çözümler', 'Mehmet Demir', '0216 444 22 22', 'satis@dijital.com'),
('Tech Market', 'Ayşe Kaya', '0312 333 33 33', 'info@techmarket.com'),
('Lisans Dünyası', 'Ali Öz', '0232 222 44 44', 'satis@lisans.com'),
('Host Center', 'Zeynep Ak', '0242 111 55 55', 'info@hostcenter.com'),
('API Solutions', 'Can Er', '0224 666 66 66', 'info@apisolutions.com'),
('Theme Store', 'Elif Şen', '0258 777 77 77', 'satis@themestore.com'),
('Software Plus', 'Murat Güç', '0352 888 88 88', 'info@softwareplus.com');

-- Örnek ürünler
INSERT INTO inventory_items (category_id, item_code, name, description, version, license_type, purchase_price, sale_price, quantity, min_quantity) VALUES
-- Hazır Yazılım Paketleri
(1, 'SW001', 'OpenCart E-ticaret', 'Açık kaynak e-ticaret yazılımı', '4.0.2', 'opensource', 0, 0, 0, 1),
(1, 'SW002', 'WordPress CMS', 'İçerik yönetim sistemi', '6.4', 'opensource', 0, 0, 0, 1),
(1, 'SW003', 'PrestaShop', 'E-ticaret yazılımı', '8.1', 'opensource', 0, 0, 0, 1),

-- Lisanslar
(2, 'LIC001', 'Windows Server 2022', 'Sunucu işletim sistemi', '2022', 'perpetual', 899, 999, 0, 2),
(2, 'LIC002', 'Adobe Creative Cloud', 'Tasarım yazılımları paketi', '2024', 'subscription', 599, 699, 0, 3),
(2, 'LIC003', 'Microsoft 365', 'Ofis yazılımları paketi', '2024', 'subscription', 199, 299, 0, 5),

-- Hosting/Domain
(3, 'HST001', 'Business Hosting', 'İşletme hosting paketi', '2024', 'subscription', 299, 399, 0, 10),
(3, 'HST002', 'VPS Sunucu', 'Sanal özel sunucu', '2024', 'subscription', 499, 599, 0, 5),
(3, 'HST003', 'Domain Name', '.com domain kaydı', '2024', 'subscription', 99, 149, 0, 20),

-- Yazılım Bileşenleri
(4, 'CMP001', 'Payment Gateway', 'Ödeme sistemi entegrasyonu', '2.1', 'perpetual', 399, 499, 0, 3),
(4, 'CMP002', 'SEO Plugin', 'Arama motoru optimizasyonu eklentisi', '3.0', 'subscription', 149, 199, 0, 5),
(4, 'CMP003', 'Security Suite', 'Güvenlik yazılımı paketi', '2024', 'subscription', 299, 399, 0, 4),

-- Tema/Şablonlar
(5, 'TPL001', 'E-ticaret Teması', 'Responsive e-ticaret teması', '2.0', 'perpetual', 79, 99, 0, 10),
(5, 'TPL002', 'Kurumsal Tema', 'Kurumsal website teması', '1.5', 'perpetual', 89, 119, 0, 8),
(5, 'TPL003', 'Blog Teması', 'Profesyonel blog teması', '3.0', 'perpetual', 69, 89, 0, 12);

-- Hazır Yazılım Paketleri için fiyat güncellemesi
UPDATE inventory_items
SET purchase_price = CASE
    WHEN item_code = 'SW001' THEN 2999
    WHEN item_code = 'SW002' THEN 1999
    WHEN item_code = 'SW003' THEN 2499
END,
sale_price = CASE
    WHEN item_code = 'SW001' THEN 3499
    WHEN item_code = 'SW002' THEN 2499
    WHEN item_code = 'SW003' THEN 2999
END
WHERE category_id = 1;

-- Tedarikçi ürünlerini temizle ve yeniden ekle
DELETE FROM supplier_products;

-- Her ürün için 8 farklı tedarikçi ve fiyat ekle
INSERT INTO supplier_products (supplier_id, inventory_item_id, price, currency, delivery_time)
SELECT
s.id as supplier_id,
i.id as inventory_item_id,
CASE
    WHEN s.id = 1 THEN i.purchase_price * 0.95
    WHEN s.id = 2 THEN i.purchase_price * 0.98
    WHEN s.id = 3 THEN i.purchase_price * 1.02
    WHEN s.id = 4 THEN i.purchase_price * 0.97
    WHEN s.id = 5 THEN i.purchase_price * 1.05
    WHEN s.id = 6 THEN i.purchase_price * 0.99
    WHEN s.id = 7 THEN i.purchase_price * 1.03
    WHEN s.id = 8 THEN i.purchase_price * 0.96
END as price,
CASE
    WHEN s.id IN (1,3,5,7) THEN 'TRY'
    WHEN s.id IN (2,6,8) THEN 'USD'
    ELSE 'EUR'
END as currency,
FLOOR(1 + RAND() * 7) as delivery_time
FROM
inventory_items i
CROSS JOIN suppliers s
WHERE
i.status = 'active';

-- Satın alma işlemleri tablosu
CREATE TABLE IF NOT EXISTS purchase_transactions (
id INT PRIMARY KEY AUTO_INCREMENT,
inventory_item_id INT NOT NULL,
supplier_id INT NOT NULL,
quantity INT DEFAULT 1,
price DECIMAL(10,2) NOT NULL,
currency ENUM('TRY', 'USD', 'EUR') DEFAULT 'TRY',
purchase_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (inventory_item_id) REFERENCES inventory_items(id),
FOREIGN KEY (supplier_id) REFERENCES suppliers(id)
);

-- Önce müşteri grupları tablosunu oluştur
CREATE TABLE IF NOT EXISTS customer_groups (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    discount_rate DECIMAL(5,2) DEFAULT 0.00,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Müşteri gruplarını ekle
INSERT INTO customer_groups (name, description, discount_rate) VALUES
('Standart', 'Standart müşteriler', 0),
('Premium', 'Premium müşteriler', 5.00),
('VIP', 'VIP müşteriler', 10.00);

-- Sonra müşteriler tablosunu oluştur
DROP TABLE IF EXISTS customers;
CREATE TABLE IF NOT EXISTS customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    group_id INT,
    name VARCHAR(100) NOT NULL,
    company_name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20),
    address TEXT,
    tax_office VARCHAR(100),
    tax_number VARCHAR(20),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (group_id) REFERENCES customer_groups(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Müşteri verilerini ekle
INSERT INTO customers (group_id, name, company_name, email, phone, status) VALUES
(1, 'İlyas Demirelli', 'Demirelli Ltd', 'ilyasdemirelli68@gmail.com', '5551234567', 'active'),
(2, 'Ahmet Yılmaz', 'ABC Firma', 'ahmet@firma.com', '5559876543', 'active'),
(3, 'Mehmet Demir', 'XYZ Şirket', 'mehmet@sirket.com', '5553334444', 'active');

-- Satışlar tablosu
CREATE TABLE IF NOT EXISTS sales (
id INT PRIMARY KEY AUTO_INCREMENT,
customer_id INT NOT NULL,
total_amount DECIMAL(10,2) NOT NULL,
discount_rate DECIMAL(5,2) NOT NULL DEFAULT 0,
final_amount DECIMAL(10,2) NOT NULL,
status ENUM('pending', 'completed', 'cancelled') NOT NULL DEFAULT 'completed',
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
FOREIGN KEY (customer_id) REFERENCES customers(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Satış detayları tablosu
CREATE TABLE IF NOT EXISTS sale_items (
id INT PRIMARY KEY AUTO_INCREMENT,
sale_id INT NOT NULL,
inventory_item_id INT NOT NULL,
quantity INT NOT NULL,
unit_price DECIMAL(10,2) NOT NULL,
purchase_price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
total_price DECIMAL(10,2) NOT NULL,
created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
FOREIGN KEY (sale_id) REFERENCES sales(id),
FOREIGN KEY (inventory_item_id) REFERENCES inventory_items(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- İndeksler
CREATE INDEX idx_sales_customer ON sales(customer_id);
CREATE INDEX idx_sale_items_sale ON sale_items(sale_id);
CREATE INDEX idx_sale_items_item ON sale_items(inventory_item_id);

-- CRM Modülü için tablolar
-- Görevler tablosu
CREATE TABLE IF NOT EXISTS tasks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    type ENUM('meeting', 'call', 'visit', 'follow_up', 'other') NOT NULL,
    start_date DATETIME NOT NULL,
    due_date DATETIME NOT NULL,
    status ENUM('pending', 'in_progress', 'completed', 'deleted') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Notlar tablosu
CREATE TABLE IF NOT EXISTS notes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    type ENUM('meeting', 'complaint', 'general') DEFAULT 'general',
    status ENUM('active', 'deleted') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Örnek görev verileri
INSERT INTO tasks (customer_id, title, description, type, start_date, due_date, status) VALUES
(1, 'Müşteri Ziyareti', 'Yeni ürün tanıtımı için ziyaret', 'visit', NOW(), DATE_ADD(NOW(), INTERVAL 2 DAY), 'pending'),
(2, 'Telefon Görüşmesi', 'Fiyat teklifinin görüşülmesi', 'call', NOW(), DATE_ADD(NOW(), INTERVAL 1 DAY), 'pending'),
(3, 'Toplantı', 'Yıllık değerlendirme toplantısı', 'meeting', NOW(), DATE_ADD(NOW(), INTERVAL 3 DAY), 'pending');

-- Örnek not verileri
INSERT INTO notes (customer_id, title, content, type) VALUES
(1, 'Görüşme Notu', 'Müşteri yeni ürün serisi ile ilgileniyor. Fiyat listesi gönderilecek.', 'meeting'),
(2, 'Şikayet Kaydı', 'Teslimat gecikmesi hakkında şikayet alındı. Lojistik ile görüşülecek.', 'complaint'),
(3, 'Genel Not', 'Müşteri referans olmayı kabul etti. Web sitesi için logo gönderilecek.', 'general');

-- Müşteri iletişim geçmişi tablosu
CREATE TABLE IF NOT EXISTS customer_interactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL,
    interaction_type ENUM('call', 'email', 'meeting', 'other') NOT NULL,
    description TEXT,
    contact_person VARCHAR(100),
    interaction_date DATETIME NOT NULL,
    next_follow_up_date DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Örnek etkileşim verileri
INSERT INTO customer_interactions (customer_id, interaction_type, description, contact_person, interaction_date, next_follow_up_date) VALUES
(1, 'call', 'Ürün tanıtımı yapıldı', 'Ahmet Yılmaz', NOW(), DATE_ADD(NOW(), INTERVAL 7 DAY)),
(2, 'meeting', 'Yeni proje görüşmesi', 'Mehmet Demir', NOW(), DATE_ADD(NOW(), INTERVAL 14 DAY)),
(3, 'email', 'Teklif gönderildi', 'Ayşe Kaya', NOW(), DATE_ADD(NOW(), INTERVAL 3 DAY));

-- Müşteri memnuniyet anketi tablosu
CREATE TABLE IF NOT EXISTS customer_surveys (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL,
    satisfaction_score INT CHECK (satisfaction_score BETWEEN 1 AND 5),
    feedback TEXT,
    survey_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Örnek görev verilerini güncelle
DELETE FROM tasks; -- Önceki verileri temizle
INSERT INTO tasks (customer_id, title, description, type, start_date, due_date, status) 
SELECT 
    c.id,
    'Müşteri Ziyareti',
    'Yeni ürün tanıtımı için ziyaret',
    'visit',
    NOW(),
    DATE_ADD(NOW(), INTERVAL 2 DAY),
    'pending'
FROM customers c
WHERE c.email = 'ilyasdemirelli68@gmail.com'
LIMIT 1;

INSERT INTO tasks (customer_id, title, description, type, start_date, due_date, status)
SELECT 
    c.id,
    'Telefon Görüşmesi',
    'Fiyat teklifinin görüşülmesi',
    'call',
    NOW(),
    DATE_ADD(NOW(), INTERVAL 1 DAY),
    'pending'
FROM customers c
WHERE c.email = 'ahmet@firma.com'
LIMIT 1;

INSERT INTO tasks (customer_id, title, description, type, start_date, due_date, status)
SELECT 
    c.id,
    'Toplantı',
    'Yıllık değerlendirme toplantısı',
    'meeting',
    NOW(),
    DATE_ADD(NOW(), INTERVAL 3 DAY),
    'pending'
FROM customers c
WHERE c.email = 'mehmet@sirket.com'
LIMIT 1;

-- Örnek not verilerini güncelle
DELETE FROM notes; -- Önceki verileri temizle
INSERT INTO notes (customer_id, title, content, type)
SELECT 
    c.id,
    'Görüşme Notu',
    'Müşteri yeni ürün serisi ile ilgileniyor. Fiyat listesi gönderilecek.',
    'meeting'
FROM customers c
WHERE c.email = 'ilyasdemirelli68@gmail.com'
LIMIT 1;

INSERT INTO notes (customer_id, title, content, type)
SELECT 
    c.id,
    'Şikayet Kaydı',
    'Teslimat gecikmesi hakkında şikayet alındı. Lojistik ile görüşülecek.',
    'complaint'
FROM customers c
WHERE c.email = 'ahmet@firma.com'
LIMIT 1;

INSERT INTO notes (customer_id, title, content, type)
SELECT 
    c.id,
    'Genel Not',
    'Müşteri referans olmayı kabul etti. Web sitesi için logo gönderilecek.',
    'general'
FROM customers c
WHERE c.email = 'mehmet@sirket.com'
LIMIT 1;
