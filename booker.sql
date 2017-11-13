-- phpMyAdmin SQL Dump
-- version 4.7.3
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Ноя 13 2017 г., 04:51
-- Версия сервера: 5.6.37
-- Версия PHP: 5.6.31

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `user2`
--

-- --------------------------------------------------------

--
-- Структура таблицы `booker_events`
--

CREATE TABLE `booker_events` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `time_start` int(11) NOT NULL,
  `time_end` int(11) NOT NULL,
  `parent` int(11) NOT NULL DEFAULT '0',
  `time_create` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `booker_events`
--

INSERT INTO `booker_events` (`id`, `user_id`, `room_id`, `description`, `time_start`, `time_end`, `parent`, `time_create`) VALUES
(112, 2, 1, 'meeting', 1509609601, 1509618600, 0, 1510534379);

-- --------------------------------------------------------

--
-- Структура таблицы `booker_roles`
--

CREATE TABLE `booker_roles` (
  `id` int(11) NOT NULL,
  `name` varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `booker_roles`
--

INSERT INTO `booker_roles` (`id`, `name`) VALUES
(1, 'admin'),
(2, 'user');

-- --------------------------------------------------------

--
-- Структура таблицы `booker_rooms`
--

CREATE TABLE `booker_rooms` (
  `id` int(11) NOT NULL,
  `name` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `booker_rooms`
--

INSERT INTO `booker_rooms` (`id`, `name`) VALUES
(1, 'Boardroom 1'),
(2, 'Boardroom 2'),
(3, 'Boardroom 3');

-- --------------------------------------------------------

--
-- Структура таблицы `booker_users`
--

CREATE TABLE `booker_users` (
  `id` int(25) NOT NULL,
  `login` varchar(25) NOT NULL,
  `email` varchar(50) NOT NULL,
  `role` int(11) NOT NULL DEFAULT '2',
  `password` varchar(100) NOT NULL,
  `hash` varchar(100) NOT NULL,
  `status` int(11) NOT NULL DEFAULT '1',
  `time` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `booker_users`
--

INSERT INTO `booker_users` (`id`, `login`, `email`, `role`, `password`, `hash`, `status`, `time`) VALUES
(1, 'admin', 'admin@gmail.com', 1, '$2y$10$Wxs2PUnLFFxZvnZT7zJw/uTIhyts3tnInVZQWJH5ecJRXV/GoQBIe', '89bd7a29f4a06300ba045b46e813fb75', 1, 1510533686),
(2, 'user', 'user@gmail.com', 2, '$2y$10$b7ZVpvyzkqzEiyMm0iCGNup/gOZC7ASSWrXznPzULjN3Yx8NiZs.S', '83563c7e9992c92b9d97f30360f631f8', 1, 1510533963);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `booker_events`
--
ALTER TABLE `booker_events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booker_events_fk1` (`room_id`);

--
-- Индексы таблицы `booker_roles`
--
ALTER TABLE `booker_roles`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `booker_rooms`
--
ALTER TABLE `booker_rooms`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `booker_users`
--
ALTER TABLE `booker_users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `role` (`role`),
  ADD KEY `role_2` (`role`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `booker_events`
--
ALTER TABLE `booker_events`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=113;
--
-- AUTO_INCREMENT для таблицы `booker_roles`
--
ALTER TABLE `booker_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT для таблицы `booker_rooms`
--
ALTER TABLE `booker_rooms`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
--
-- AUTO_INCREMENT для таблицы `booker_users`
--
ALTER TABLE `booker_users`
  MODIFY `id` int(25) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `booker_events`
--
ALTER TABLE `booker_events`
  ADD CONSTRAINT `booker_events_fk1` FOREIGN KEY (`room_id`) REFERENCES `booker_rooms` (`id`);

--
-- Ограничения внешнего ключа таблицы `booker_users`
--
ALTER TABLE `booker_users`
  ADD CONSTRAINT `booker_users_ibfk_1` FOREIGN KEY (`role`) REFERENCES `booker_roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
