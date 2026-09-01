# Hospital Management System — Integrated Build

This is the connected foundation rather than isolated folder upgrades.

Core flow:
Patient registers/login -> books from home -> appointment becomes Pending ->
Doctor receives request -> doctor changes status -> patient notification ->
consultation/records/prescription/lab -> pharmacy -> billing/accounting.

Before production use:
1. Import database/hospital.sql after the main hospital schema.
2. Confirm the existing tables/columns match the integrated code.
3. Configure database credentials using environment variables.
4. Move secrets out of source files.
5. Disable directory listing and enable HTTPS.
6. The current project includes records, pharmacy, laboratory, admissions, billing, messaging, and reporting modules.

Hospital branding:
- Change `CAVENDISH INTERNATIONAL HOSPITAL` in `config/app.php` to set the hospital name.
- Replace `images/hospital logo.jpeg 1.jpeg` to use a different logo.
