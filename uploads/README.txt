# Campus Lost & Found Portal

A web-based **Campus Lost & Found Portal** developed using **PHP and MySQL** that helps students report, search, and recover lost or found items within their college campus.

The system provides a centralized platform where students can post details about lost or found belongings, search for matching items, and contact the respective owner or finder.

---

## 📌 Project Overview

In colleges, students frequently lose personal belongings such as:

* ID cards
* Books
* Wallets
* Keys
* Mobile phones
* Earphones
* Bags
* Certificates
* Accessories

Usually, information about these items is shared through WhatsApp groups or verbally, making it difficult to track and find the correct owner.

The **Campus Lost & Found Portal** solves this problem by providing a single platform for managing lost and found items.

---

## 🎯 Objectives

* Provide a centralized platform for lost and found items.
* Allow students to report lost belongings.
* Allow students to report items they have found.
* Make it easy to search and filter reported items.
* Allow users to contact the owner/finder.
* Track the status of reported items.
* Provide an admin panel for managing users and item reports.

---

## ✨ Features

### 👤 User Features

* User Registration
* User Login & Logout
* Secure Session Management
* User Dashboard
* Add Lost Item
* Add Found Item
* Upload Item Image
* View Item Details
* Search Items
* Filter Items by Category
* Filter Items by Location
* Edit Own Posts
* Delete Own Posts
* Mark Item as Returned
* Contact Owner/Finder
* View Personal Reports

### 🔐 Admin Features

* Admin Login
* Admin Dashboard
* View Total Users
* View Total Lost Items
* View Total Found Items
* View Returned Items
* View All Reported Items
* Manage Users
* Edit/Delete Items
* Update Item Status
* Remove Inappropriate Reports

---

## 🛠️ Technologies Used

### Frontend

* HTML5
* CSS3
* Bootstrap
* JavaScript

### Backend

* PHP

### Database

* MySQL

### Development Environment

* XAMPP
* Apache
* MySQL
* VS Code

---

## 🏗️ System Architecture

```text
User
  │
  ▼
Frontend
HTML + CSS + Bootstrap + JavaScript
  │
  ▼
PHP Backend
  │
  ├── Authentication
  ├── Item Management
  ├── Search & Filter
  ├── Image Upload
  └── Admin Management
  │
  ▼
MySQL Database
```

---

## 📂 Project Structure

```text
campus-lost-found/
│
├── admin/
│   ├── login.php
│   ├── dashboard.php
│   ├── users.php
│   └── manage_items.php
│
├── assets/
│   ├── css/
│   │   └── style.css
│   │
│   ├── js/
│   │   └── script.js
│   │
│   └── images/
│
├── config/
│   └── database.php
│
├── uploads/
│
├── index.php
├── register.php
├── login.php
├── logout.php
├── dashboard.php
├── add_item.php
├── items.php
├── item_details.php
├── edit_item.php
└── delete_item.php
```

---

## 🗄️ Database Design

The project uses a MySQL database named:

```text
campus_lost_found
```

### Main Tables

#### 1. users

Stores registered student information.

```text
id
name
email
password
phone
created_at
```

#### 2. items

Stores lost and found item information.

```text
id
user_id
item_name
category
description
location
item_date
image
item_type
status
created_at
```

#### 3. categories

Stores available item categories.

```text
id
category_name
```

#### 4. admin

Stores administrator login information.

```text
id
username
password
```

### Relationship

```text
users
  │
  │ 1
  │
  │
  │ many
  ▼
items
```

One user can create multiple lost/found item reports.

---

## 🔄 Item Status

Each item can have one of the following statuses:

```text
Lost
Found
Claimed
Returned
```

Example:

```text
Black Wallet
     ↓
Lost
     ↓
Found by another student
     ↓
Claimed
     ↓
Returned
```

---

## 🔎 Search & Filter

Users can search for items using:

* Item Name
* Category
* Location
* Item Type
* Status

Example:

```text
Search: Wallet

Category: Accessories

Location: Library

Type: Lost
```

The system displays matching reports from the database.

---

## 🔐 Authentication

The system provides separate authentication for:

### Student

```text
Register
   ↓
Login
   ↓
Student Dashboard
```

### Admin

```text
Admin Login
    ↓
Admin Dashboard
```

