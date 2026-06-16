# Student Relationship System - Code Notes

This document provides a clear breakdown of every script in this project, explaining what they do and how they fit together to create the full application.

## 1. Core & Database Files

- **`schema.sql`**
  This is the original MySQL script used to structure the database. It creates the three main tables:
  1. `students` (stores profile information like email, hashed password, interests).
  2. `messages` (stores chats by `sender_id` and `receiver_id`).
  3. `admin` (stores administrative credentials).
  It also manually injects your first admin account record.

- **`db.php`**
  This is the database connection file. It uses MySQLi (`new mysqli()`) to connect the PHP server to your MySQL database (`relationship_system`). Every PHP file that needs to read or write data includes this file at the top (`require 'db.php';`).

## 2. Authentication Flow

- **`index.html`**
  The main landing/home page of the application that students see first. It contains no PHP logic, just basic static HTML and links to Login or Register.

- **`register.php`**
  Handles student sign-ups. When the form is submitted via `POST`, it verifies the data, hashes the password carefully using `password_hash()` for security, and runs an `INSERT` statement to save the new student into the database.

- **`login.php`**
  Handles authentication. It looks up the email provided by the user, and if a match is found, verifies their submitted password against the securely hashed string using `password_verify()`. If it succeeds, it initializes a `$_SESSION` variable so the browser remembers they are logged in.

- **`logout.php`**
  A utility file that simply calls `session_destroy()` and redirects the user back to `index.html`. It clears the browser's knowledge of the active session.

## 3. Profiles and Matching

- **`dashboard.php`**
  The main portal for students. It first checks if the user is actively logged in using `$_SESSION`. It then fetches their basic profile info. Afterwards, it runs a complex MySQL query to find matching students (by comparing string matches on `department` or SQL `LIKE` logic on shared `interests`).

## 4. The Real-Time Chat System

- **`chat.php`**
  The master frontend layout for talking to peers. It is divided into two sections using CSS flexbox:
  1. **Sidebar:** Fetches and displays a list of ALL users on the platform.
  2. **Chat Window:** Displays the conversation. 
  It also runs an inline piece of Javascript `setInterval()` that quietly polls `load_messages.php` every 3 seconds to keep replacing the chat text with new updates — entirely eliminating the need for users to hit "refresh".

- **`send_message.php`**
  A discrete "action" endpoint. When you type a chat message and hit "Send", the form sends a hidden `POST` request to this file. It validates the text, runs a safe MySQL `INSERT`, and instantly executes a `header("Location: chat.php... ");` redirect so the user simply bounces back and sees their newest sent message.

- **`load_messages.php`**
  A purely backend utility file. The Javascript running inside `chat.php` constantly hits this URL. This file executes a `SELECT` query pulling the chronological message history between the sender and receiver. It builds and echoes simple HTML strips (`<div>` tags), allowing Javascript to efficiently copy-paste those strips onto the chatbox.

## 5. Administration

- **`admin.php`**
  A completely self-contained file with dual functionality. If the Admin is not logged in, it shows a login form. If they are, it transforms into a dashboard showcasing all registered students and an ongoing monitor of every single message sent in the system. It uses simple `DELETE FROM` SQL commands attached to buttons to purge rule-breakers.

## 6. Styling

- **`assets/style.css`**
  The central aesthetic sheet. It handles the color palette (clean whites and light blues), establishes Flexbox layouts for the grid positioning, standardizes button transitions, paints the hospital-esque background theme, and defines the colored "chat bubble" looks. All HTML/PHP files reference this style document in their `<head>`.
