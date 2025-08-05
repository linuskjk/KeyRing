# KeyRing

**KeyRing** is a simple, self-hosted TOTP (Time-based One-Time Password) manager and generator written in PHP and JavaScript.  
It allows you to securely store, generate, and manage your 2FA secrets in your own environment.

---

## Features

- **User Registration & Login:** Secure password storage (hashed), session management, and inactivity auto-logout.
- **TOTP Generator:** Generates 6-digit codes for each entry, with a live timer and progress bar.
- **Add via QR Code:** Scan TOTP QR codes with your webcam to add new secrets easily.
- **Edit/Delete Entries:** Rename or remove TOTP secrets with confirmation dialogs.
- **Copy to Clipboard:** Click on a code to copy it instantly.
- **Responsive Design:** Works well on desktop and mobile.
- **Dark/Light Mode:** Toggle between dark and light themes.
- **Settings Menu:** Change your password, toggle theme, and manage your session.
- **Success/Error Notifications:** Get instant feedback for actions.
- **Security:** Session timeout, password change, and all secrets stored per user.

---

## How to Use

1. **Clone or copy the repository to your PHP-enabled web server.**
2. Make sure the web server can write to the `vaults/` directory and `users.json`.
3. Open `index.html` in your browser to register or log in.
4. Add TOTP secrets manually or by scanning a QR code.
5. Click on a code to copy it.
6. Use the settings menu (⚙️) to change your password or toggle dark/light mode.

---

## File Overview

- `dashboard.php` — Main user dashboard, TOTP generator, QR scanner, settings, and secret management.
- `index.html` — Login page.
- `register.html` — Registration page.
- `auth.php` — Handles authentication.
- `save.php` — Saves new TOTP secrets.
- `edit.php` — Edits existing TOTP secrets.
- `delete.php` — Deletes TOTP secrets.
- `change_password.php` — Handles password changes.
- `logout.php` — Logs out the user.
- `users.json` — Stores user credentials (hashed).
- `vaults/` — Stores each user's TOTP secrets as JSON files.
- `style.css` — Main stylesheet.

---

## Requirements

- PHP 7.2+ with file write permissions.
- Modern browser (for QR scanning and clipboard).
- No database required.

---

## Security Notes

- Passwords are hashed using PHP's `password_hash`.
- All secrets are stored per user in the `vaults/` directory.
- Sessions auto-expire after 10 minutes of inactivity.
- For production, use HTTPS and restrict file permissions.

---

## License

MIT License

---

## Credits

- [otplib](https://github.com/yeojz/otplib) for TOTP generation (browser CDN).
- [html5-qrcode](https://github.com/mebjas/html5-qrcode) for QR code scanning.
- [qrcodejs](https://github.com/davidshimjs/qrcodejs) for QR code generation.

---

**Enjoy your private, self-hosted 2FA vault!**
