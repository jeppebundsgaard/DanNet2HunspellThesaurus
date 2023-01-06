-- phpMyAdmin SQL Dump
-- version 5.1.4deb1
-- https://www.phpmyadmin.net/
--
-- Vært: localhost:3306
-- Genereringstid: 06. 01 2023 kl. 00:08:14
-- Serverversion: 8.0.31-0ubuntu2
-- PHP-version: 8.1.7-1ubuntu3.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `dannet`
--

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `dummies`
--

CREATE TABLE `dummies` (
  `synset_id` varchar(64) NOT NULL DEFAULT '',
  `label` varchar(512) NOT NULL DEFAULT '',
  `gloss` varchar(256) NOT NULL,
  `ontological_type` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `relations`
--

CREATE TABLE `relations` (
  `synset_id` int UNSIGNED NOT NULL,
  `name` varchar(32) NOT NULL,
  `name2` varchar(32) NOT NULL DEFAULT '',
  `target` enum('dannet','wordnet','dummies') NOT NULL DEFAULT 'dannet',
  `value` int UNSIGNED NOT NULL,
  `othervalue` varchar(32) NOT NULL DEFAULT '''''',
  `taxonomic` enum('taxonomic','nontaxonomic') NOT NULL,
  `inheritance_comment` varchar(256) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `synsets`
--

CREATE TABLE `synsets` (
  `synset_id` int UNSIGNED NOT NULL,
  `label` varchar(512) NOT NULL DEFAULT '',
  `gloss` varchar(256) NOT NULL,
  `ontological_type` varchar(256) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `words`
--

CREATE TABLE `words` (
  `word_id` varchar(16) NOT NULL DEFAULT '',
  `form` varchar(64) NOT NULL,
  `pos` enum('None','Noun','Verb','Adjective') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struktur-dump for tabellen `wordsenses`
--

CREATE TABLE `wordsenses` (
  `wordsense_id` int UNSIGNED NOT NULL,
  `word_id` varchar(16) NOT NULL DEFAULT '',
  `synset_id` int UNSIGNED NOT NULL,
  `register` varchar(128) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Begrænsninger for dumpede tabeller
--

--
-- Indeks for tabel `dummies`
--
ALTER TABLE `dummies`
  ADD PRIMARY KEY (`synset_id`);

--
-- Indeks for tabel `relations`
--
ALTER TABLE `relations`
  ADD KEY `synset_id` (`synset_id`);

--
-- Indeks for tabel `synsets`
--
ALTER TABLE `synsets`
  ADD PRIMARY KEY (`synset_id`);

--
-- Indeks for tabel `words`
--
ALTER TABLE `words`
  ADD PRIMARY KEY (`word_id`);

--
-- Indeks for tabel `wordsenses`
--
ALTER TABLE `wordsenses`
  ADD KEY `wordsense_id` (`wordsense_id`);
COMMIT;