PHP sessions are used to maintain authenticated users.

---

## 📸 Image Upload

Users can upload an image while creating a lost or found item report.

Example:

```text
Item Name: Black Wallet
Category: Accessories
Location: CSE Block
Image: wallet.jpg
Status: Lost
```

The uploaded image is stored in the project's `uploads/` directory.

---

## 🚀 Installation & Setup

### Step 1 — Install XAMPP

Install XAMPP on your system.

Start:

```text
Apache
MySQL
```

from the XAMPP Control Panel.

---

### Step 2 — Clone the Repository

```bash
git clone https://github.com/your-username/campus-lost-found.git
```

Or download the project as a ZIP file.

---

### Step 3 — Move Project to XAMPP

Copy the project folder into:

```text
C:\xampp\htdocs\
```

Example:

```text
C:\xampp\htdocs\campus-lost-found
```

---

### Step 4 — Create Database

Open:

```text
http://localhost/phpmyadmin
```

Create a database:

```text
campus_lost_found
```

Import the provided SQL file if available.

---

### Step 5 — Configure Database

Open:

```text
config/database.php
```

Configure the MySQL connection:

```php
$host = "localhost";
$username = "root";
$password = "";
$database = "campus_lost_found";
```

Make sure the database name matches your MySQL database.

---

### Step 6 — Run the Project

Open your browser and visit:

```text
http://localhost/campus-lost-found/
```

---

## 👨‍💻 User Workflow

```text
Register
   ↓
Login
   ↓
Dashboard
   ↓
Report Lost / Found Item
   ↓
Item Added to Database
   ↓
Other Users Search
   ↓
View Item Details
   ↓
Contact Owner/Finder
   ↓
Claim Item
   ↓
Mark as Returned
```

---

## 👨‍💼 Admin Workflow

```text
Admin Login
     ↓
Admin Dashboard
     ↓
View Statistics
     ↓
Manage Users
     ↓
Manage Items
     ↓
Remove Invalid Reports
     ↓
Update Item Status
```

---

## 🔒 Security Considerations

The project implements basic security practices such as:

* Password hashing
* PHP session authentication
* Input validation
* Prepared SQL statements
* File upload validation
* Authentication checks for protected pages

For production deployment, additional security measures should be implemented.

---

## 📊 Future Enhancements

The project can be extended with:

* Email notifications
* Real-time notifications
* Advanced matching between lost and found items
* QR code-based item identification
* Mobile application
* AI-based image matching
* Location/map integration
* Chat between users
* Report verification system
* OTP-based authentication

These features are intentionally kept outside the basic version to maintain a moderate project scope.

---

## 🎓 Academic Use

This project is suitable for:

* Mini Project
* Final Year Project
* PHP/MySQL Practice
* Web Development Project
* College Project Demonstration

It demonstrates practical knowledge of:

```text
PHP
↓
MySQL
↓
CRUD Operations
↓
Authentication
↓
Sessions
↓
File Upload
↓
Search & Filtering
↓
Admin Management
```

---

## 📸 Screenshots

Add screenshots of the following pages:

```text
1. Home Page
2. Registration Page
3. Login Page
4. User Dashboard
5. Add Lost Item
6. Add Found Item
7. Item Listing
8. Search & Filter
9. Item Details
10. Admin Dashboard
```

Example:

```markdown
## Screenshots

### Home Page
![Home Page](screenshots/home.png)

### User Dashboard
![Dashboard](screenshots/dashboard.png)

### Admin Dashboard
![Admin Dashboard](screenshots/admin-dashboard.png)
```

---

## 🧪 Testing

The following functionalities should be tested:

| Feature       | Test                         |
| ------------- | ---------------------------- |
| Registration  | Create a new account         |
| Login         | Login with valid credentials |
| Invalid Login | Test incorrect credentials   |
| Add Item      | Create lost/found report     |
| Image Upload  | Upload valid image           |
| Search        | Search by item name          |
| Filter        | Filter by category/location  |
| Edit          | Update own report            |
| Delete        | Delete own report            |
| Status        | Change item status           |
| Admin         | Manage users/items           |
| Logout        | Destroy user session         |

---

## 👨‍💻 Author

**LOKESH CHINNAMANI**

Student / Developer

---

## 📄 License

This project is developed for educational and academic purposes.
