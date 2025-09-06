Got it 👍 You’re uploading this **CRUD Blog Application with Authentication** project to GitHub.
I’ll prepare a **professional README.md** file that:

* Explains what the project does
* Shows features
* Gives setup steps (for beginners with XAMPP)
* Shows how to use it (Register → Login → CRUD operations)

Here’s a complete **README.md** for you:

---

# 📝 Basic Blog CRUD Application (PHP & MySQL)

A simple **Blog Application** built with **PHP, MySQL, and XAMPP** that demonstrates:

* ✅ User Authentication (Register & Login)
* ✅ Create, Read, Update, Delete (CRUD) posts
* ✅ Session management for users
* ✅ Password hashing for secure storage

---

## 🚀 Features

* 🔐 **User Registration & Login** (with hashed passwords)
* ➕ **Add New Post** (only when logged in)
* 📖 **View All Posts** with author & timestamp
* ✏️ **Edit Post** (only by the user who created it)
* ❌ **Delete Post** (only by the user who created it)
* 🕒 **Timestamps** for users and posts

---

## 🛠️ Tech Stack

* **Frontend:** HTML, CSS (basic styling, easy to extend)
* **Backend:** PHP (Core PHP, procedural)
* **Database:** MySQL
* **Server:** XAMPP (Apache + MySQL)

---

## 📂 Project Structure

```
crud_app/
│
├── add.php        # Add new blog post
├── db.php         # Database connection
├── delete.php     # Delete a post
├── edit.php       # Edit an existing post
├── index.php      # Homepage (list posts)
├── login.php      # User login
├── logout.php     # Logout user
├── register.php   # New user registration
└── README.md      # Project documentation
```

---

## ⚙️ Installation & Setup

### 1. Install XAMPP

* Download & install from: [XAMPP Download](https://www.apachefriends.org/download.html)
* Start **Apache** and **MySQL** in the XAMPP Control Panel

### 2. Clone the Project

```bash
cd C:\xampp\htdocs
git clone https://github.com/yourusername/crud_app.git
```

Or manually copy the project folder into:

```
C:\xampp\htdocs\crud_app
```

### 3. Setup the Database

1. Open [phpMyAdmin](http://localhost/phpmyadmin)
2. Create a new database named **blog**
3. Run this SQL to create tables:

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```

---

## ▶️ Running the Project

1. Start **Apache** and **MySQL** from XAMPP
2. Open your browser and go to:

   ```
   http://localhost/crud_app/index.php
   ```

---

## 🔑 Usage

1. **Register** a new account → `register.php`
2. **Login** with your account → `login.php`
3. **Add a Post** from the homepage
4. **Edit/Delete** only your own posts
5. **Logout** when done

---

## 📸 Screenshots (Optional)

* Register Page
* Login Page
* Post List
* Add Post Form

---

## 📌 Future Enhancements

* 🔍 Search functionality
* 📑 Pagination
* 🎨 Bootstrap for responsive UI

---

## 👨‍💻 Author

Developed as part of **ApexPlanet Internship** project.

---

👉 You just need to replace the GitHub link (`https://github.com/yourusername/crud_app.git`) with your actual repo URL after you push.

Do you want me to also include **SQL dump file (`blog.sql`)** in your repo so others can directly import instead of writing queries?
