CREATE DATABASE IF NOT EXISTS db_laundry CHARACTER SET utf8mb4;
USE db_laundry;

CREATE TABLE admin (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL
);

CREATE TABLE mesin (
  id INT AUTO_INCREMENT PRIMARY KEY,
  kode VARCHAR(20) NOT NULL UNIQUE,
  nama VARCHAR(100) NOT NULL,
  jenis ENUM('Mesin Cuci','Pengering','Setrika Uap') NOT NULL,
  merek VARCHAR(50) NOT NULL,
  lokasi VARCHAR(80) NOT NULL,
  status ENUM('Normal','Rusak') NOT NULL DEFAULT 'Normal',
  foto VARCHAR(100) NULL
);

CREATE TABLE laporan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  mesin_id INT NOT NULL,
  tanggal DATE NOT NULL,
  deskripsi TEXT NOT NULL,
  status ENUM('Menunggu','Diperbaiki','Selesai') NOT NULL DEFAULT 'Menunggu',
  tanggal_selesai DATE NULL,
  FOREIGN KEY (mesin_id) REFERENCES mesin(id) ON DELETE CASCADE
);

INSERT INTO admin (username, password) VALUES ('admin', 'admin');
INSERT INTO mesin (kode, nama, jenis, merek, lokasi, status) VALUES
('MC-01','Mesin Cuci 1','Mesin Cuci','Electrolux','Ruang Cuci A','Normal'),
('MC-02','Mesin Cuci 2','Mesin Cuci','LG','Ruang Cuci A','Rusak'),
('PG-01','Pengering 1','Pengering','Samsung','Ruang Jemur','Normal');
INSERT INTO laporan (mesin_id, tanggal, deskripsi) VALUES (2, CURDATE(), 'Drum tidak berputar dan muncul bunyi berdenging.');
