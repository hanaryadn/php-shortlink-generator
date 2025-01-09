# SHORTLINK GENERATOR

Shorten your long URL with optional custom shortlink support

# Homepage :
<img src="https://github.com/user-attachments/assets/aa5e26e9-748a-4383-810b-b4cf7defdb2a" width="50%" height="50%">

# Result :
<img src="https://github.com/user-attachments/assets/b72ab540-775e-42e5-b8e4-9b53fa837485" width="50%" height="50%">

Setup :
Create DB and run this SQL :

```
CREATE TABLE short_links (
    id INT AUTO_INCREMENT PRIMARY KEY,
    short_code VARCHAR(10) NOT NULL UNIQUE,
    original_url TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## Give me Star if you like it ^_^
