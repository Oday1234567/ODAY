-- إنشاء قاعدة البيانات
CREATE DATABASE IF NOT EXISTS ra2ba_db 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE ra2ba_db;

-- جدول المستخدمين/الأعضاء
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'editor', 'supervisor') DEFAULT 'editor',
    committee VARCHAR(50),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- جدول الفعاليات
CREATE TABLE events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    event_time TIME NOT NULL,
    location VARCHAR(255),
    capacity INT,
    image VARCHAR(255),
    status ENUM('active', 'draft', 'cancelled') DEFAULT 'draft',
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- جدول تسجيلات الفعاليات
CREATE TABLE event_registrations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    college VARCHAR(100),
    registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
);

-- جدول المقالات
CREATE TABLE articles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    content LONGTEXT,
    category VARCHAR(100),
    featured_image VARCHAR(255),
    status ENUM('published', 'draft') DEFAULT 'draft',
    author_id INT,
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL
);

-- جدول طلبات الانضمام
CREATE TABLE join_requests (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    college VARCHAR(100),
    major VARCHAR(100),
    academic_year VARCHAR(50),
    interests TEXT,
    experience TEXT,
    skills TEXT,
    motivation TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- جدول رسائل اتصل بنا
CREATE TABLE contact_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(255),
    message TEXT,
    status ENUM('new', 'replied') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- جدول إحصائيات الزوار
CREATE TABLE visitor_stats (
    id INT PRIMARY KEY AUTO_INCREMENT,
    visit_date DATE NOT NULL UNIQUE,
    page_views INT DEFAULT 0,
    unique_visitors INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- جدول سجل الدخول
CREATE TABLE login_logs (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    login_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- جدول الإعدادات
CREATE TABLE settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- إدخال مدير أساسي
-- كلمة المرور: password
INSERT INTO users (name, email, password, role, status) 
VALUES ('المدير العام', 'admin@ra2ba.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'active');

-- إدخال إعدادات أساسية
INSERT INTO settings (setting_key, setting_value) VALUES
('site_name', 'الرابطة الطلابية'),
('site_description', 'رابطة طلاب الكلية - حيث تبدأ القيادة'),
('admin_email', 'admin@ra2ba.edu'),
('contact_phone', '+966 123 456 789'),
('contact_address', 'جامعة الملك سعود - الرياض');

-- إنشاء الفهرس لتحسين الأداء
CREATE INDEX idx_events_date ON events(event_date);
CREATE INDEX idx_events_status ON events(status);
CREATE INDEX idx_articles_status ON articles(status);
CREATE INDEX idx_articles_category ON articles(category);
CREATE INDEX idx_visit_date ON visitor_stats(visit_date);
CREATE INDEX idx_login_time ON login_logs(login_time);
CREATE INDEX idx_join_status ON join_requests(status);
CREATE INDEX idx_message_status ON contact_messages(status);

-- إدخال بيانات تجريبية للاختبار
INSERT INTO events (title, description, event_date, event_time, location, capacity, status, created_by) VALUES
('اليوم الهندسي', 'فعالية تعريفية بالتخصصات الهندسية وفرص العمل', '2024-03-15', '10:00:00', 'قاعة المؤتمرات - مبنى الهندسة', 100, 'active', 1),
('ورشة البرمجة', 'ورشة عملية لتعليم أساسيات البرمجة بلغة Python', '2024-03-20', '14:00:00', 'معمل الحاسب الآلي - مبنى العلوم', 30, 'active', 1),
('ملتقى التوظيف', 'فرص تدريب وعمل للخريجين مع كبرى الشركات', '2024-04-01', '09:00:00', 'الصالة الرياضية', 200, 'active', 1);

INSERT INTO articles (title, content, category, status, author_id, views) VALUES
('نصائح للفصل الدراسي الجديد', 'نصائح عملية للاستفادة القصوى من الفصل الدراسي وتحقيق النجاح الأكاديمي...', 'تعليم', 'published', 1, 150),
('أهمية العمل التطوعي', 'كيف يمكن للعمل التطوعي أن ينمي مهاراتك ويوسع شبكة علاقاتك المهنية...', 'تطوير', 'published', 1, 89),
('تقنيات الدراسة الفعالة', 'أحدث الطرق والتقنيات لتحسين عملية الدراسة وزيادة التركيز...', 'تعليم', 'published', 1, 120);

INSERT INTO contact_messages (name, email, subject, message, status) VALUES
('أحمد محمد', 'ahmed@example.com', 'استفسار عن العضوية', 'أرغب في معرفة شروط العضوية في الرابطة وكيفية الانضمام...', 'new'),
('فاطمة عبدالله', 'fatima@example.com', 'مقترح فعالية', 'أقترح تنظيم فعالية عن الذكاء الاصطناعي وتطبيقاته...', 'replied');

-- إدخال إحصائيات زوار تجريبية
INSERT INTO visitor_stats (visit_date, page_views, unique_visitors) VALUES
(CURDATE() - INTERVAL 6 DAY, 45, 23),
(CURDATE() - INTERVAL 5 DAY, 67, 34),
(CURDATE() - INTERVAL 4 DAY, 89, 45),
(CURDATE() - INTERVAL 3 DAY, 56, 28),
(CURDATE() - INTERVAL 2 DAY, 78, 39),
(CURDATE() - INTERVAL 1 DAY, 92, 47),
(CURDATE(), 34, 18)
ON DUPLICATE KEY UPDATE 
page_views = VALUES(page_views), 
unique_visitors = VALUES(unique_visitors);-- جدول ردود الرسائل
CREATE TABLE message_replies (
    id INT PRIMARY KEY AUTO_INCREMENT,
    message_id INT,
    reply_subject VARCHAR(255) NOT NULL,
    reply_message TEXT NOT NULL,
    replied_by INT,
    replied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (message_id) REFERENCES contact_messages(id) ON DELETE CASCADE,
    FOREIGN KEY (replied_by) REFERENCES users(id) ON DELETE SET NULL
);

-- إنشاء الفهرس لجدول الردود
CREATE INDEX idx_message_replies_message_id ON message_replies(message_id);
CREATE INDEX idx_message_replies_replied_at ON message_replies(replied_at);

