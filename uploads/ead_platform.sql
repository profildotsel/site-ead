
CREATE DATABASE IF NOT EXISTS ead_platform;
USE ead_platform;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    `role` ENUM('administrador', 'instrutor', 'estudante') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    instructor_id INT NOT NULL,
    `status` ENUM('publicado', 'rascunho', 'arquivado') DEFAULT 'rascunho',
    start_date DATE,
    end_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (instructor_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    completion_date TIMESTAMP NULL,
    `status` ENUM('inscrito', 'concluido', 'cancelado') DEFAULT 'inscrito',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (user_id, course_id)
);

CREATE TABLE lessons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    `order` INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

CREATE TABLE assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    lesson_id INT,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    due_date TIMESTAMP,
    max_score DECIMAL(5, 2) DEFAULT 100.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE SET NULL
);

CREATE TABLE submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    assignment_id INT NOT NULL,
    user_id INT NOT NULL,
    submission_text TEXT,
    submission_file_url VARCHAR(255),
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    grade DECIMAL(5, 2),
    feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE resources (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    lesson_id INT,
    title VARCHAR(255) NOT NULL,
    `type` ENUM('link', 'arquivo', 'texto') NOT NULL,
    url VARCHAR(255),
    content TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE SET NULL
);

INSERT INTO `users` (`id`, `username`, `password_hash`, `email`, `first_name`, `last_name`, `role`) VALUES
(1, 'admin', 'admin123', 'admin@ead.com', 'Admin', 'System', 'administrador'),
(2, 'professor', 'prof123', 'prof@ead.com', 'João', 'Silva', 'instrutor'),
(3, 'aluno', 'aluno123', 'aluno@ead.com', 'Maria', 'Santos', 'estudante');

INSERT INTO `courses` (`id`, `title`, `description`, `instructor_id`, `status`, `start_date`, `end_date`) VALUES
(1, 'Introdução ao Desenvolvimento Web', 'Aprenda HTML, CSS e JavaScript do zero', 2, 'publicado', '2025-08-01', '2025-12-01'),
(2, 'Programação em Python', 'Domine a linguagem Python para desenvolvimento', 2, 'publicado', '2025-08-15', '2025-11-15'),
(3, 'Design de Interfaces', 'Princípios de UX/UI para aplicações modernas', 2, 'rascunho', '2025-09-01', '2025-12-15');

INSERT INTO `enrollments` (`id`, `user_id`, `course_id`, `status`) VALUES (1, 3, 1, 'inscrito'), (2, 3, 2, 'inscrito');
INSERT INTO `lessons` (`id`, `course_id`, `title`, `content`, `order`) VALUES (1, 1, 'Fundamentos de HTML', '<h2>Introdução ao HTML</h2><p>HTML é a linguagem de marcação padrão para páginas web.</p>', 1),(2, 1, 'Estilização com CSS', '<h2>Introdução ao CSS</h2><p>CSS (Cascading Style Sheets) é usado para estilizar páginas HTML.</p>', 2),(3, 1, 'JavaScript Básico', '<h2>Introdução ao JavaScript</h2><p>JavaScript é a linguagem de programação das páginas web.</p>', 3),(4, 2, 'Introdução ao Python', '<h2>Primeiros Passos com Python</h2><p>Python é uma linguagem de programação poderosa e fácil de aprender.</p>', 1);
INSERT INTO `assignments` (`id`, `course_id`, `lesson_id`, `title`, `description`, `due_date`, `max_score`) VALUES (1, 1, 1, 'Estrutura HTML', 'Crie uma página HTML com título, parágrafo e lista.', '2025-08-20 23:59:59', 100),(2, 1, 2, 'Estilo CSS', 'Adicione estilos à sua página HTML usando CSS.', '2025-08-25 23:59:59', 100),(3, 2, 4, 'Hello World em Python', 'Escreva um programa Python que imprima "Hello, World!"', '2025-08-30 23:59:59', 100);
INSERT INTO `submissions` (`id`, `assignment_id`, `user_id`, `submission_text`, `grade`, `feedback`) VALUES (1, 1, 3, '<!DOCTYPE html><html>...</html>', 95.00, 'Excelente trabalho! Estrutura HTML correta.');
INSERT INTO `resources` (`id`, `course_id`, `lesson_id`, `title`, `type`, `url`) VALUES (1, 1, 1, 'Documentação HTML', 'link', 'https://developer.mozilla.org/pt-BR/docs/Web/HTML'),(2, 1, 2, 'Guia de CSS', 'arquivo', '/uploads/css-guide.pdf'),(3, 1, NULL, 'Boas Práticas', 'texto', NULL);