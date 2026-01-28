-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 07, 2026 at 06:04 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cyber`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`username`, `password`) VALUES
('kas', 'kas');

-- --------------------------------------------------------

--
-- Table structure for table `finished_lessons`
--

CREATE TABLE `finished_lessons` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `lesson_id` int(11) NOT NULL,
  `completed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `fix_guide`
--

CREATE TABLE `fix_guide` (
  `guide_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `steps` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `lessons`
--

CREATE TABLE `lessons` (
  `lesson_id` int(11) NOT NULL,
  `header` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lessons`
--

INSERT INTO `lessons` (`lesson_id`, `header`, `content`, `created_at`) VALUES
(3, 'Introduction to cyber security', 'Cybersecurity is the practice of protecting computer systems, networks, and digital information from theft, damage, or unauthorized access. In today’s digital world—where almost everything from banking to healthcare depends on technology—the importance of cybersecurity cannot be overstated. But before diving deep, let’s start with some questions that spark curiosity and highlight why cybersecurity matters.', '2025-09-18 06:03:08'),
(5, 'SQL Injection (SQLi)', 'introduction:\r\nSQL Injection happens when a web app takes text a user types and accidentally mixes it straight into a database command. Think of it like letting someone whisper instructions into the ear of a librarian who then follows them exactly — even if the instructions are bad. If successful, an attacker can read, change, or delete data they shouldn’t see.\r\n\r\nWhy it matters:\r\nThis is serious because databases often hold passwords, personal info, financial records — the whole store. If an attacker succeeds, they can cause data leaks, break features, or even take over the system.\r\n\r\nHow it usually happens:\r\nDevelopers sometimes build database queries by joining strings together (e.g., `\"SELECT * FROM users WHERE id=\" + userInput`). If `userInput` contains sneaky text, it can change the meaning of the query. The mistake is trusting user input without treating it as data only.\r\n\r\nSafe places to practice:\r\nOnly test on systems you own or have clear permission for. Good learning targets are intentionally vulnerable apps you run locally, such as OWASP Juice Shop, WebGoat, or DVWA — use those inside an isolated VM or container.\r\n\r\nHow to stop it — simple rules:\r\n\r\n*\'Never\' build SQL by concatenating raw user text.\r\n* Use \'parameterized queries\' / *\'prepared statements\' (these treat user input as data, not code).\r\n* Use the database account with the \'least privileges\' needed (don’t give your web app the “king” account).\r\n* Validate inputs when possible (e.g., accept only numbers for `id` fields). Prefer allow-lists (known good values) over block-lists.\r\n* Don’t show detailed database error messages to users — they help attackers.\r\n* Treat a Web Application Firewall (WAF) as extra protection — useful, but not a replacement for secure coding.\r\n\r\nShort, friendly example (PHP PDO):\r\n\r\n```php\r\n// Safe: the ? is a placeholder — the DB treats $email only as data\r\n$stmt = $pdo->prepare(\'SELECT * FROM users WHERE email = ?\');\r\n$stmt->execute([$email]);\r\n$user = $stmt->fetch();\r\n```\r\n\r\nWhy this is safe:\r\nthe database receives the SQL structure and the input separately, so even if `$email` contains weird characters, it won’t change the query’s meaning.\r\n\r\n\"How to spot it in the wild (detection tips):\"\r\n\r\n* Unexpected spikes in database errors or failed queries.\r\n* Logs showing queries with strange or very long input values.\r\n* Alerts from automated scanners (DAST or SAST) that flag unparameterized queries or suspicious query patterns.\r\n* Unusually slow queries or full-table scans triggered by odd inputs.\r\n\r\nQuick checklist for developers (teaching checklist):\r\n\r\n* Use parameterized queries everywhere.\r\n* Restrict DB user permissions.\r\n* Validate and canonicalize inputs.\r\n* Turn off verbose DB errors in production.\r\n* Run regular static and dynamic scans in staging environments.\r\n\r\nIf you want, I can convert this into a one-page lesson (markdown or HTML) with a short exercise and an instructor answer key. Which format would you prefer?\r\n', '2025-09-28 05:33:01'),
(6, 'Cross-Site Scripting (XSS)', 'What it is (plain):\r\nXSS happens when a website lets attacker-supplied text run as code in someone else’s browser often letting attackers steal session data or show fake pages.\r\n\r\nWhy it matters:\r\nIf an attacker can run code in victims’ browsers, they can impersonate them, steal cookies, or trick users into actions (phishing).\r\n\r\nHow it usually happens (high level):\r\nUser content (comments, profile bio, search terms) is inserted into pages without proper escaping. The browser then treats that content as code instead of text.\r\n\r\nSafe places to practice:\r\nJuice Shop, WebGoat, DVWA — use local VMs or containers.\r\n\r\nHow to stop it (simple rules):\r\n\r\nEscape / encode output based on where the text is placed (HTML, attribute, JS, URL).\r\n\r\nUse frameworks that escape by default (React, Angular templating) and avoid innerHTML.\r\n\r\nUse a Content Security Policy (CSP) to limit which scripts are allowed.\r\n\r\nSanitize HTML only with vetted libraries and strict allow-lists.\r\n\r\nSafe example (conceptual):\r\nUse template syntax that auto-escapes: {{ user_comment }} instead of inserting raw HTML.\r\n\r\nHow to spot it:\r\nReports of strange popups, WAF XSS alerts, or scanners flagging pages that accept HTML.\r\n\r\nChecklist: output encoding per context, avoid innerHTML, apply CSP, sanitize rich text safely, test with DAST.', '2025-09-28 11:03:01'),
(7, 'Cross-Site Request Forgery (CSRF)', 'What it is :\r\nCSRF tricks a logged-in user’s browser into submitting actions they didn’t intend (like changing an email or transferring funds) by having the browser carry the user’s authentication automatically.\r\n\r\nWhy it matters:\r\nBecause browsers send cookies automatically, a victim could make a harmful request just by visiting a malicious page while logged in to another site.\r\n\r\nHow it usually happens (high level):\r\nA web action relies only on the browser’s cookies/session and doesn’t verify that the request truly came from the right site/user.\r\n\r\nSafe places to practice:\r\nTest on local vulnerable apps in a VM; focus on state-changing endpoints and token validation logic.\r\n\r\nHow to stop it (simple rules):\r\n\r\nUse CSRF tokens for state-changing forms (unique per session or per request).\r\n\r\nSet cookies with SameSite where appropriate.\r\n\r\nFor APIs, use Authorization headers (Bearer tokens) rather than cookies.\r\n\r\nRequire re-auth or MFA for critical actions.\r\n\r\nSafe example (pseudo):\r\nGenerate a token server-side, include it in the form and validate it when the form is submitted.\r\n\r\nHow to spot it:\r\nEndpoints that change data but accept requests with no CSRF token or origin/referrer checks; DAST will flag missing tokens.\r\n\r\nChecklist: CSRF tokens for forms, SameSite cookie settings, Auth headers for APIs, extra verification for critical ops.', '2025-09-28 11:13:16'),
(8, 'Broken / Weak Authentication & Session Management', 'What it is (plain):\r\nProblems with how users log in and how sessions/tokens are handled — like weak passwords, poorly stored passwords, or session IDs that are easy to guess.\r\n\r\nWhy it matters:\r\nThese flaws let attackers take over accounts, impersonate users, or maintain access to systems.\r\n\r\nHow it usually happens (high level):\r\nWeak password policies, storing passwords in plaintext, predictable session tokens, or not protecting cookies properly.\r\n\r\nSafe places to practice:\r\nCreate test accounts on local vulnerable apps; use password-testing tools only on systems you own.\r\n\r\nHow to stop it (simple rules):\r\n\r\nHash passwords with strong algorithms (bcrypt, Argon2) and use salts.\r\n\r\nEnforce rate-limits and lockouts to prevent brute force.\r\n\r\nUse MFA for sensitive accounts.\r\n\r\nSet session cookies with Secure, HttpOnly, and SameSite.\r\n\r\nRotate tokens and shorten session lifetimes.\r\n\r\nSafe example (conceptual):\r\nUse a library for authentication rather than building it yourself; store only password hashes, never plain text.\r\n\r\nHow to spot it:\r\nMany failed login attempts, credential stuffing alerts, tokens reused across sessions, or session IDs appearing in logs/URLs.\r\n\r\nChecklist: strong hashing, MFA, rate limiting, secure cookie flags, token rotation.', '2025-09-28 11:20:02'),
(9, 'Insecure Direct Object References (IDOR) / Broken Access Control', 'What it is (plain):\r\nWhen an app lets users pick objects (like invoices or profiles) by ID, but doesn’t check whether the user owns that object — allowing someone to see or change others’ data just by changing an ID.\r\n\r\nWhy it matters:\r\nAttackers can access confidential data or perform actions on behalf of other users.\r\n\r\nHow it usually happens (high level):\r\nDevelopers trust the client to enforce permission rules (e.g., “only show edit button if owner”), but forget to check on the server.\r\n\r\nSafe places to practice:\r\nTry accessing neighboring resources by changing IDs in a local test environment you control.\r\n\r\nHow to stop it (simple rules):\r\n\r\n*Always enforce server-side authorization: check ownership/permissions for every request.\r\n*Use indirect/unpredictable references (random UUIDs or tokens) — but still check authorization on the server.\r\n*Implement clear RBAC/ABAC rules and test them.\r\n\r\nHow to spot it:\r\nLogs showing many accesses to sequential IDs, scanners flagging endpoints with missing auth checks.\r\nChecklist: server-side checks on every request, use unguessable IDs as defense-in-depth, test access controls regularly.', '2025-09-28 11:23:25'),
(10, 'Security Misconfiguration', 'What it is (plain):\r\nThis is when systems are left with unsafe defaults or poorly configured settings — like admin pages exposed, debug mode on, or old unpatched components.\r\n\r\nWhy it matters:\r\nSmall mistakes in settings can open big doors for attackers — the system may be fundamentally insecure even if the code is fine.\r\n\r\nHow it usually happens (high level):\r\nRushing deployments, forgetting to change default passwords, leaving debug info enabled, or not patching software.\r\n\r\nSafe places to practice:\r\nRun configuration scanners (OpenVAS, Nessus) in staging and use CIS benchmark checks.\r\n\r\nHow to stop it (simple rules):\r\n\r\nRemove or disable unused features and ports.\r\nTurn off debug or verbose error messages in production.\r\nChange default credentials and rotate secrets.\r\nKeep software up to date and automate security checks in CI/CD.\r\nUse IaC (Infrastructure as Code) so configs are consistent and reviewable.\r\n\r\nHow to spot it:\r\nExposed management consoles, outdated library versions, public cloud buckets with wrong permissions.\r\n\r\nChecklist: asset inventory, patching schedule, remove defaults, automated config scans, minimize attack surface.', '2025-09-28 11:29:42'),
(11, 'Sensitive Data Exposure', 'What it is:\r\nFailing to protect private data — like storing passwords in plain text, using weak encryption, or leaving backups public — which makes data easy to steal or misuse.\r\n\r\nWhy it matters:\r\nIf private data leaks, it harms users and the organization (identity theft, regulatory fines, reputational damage).\r\n\r\nHow it usually happens (high level):\r\nUsing weak or no encryption for data in transit or at rest, saving secrets in code, or poor key management.\r\n\r\nSafe places to practice:\r\nScan repos for secrets (locally), run TLS tests (Qualys SSL Labs) on your servers, and use local vaults for secret management.\r\n\r\nHow to stop it (simple rules):\r\nUse TLS for all connections and enforce modern cipher suites.\r\nEncrypt sensitive data at rest using strong algorithms and manage keys using a vault (HashiCorp Vault, cloud KMS).\r\nNever store secrets in source code — use environment variables or secret stores.\r\nMask data in logs and use tokenization where appropriate.\r\n\r\nHow to spot it:\r\nPublicly accessible buckets, secrets found in source control, or weak TLS configuration warnings.\r\n\r\nChecklist: TLS enforced, secrets vaulted, no secrets in code, encrypted backups, key rotation policy.', '2025-09-28 11:33:21'),
(12, 'Insecure Deserialization', 'What it is :\r\nWhen an app accepts structured data (like serialized objects) from untrusted sources and turns them back into live program objects — an attacker can send crafted inputs that alter program behavior.\r\n\r\nWhy it matters:\r\nIn bad cases this can let attackers run arbitrary code, change logic, or escalate privileges.\r\n\r\nHow it usually happens (high level):\r\nDevelopers deserialize blobs (binary or complex JSON) without verifying that the content is safe or from a trusted source.\r\n\r\nSafe places to practice:\r\nStudy OWASP deserialization guidance in a lab environment; only test on systems you own.\r\n\r\nHow to stop it (simple rules):\r\nAvoid native object deserialization for untrusted data. Prefer safe formats (JSON) parsed with strict schemas.\r\nUse integrity checks (MACs/signatures) on serialized blobs so you can detect tampering.\r\nOnly allow known-safe types/classes on deserialization and run deserialization in low-privilege contexts.\r\n\r\nHow to spot it:\r\nDeserialization exceptions in logs, or SAST flags pointing to deserialization of untrusted input.\r\n\r\nChecklist: avoid native deserialization, validate schemas, sign serialized data, run in sandboxed processes.', '2025-09-28 11:34:27'),
(13, 'Server-Side Request Forgery (SSRF)', 'What it is:\r\nSSRF lets an attacker make your server send requests to places it shouldn’t — like internal services or cloud metadata endpoints — using your server as a proxy.\r\n\r\nWhy it matters:\r\nAttackers can reach internal-only systems, retrieve secrets (e.g., cloud credentials), or pivot into private networks.\r\n\r\nHow it usually happens (high level):\r\nAn app fetches URLs supplied by users (e.g., previewing an image) but doesn’t validate or restrict where the server may connect.\r\n\r\nSafe places to practice:\r\nTest in an isolated network; use SSRF labs and only test systems you control.\r\n\r\nHow to stop it (simple rules):\r\nDon’t let users specify arbitrary URLs to fetch. Use allow-lists of trusted domains.\r\nBlock internal IP ranges and cloud metadata IPs at the network level.\r\nValidate and canonicalize URLs, restrict schemes and ports, and perform outbound requests from sandboxed network zones.\r\n\r\nHow to spot it:\r\nUnexpected outbound requests to internal IPs, or logs showing server connecting to metadata services.\r\nChecklist: allow-list outbound endpoints, egress filtering, validate and canonicalize URLs, sandbox network calls.', '2025-09-28 11:36:22'),
(14, 'OS / Command Injection', 'What it is :\r\nIf an app builds shell commands using user input and then runs them, an attacker can insert extra commands and make the server run whatever they want.\r\n\r\nWhy it matters:\r\nCommand injection can lead to full server compromise — attackers can read files, add users, or install malware.\r\n\r\nHow it usually happens (high level):\r\nDevelopers call system shells with concatenated user input (e.g., system(\"ping \" + userHost)). Attackers inject extra control characters to append commands.\r\n\r\nSafe places to practice:\r\nCode review and static scanning in staging; use local vulnerable labs in isolated VMs for educational purposes.\r\n\r\nHow to stop it (simple rules):\r\nAvoid running shell commands with user input. Use native language APIs instead (file APIs, networking libraries).\r\nIf you must run external programs, pass arguments as arrays (no shell parsing) and never use shell=True.\r\nValidate input strictly with allow-lists and run processes as low-privilege users.\r\n\r\nSafe example (Python):\r\n\r\n# Safe: pass args as a list; do not use shell=True\r\nsubprocess.run([\"/usr/bin/convert\", input_file_path, output_file_path], check=True)\r\n\r\n\r\nHow to spot it:\r\nLogs showing weird process invocations, unexpected shell-created child processes, or SAST flags.\r\nChecklist: avoid shelling out, use safe subprocess APIs, validate inputs, run in constrained privileges.', '2025-09-28 11:38:56');

-- --------------------------------------------------------

--
-- Table structure for table `ongoing_lesson`
--

CREATE TABLE `ongoing_lesson` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `lesson_id` int(11) NOT NULL,
  `answer` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ongoing_lesson`
--

INSERT INTO `ongoing_lesson` (`id`, `user_id`, `lesson_id`, `answer`) VALUES
(3, 1, 3, 'C) To ensure confidentiality/ integrity/ and availability of information'),
(4, 1, 3, 'B) An intentional attempt to gain unauthorized access or cause harm to digital systems');

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `lesson_id` int(11) NOT NULL,
  `questions` text NOT NULL,
  `answers` text NOT NULL,
  `correct_answer` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`id`, `lesson_id`, `questions`, `answers`, `correct_answer`) VALUES
(3, 3, '1. What is the primary goal of cybersecurity?', 'A) To make computers faster, B) To protect systems/ networks/ and data from threats, C) To ensure confidentiality/ integrity/ and availability of information, D) To reduce internet costs', 'C) To ensure confidentiality/ integrity/ and availability of information'),
(4, 3, '2. Which of the following best describes a cyber attack?', 'A) A natural disaster damaging computer systems, B) An intentional attempt to gain unauthorized access or cause harm to digital systems, C) A computer upgrade performed by IT staff, D) A legal audit of digital information', 'B) An intentional attempt to gain unauthorized access or cause harm to digital systems'),
(5, 3, '3. Why is cybersecurity important in modern society?', 'A) Because technology is only used in schools, B) Because only banks need to secure data, C) Because almost every sector depends on technology and digital data, D) Because it reduces electricity bills', 'C) Because almost every sector depends on technology and digital data'),
(6, 3, '4. Which of these is NOT considered part of cybersecurity?', 'A) Protecting personal data,B) Preventing unauthorized access,C) Ensuring safe online transactions,D) Building faster processors', 'D) Building faster processors'),
(7, 3, '5. Which of the following is an example of unauthorized access?', 'A) A user logging in with their password,B) A bank updating its software,C) A hacker breaking into someone’s email account without permission,D) An IT team running system backups', 'C) A hacker breaking into someone’s email account without permission'),
(8, 3, '6. What are the three core principles of cybersecurity often referred to as the CIA triad?', 'A) Communication/ Internet/ Authentication,B) Control/ Information/ Access,C) Confidentiality/ Integrity/ Availability,D) Cyber/ Internet/ Applications', 'C) Confidentiality/ Integrity/Availability'),
(9, 3, '7. Which of the following would be most affected if a hospital’s IT systems were hacked?', 'A) Coffee machines,\r\nB) Movie streaming services,\r\nC) Patient records and healthcare services,\r\nD) Video games', 'C) Patient records and healthcare services'),
(10, 3, '8. What does “confidentiality” mean in cybersecurity?', 'A) Data is always available for anyone,\r\nB) Data cannot be changed,\r\nC) Data is processed faster,\r\nD) Data is only accessible to those authorized to see it', 'D) Data is only accessible to those authorized to see it'),
(11, 3, '9. Which scenario demonstrates “integrity” in cybersecurity?', 'A) A student keeping their grades private,\r\nB) Ensuring that stored data has not been tampered with or altered,\r\nC) Allowing 24/7 system access,\r\nD) Encrypting messages during transfer', 'B) Ensuring that stored data has not been tampered with or altered'),
(12, 3, '10. What does “availability” ensure in the context of cybersecurity?', 'A) Only IT staff can view data,\r\nB) Data is encrypted before being stored,\r\nC) Data is never backed up,\r\nD) Authorized users can access systems and data when needed', 'D) Authorized users can access systems and data when needed');

-- --------------------------------------------------------

--
-- Table structure for table `scan_results`
--

CREATE TABLE `scan_results` (
  `scan_id` int(11) NOT NULL,
  `ip_address` varchar(100) NOT NULL,
  `user_id` int(11) NOT NULL,
  `datetime` timestamp NOT NULL DEFAULT current_timestamp(),
  `result` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT 'user',
  `phone` int(100) NOT NULL,
  `address` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `full_name`, `phone`, `address`) VALUES
(1, '1', 'kasunadikari331@gmail.com', '$2y$10$CLucnsR1kl16nO0JjQh8AOKOvdyh9uKAbwZJbTDBrpqEYdwFaEjq6', '1', 0, ''),
(2, 'fake', 'fake@gmail.com', '$2y$10$MzqOtpPHZ3A/19Y3uLrIbuJm4R3IyOiHoyb5lANk4RQ5S6fH1ClFC', 'fakee', 9929922, 'fake street'),
(4, 'dsd', 'sds@gmail.com', '$2y$10$LBtvoPu.eh4EwF.dDocOaOs0o2QG8d.34WSUeYC2JPV52opATEiz6', 'sds', 111, 'fake street');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `finished_lessons`
--
ALTER TABLE `finished_lessons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `lesson_id` (`lesson_id`);

--
-- Indexes for table `fix_guide`
--
ALTER TABLE `fix_guide`
  ADD PRIMARY KEY (`guide_id`);

--
-- Indexes for table `lessons`
--
ALTER TABLE `lessons`
  ADD PRIMARY KEY (`lesson_id`);

--
-- Indexes for table `ongoing_lesson`
--
ALTER TABLE `ongoing_lesson`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ongoing_user` (`user_id`),
  ADD KEY `fk_ongoing_lesson` (`lesson_id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `lesson_id` (`lesson_id`);

--
-- Indexes for table `scan_results`
--
ALTER TABLE `scan_results`
  ADD PRIMARY KEY (`scan_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `finished_lessons`
--
ALTER TABLE `finished_lessons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `fix_guide`
--
ALTER TABLE `fix_guide`
  MODIFY `guide_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `lessons`
--
ALTER TABLE `lessons`
  MODIFY `lesson_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `ongoing_lesson`
--
ALTER TABLE `ongoing_lesson`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `scan_results`
--
ALTER TABLE `scan_results`
  MODIFY `scan_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `finished_lessons`
--
ALTER TABLE `finished_lessons`
  ADD CONSTRAINT `finished_lessons_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `finished_lessons_ibfk_2` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`lesson_id`) ON DELETE CASCADE;

--
-- Constraints for table `ongoing_lesson`
--
ALTER TABLE `ongoing_lesson`
  ADD CONSTRAINT `fk_ongoing_lesson` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`lesson_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_ongoing_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ongoing_lesson_ibfk_1` FOREIGN KEY (`id`) REFERENCES `questions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`lesson_id`) REFERENCES `lessons` (`lesson_id`) ON DELETE CASCADE;

--
-- Constraints for table `scan_results`
--
ALTER TABLE `scan_results`
  ADD CONSTRAINT `scan_results_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
