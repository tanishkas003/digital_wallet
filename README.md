Digital Wallet Management System

The Digital Wallet Management System is a mini web application built using PHP, MySQL, HTML, CSS, Bootstrap, and JavaScript. It simulates a real-world wallet application where users can register, manage their wallet balance, perform transactions, and view detailed transaction history.

🔹 Features

👤 User Registration & Login (with session handling)

💰 Wallet Management

View balance & status

Add money to wallet

🛒 Transactions

Debit/Credit money with merchant selection

Prevents negative balance

Transaction history with merchant name, type, description, and timestamp

Filters by type & date

📊 Spending Report

Pie chart visualization of expenses by merchant (using Chart.js)

🔐 Secure Logout

🌐 Responsive UI with Bootstrap + custom styling

🔹 Tech Stack

Frontend: HTML, CSS, Bootstrap, JavaScript

Backend: PHP

Database: MySQL (via XAMPP for local dev)

Visualization: Chart.js

🔹 Database Design

User → stores user information

Wallet → linked to each user, tracks balance

Merchant → stores merchant details (e.g., Amazon, Flipkart, Swiggy)

Transaction → records all wallet transactions (Debit/Credit)
